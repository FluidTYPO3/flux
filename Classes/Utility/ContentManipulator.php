<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * ClipBoard Utility
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Utility
 */
class Tx_Flux_Utility_ContentManipulator {

	/**
	 * @param array $row
	 * @param array $parameters
	 * @param t3lib_TCEmain $tceMain
	 * @return boolean
	 */
	public static function affectRecordByRequestParameters(array &$row, $parameters, t3lib_TCEmain $tceMain) {
		$url = TRUE === isset($parameters['returnUrl']) ? $parameters['returnUrl'] : NULL;
		$urlHashCutoffPoint = strrpos($url, '#');
		$area = NULL;
		if ($urlHashCutoffPoint > 0) {
			$area = substr($url, 1 - (strlen($url) - $urlHashCutoffPoint));
			if (strpos($area, ':') === FALSE) {
				return FALSE;
			}
		}
		list ($contentAreaFromUrl, $parentUidFromUrl, $afterElementUid) = explode(':', $area);
		if ($contentAreaFromUrl) {
			$row['tx_flux_column'] = $contentAreaFromUrl;
		}
		if ($parentUidFromUrl > 0) {
			$row['tx_flux_parent'] = $parentUidFromUrl;
		}
		if ($row['tx_flux_parent'] > 0) {
			$row['colPos'] = -42;
			if (0 > $afterElementUid) {
				$row['sorting'] = $tceMain->resorting('tt_content', $row['pid'], 'sorting', abs($afterElementUid));
			}
		}
		return TRUE;
	}

	/**
	 * Paste one record after another record.
	 *
	 * @param string $command The command which caused pasting - "copy" is targeted in order to determine "reference" pasting.
	 * @param array $row The record to be pasted, by reference. Changes original $row
	 * @param array $parameters List of parameters defining the paste operation target
	 * @param t3lib_TCEmain $tceMain
	 * @return boolean
	 */
	public static function pasteAfter($command, array &$row, $parameters, t3lib_TCEmain $tceMain) {
		$id = $row['uid'];
		if (1 < substr_count($parameters[1], '-')) {
			list ($pid, $subCommand, $relativeUid, $parentUid, $possibleArea, $possibleColPos) = explode('-', $parameters[1]);
			$relativeUid = 0 - $relativeUid;
		} else {
			$relativeUid = $parameters[1];
		}
		if ($command === 'copy') {
			$copiedUid = $tceMain->copyMappingArray['tt_content'][$id];
			$condition = "uid = '" . $copiedUid . "'";
			$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', $condition));
			if ('reference' === $subCommand) {
				$record['CType'] = 'shortcut';
				$record['records'] = $id;
			}
		} else {
			$condition = "uid = '" . $id . "'";
			$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', $condition));
		}
		if (0 > $relativeUid) {
			$relativeRecord = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', "uid = '" . abs($relativeUid) . "'"));
			$record['sorting'] = $relativeRecord['sorting'] + 128;
			$record['pid'] = $relativeRecord['pid'];
			$record['colPos'] = $relativeRecord['colPos'];
			$record['tx_flux_column'] = $relativeRecord['tx_flux_column'];
			$record['tx_flux_parent'] = $relativeRecord['tx_flux_parent'];
		} elseif (FALSE === empty($possibleArea)) {
			$record['tx_flux_parent'] = $parentUid;
			$record['tx_flux_column'] = $possibleArea;
			$record['colPos'] = -42;
		} else {
			$record['sorting'] = 0;
			if (0 < $pid) {
				$record['pid'] = $pid;
			} else {
				$record['pid'] = $relativeUid;
			}
			$record['tx_flux_column'] = '';
			$record['tx_flux_parent'] = '';
		}
		if (FALSE === empty($possibleColPos) || $possibleColPos === 0 || $possibleColPos === '0') {
			$record['colPos'] = $possibleColPos;
		}
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', $condition, $record);
	}

	/**
	 * Move the content element depending on various request/row parameters.
	 *
	 * @param array $row The row which may, may not, trigger moving.
	 * @param string $relativeTo If not-zero moves record to after this UID (negative) or top of this colPos (positive)
	 * @return boolean
	 */
	public static function moveRecord(array &$row, &$relativeTo) {
		if (0 <= intval($relativeTo)) {
			// dropping an element in a column header dropzone in 6.0 only sends the "colPos"
			// and this colPos may contain nothing but positive integers. Bring the severe hacking.
			$backtrace = debug_backtrace();
			self::affectRecordByBacktrace($row, $backtrace);
		} elseif ($row['pid'] < 0) {
			// inserting a new element after another element. Check column position of that element.
			$relativeTo = abs($row['pid']);
			$relativeToRecord = t3lib_BEfunc::getRecord('tt_content', $relativeTo);
			$row['tx_flux_parent'] = $relativeToRecord['tx_flux_parent'];
			$row['tx_flux_column'] = $relativeToRecord['tx_flux_column'];
		} elseif (FALSE !== strpos($relativeTo, 'FLUX')) {
			// Triggers when CE is dropped on a nested content area's header dropzone (EXT:gridelements)
			list ($areaName, $parentElementUid, $pid) = explode('-', trim($relativeTo, '-'));
			$row['tx_flux_column'] = $areaName;
			$row['tx_flux_parent'] = $parentElementUid;
			$row['pid'] = $pid;
			$row['sorting'] = -1;
			$relativeTo = $pid;
		} elseif (0 < strpos($relativeTo, 'x')) {
			// Triggers when CE is dropped on a root (not CE) column header's dropzone (EXT:gridelements)
			// set colPos and remove FCE relation
			list ($relativeTo, $colPos) = explode('x', $relativeTo);
			$row['tx_flux_column'] = $row['tx_flux_parent'] = NULL;
			$row['colPos'] = $colPos;
			$row['sorting'] = -1;
		} elseif (0 > $relativeTo) {
			// Triggers when sorting a CE after another CE, $relativeTo is negative value of CE's UID
			$row['tx_flux_column'] = self::detectParentElementAreaFromRecord($relativeTo);
			$row['tx_flux_parent'] = self::detectParentUidFromRecord($relativeTo);
		}
		if ($row['tx_flux_parent'] > 0) {
			$row['colPos'] = -42;
		}
		return TRUE;
	}

