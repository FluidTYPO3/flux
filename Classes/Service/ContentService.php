<?php
namespace FluidTYPO3\Flux\Service;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Flux FlexForm integration Service
 *
 * Main API Service for interacting with Flux-based FlexForms
 *
 * @package Flux
 * @subpackage Service
 */
class ContentService implements SingletonInterface {

	const COLPOS_FLUXCONTENT = 18181;

	/**
	 * @param array $row
	 * @param array $parameters
	 * @param DataHandler $tceMain
	 * @return boolean
	 */
	public function affectRecordByRequestParameters(array &$row, $parameters, DataHandler $tceMain) {
		$url = TRUE === isset($parameters['returnUrl']) ? $parameters['returnUrl'] : NULL;
		$urlHashCutoffPoint = strrpos($url, '#');
		$area = NULL;
		if ($urlHashCutoffPoint > 0) {
			$area = substr($url, 1 - (strlen($url) - $urlHashCutoffPoint));
			if (FALSE === strpos($area, ':')) {
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
			$row['colPos'] = self::COLPOS_FLUXCONTENT;
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
	 * @param DataHandler $tceMain
	 * @return boolean
	 */
	public function pasteAfter($command, array &$row, $parameters, DataHandler $tceMain) {
		$id = $row['uid'];
		if (1 < substr_count($parameters[1], '-')) {
			list ($pid, $subCommand, $relativeUid, $parentUid, $possibleArea, $possibleColPos) = explode('-', $parameters[1]);
			$relativeUid = 0 - $relativeUid;
		} else {
			list ($tablename, $pid, $relativeUid) = $parameters;
		}
		if ($command !== 'copy') {
			$record = $row;
		} else {
			$copiedUid = $tceMain->copyMappingArray['tt_content'][$id];
			$record = $this->loadRecordFromDatabase($copiedUid);
			if ('reference' === $subCommand) {
				$record['CType'] = 'shortcut';
				$record['records'] = $id;
			}
			$id = $copiedUid;
		}
		if (FALSE === empty($possibleArea)) {
			$record['tx_flux_parent'] = $parentUid;
			$record['tx_flux_column'] = $possibleArea;
			$record['colPos'] = self::COLPOS_FLUXCONTENT;
		} elseif (0 > $relativeUid) {
			$relativeRecord = $this->loadRecordFromDatabase(abs($relativeUid));
			$record['sorting'] = $tceMain->resorting('tt_content', $row['pid'], 'sorting', abs($relativeUid));
			$record['pid'] = $relativeRecord['pid'];
			$record['colPos'] = $relativeRecord['colPos'];
			$record['tx_flux_column'] = $relativeRecord['tx_flux_column'];
			$record['tx_flux_parent'] = $relativeRecord['tx_flux_parent'];
		} elseif (0 < $relativeUid) {
			$record['sorting'] = 0;
			$record['pid'] = $relativeUid;
			$record['tx_flux_column'] = '';
			$record['tx_flux_parent'] = '';
		}
		if (FALSE === empty($possibleColPos) || 0 === $possibleColPos || '0' === $possibleColPos) {
			$record['colPos'] = $possibleColPos;
		}
		if (TRUE === isset($pid) && FALSE === isset($relativeRecord['pid'])) {
			$record['pid'] = $pid;
		}
		$this->updateRecordInDatabase($record, $id);
		$row = $record;
	}

	/**
	 * Move the content element depending on various request/row parameters.
	 *
	 * @param array $row The row which may, may not, trigger moving.
	 * @param string $relativeTo If not-zero moves record to after this UID (negative) or top of this colPos (positive)
	 * @return boolean
	 */
	public function moveRecord(array &$row, &$relativeTo) {
		if (FALSE !== strpos($relativeTo, 'FLUX')) {
			// Triggers when CE is dropped on a nested content area's header dropzone (EXT:gridelements)
			list ($areaName, $parentElementUid, $pid) = explode('-', trim($relativeTo, '-'));
			$row['tx_flux_column'] = $areaName;
			$row['tx_flux_parent'] = $parentElementUid;
			$row['pid'] = $pid;
			$row['sorting'] = -1;
			$relativeTo = $pid;
		} elseif (FALSE !== strpos($relativeTo, 'x')) {
			// Triggers when CE is dropped on a root (not CE) column header's dropzone (EXT:gridelements)
			// set colPos and remove FCE relation
			list ($relativeTo, $colPos) = explode('x', $relativeTo);
			$row['tx_flux_column'] = $row['tx_flux_parent'] = NULL;
			$row['colPos'] = $colPos;
			$row['sorting'] = -1;
		} elseif (0 <= intval($relativeTo)) {
			// dropping an element in a column header dropzone in 6.0 only sends the "colPos"
			// and this colPos may contain nothing but positive integers. Bring the severe hacking.
			$backtrace = debug_backtrace();
			$this->affectRecordByBacktrace($row, $backtrace);
		} elseif (0 > intval($relativeTo) || 0 > $row['pid']) {
			// inserting a new element after another element. Check column position of that element.
			$relativeToRecord = $this->loadRecordFromDatabase(abs($relativeTo));
			$row['tx_flux_parent'] = $relativeToRecord['tx_flux_parent'];
			$row['tx_flux_column'] = $relativeToRecord['tx_flux_column'];
			$row['colPos'] = $relativeToRecord['colPos'];
		}
		if (0 < $row['tx_flux_parent']) {
			$row['colPos'] = self::COLPOS_FLUXCONTENT;
		}
		return TRUE;
	}

	/**
	 * @param array $row
	 * @param DataHandler $tceMain
	 * @return NULL
	 */
	public function initializeRecord(array $row, DataHandler $tceMain) {
		$id = $row['uid'];
		$newUid = $tceMain->substNEWwithIDs[$id];
		$oldUid = $row['t3_origuid'];
		$languageFieldName = $GLOBALS['TCA']['tt_content']['ctrl']['languageField'];
		$newLanguageUid = NULL;
		if ($oldUid) {
			$oldRecord = $this->loadRecordFromDatabase($oldUid);
			if (FALSE === empty($row[$languageFieldName])) {
				$newLanguageUid = $row[$languageFieldName];
			} elseif (FALSE === empty($oldRecord[$languageFieldName])) {
				$newLanguageUid = $oldRecord[$languageFieldName];
			} else {
				$newLanguageUid = 1; // TODO: resolve config.sys_language_uid but WITHOUT using Extbase TS resolution, consider pid of new record
			}
			$clause = "(tx_flux_column LIKE '%:" . $oldUid . "' || tx_flux_parent = '" . $oldUid . "') AND deleted = 0 AND hidden = 0";
			$children = $this->loadRecordsFromDatabase($clause);
			if (1 > count($children)) {
				return NULL;
			}
			// Perform localization on all children, since this is not handled by the TCA field which otherwise cascades changes
			foreach ($children as $child) {
				$area = $child['tx_flux_column'];
				$overrideValues = array(
					'tx_flux_column' => $area,
					'tx_flux_parent' => $newUid,
					$languageFieldName => $newLanguageUid
				);
				if ($oldRecord[$languageFieldName] !== $newLanguageUid && $oldRecord['pid'] === $row['pid']) {
					$childUid = $tceMain->localize('tt_content', $child['uid'], $newLanguageUid);
					$this->updateRecordInDatabase($overrideValues, $childUid);
				}
			}
		}
		return NULL;
	}

	/**
	 * @param array $row
	 * @param array $backtrace
	 * @return boolean
	 */
	public function affectRecordByBacktrace(array &$row, array $backtrace) {
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
					$row['colPos'] = self::COLPOS_FLUXCONTENT;
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
	public function detectParentElementAreaFromRecord($uid) {
		$uid = abs($uid);
		$record = $this->loadRecordFromDatabase($uid);
		return $record['tx_flux_column'];
	}

	/**
	 * @param integer $uid
	 * @return integer
	 */
	public function detectParentUidFromRecord($uid) {
		$uid = abs($uid);
		$record = $this->loadRecordFromDatabase($uid);
		return intval($record['tx_flux_parent']);
	}

	/**
	 * @param mixed $uidOrClause
	 * @return array|FALSE
	 */
	protected function loadRecordFromDatabase($uidOrClause) {
		if (0 < intval($uidOrClause) && TRUE === is_integer($uidOrClause)) {
			return BackendUtility::getRecord('tt_content', $uidOrClause);
		} else {
			return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', $uidOrClause);
		}
	}

	/**
	 * @param string $clause
	 * @return array|FALSE
	 */
	protected function loadRecordsFromDatabase($clause) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', $clause);
	}

	/**
	 * @param array $row
	 * @param integer $uid
	 * @return void
	 */
	protected function updateRecordInDatabase($row, $uid = NULL) {
		if (NULL === $uid) {
			$uid = $row['uid'];
		}
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', "uid = '" . $uid . "'", $row);
	}

}