	/**
	 * @param array $row
	 * @param t3lib_TCEmain $tceMain
	 * @return boolean
	 */
	public static function initializeRecord(array $row, t3lib_TCEmain $tceMain) {
		$id = $row['uid'];
		$newUid = $tceMain->substNEWwithIDs[$id];
		$oldUid = $row['t3_origuid'];
		$languageFieldName = $GLOBALS['TCA']['tt_content']['ctrl']['languageField'];
		$newLanguageUid = NULL;
		if ($oldUid) {
			$oldRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid,pid,' . $languageFieldName, 'tt_content', "uid = '" . $oldUid . "'");
			if (empty($row[$languageFieldName]) === FALSE) {
				$newLanguageUid = $row[$languageFieldName];
			} elseif (empty($oldRecord[$languageFieldName]) === FALSE) {
				$newLanguageUid = $oldRecord[$languageFieldName];
			} else {
				$newLanguageUid = 1; // TODO: resolve config.sys_language_uid but WITHOUT using Extbase TS resolution, consider pid of new record
			}
			$clause = "(tx_flux_column LIKE '%:" . $oldUid . "' || tx_flux_parent = '" . $oldUid . "') AND deleted = 0 AND hidden = 0";
			$children = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,pid,sys_language_uid,tx_flux_column,tx_flux_parent', 'tt_content', $clause);
			if (count($children) < 1) {
				return;
			}
			// Perform localization on all children, since this is not handled by the TCA field which otherwise cascades changes
			foreach ($children as $child) {
				if (strpos($child['tx_flux_column'], ':') === FALSE) {
					$area = $child['tx_flux_column'];
				} else {
					$areaAndUid = explode(':', $child['tx_flux_column']);
					$area = $areaAndUid[0];
				}
				$overrideValues = array(
					'tx_flux_column' => $area,
					'tx_flux_parent' => $newUid,
					$languageFieldName => $newLanguageUid
				);
				if ($oldRecord[$languageFieldName] !== $newLanguageUid && $oldRecord['pid'] === $row['pid']) {
					$childUid = $tceMain->localize('tt_content', $child['uid'], $newLanguageUid);
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', "uid = '" . $childUid . "'", $overrideValues);
				}
			}
		}
		return TRUE;
	}

	/**
	 * @param array $row
	 * @param array $backtrace
	 * @return boolean
	 */
	public static function affectRecordByBacktrace(array &$row, array $backtrace) {
		$retrievedArgument = NULL;
		$targetClass = 'TYPO3\\CMS\\Backend\\View\\PageLayout\\ExtDirect\\ExtdirectPageCommands';
		$targetFunction = 'moveContentElement';
		foreach (array_reverse($backtrace) as $stackItem) {
			if ($stackItem['class'] === $targetClass && $stackItem['function'] === $targetFunction) {
				$retrievedArgument = $stackItem['args'][1];
				$segments = explode('-', $retrievedArgument);
				$slice = array_slice($segments, count($segments) - 3);
				if ($slice[0] === 'top') {
					$row['tx_flux_parent'] = $slice[1];
					$row['tx_flux_column'] = $slice[2];
					$row['colPos'] = -42;
				} elseif ($slice[0] === 'after') {
					$row['pid'] = 0 - $slice[1];
					$row['tx_flux_column'] = $slice[2];
				} else {
					$row['tx_flux_parent'] = $row['tx_flux_column'] = '';
				}
				break;
			}
		}
		return TRUE;
	}

	/**
	 * @param integer $uid
	 * @return string
	 */
	public static function detectParentElementAreaFromRecord($uid) {
		$uid = abs($uid);
		$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', "uid = '" . $uid . "'"));
		return $record['tx_flux_column'];
	}

	/**
	 * @param integer $uid
	 * @return integer
	 */
	public static function detectParentUidFromRecord($uid) {
		$uid = abs($uid);
		$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', "uid = '" . $uid . "'"));
		return intval($record['tx_flux_parent']);
	}

}
