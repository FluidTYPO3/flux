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
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

	/*
	 * @param String $id
	 * @param array $row
	 * @param array $parameters
	 * @param DataHandler $tceMain
	 * @return void
	 */
	public function affectRecordByRequestParameters($id, array &$row, $parameters, DataHandler $tceMain) {
		if (FALSE === empty($parameters['overrideVals']['tt_content']['tx_flux_parent'])) {
			$row['tx_flux_parent'] = intval($parameters['overrideVals']['tt_content']['tx_flux_parent']);
			if (0 < $row['tx_flux_parent']) {
				$row['colPos'] = self::COLPOS_FLUXCONTENT;
			}
		}
	}

	/**
	 * Paste one record after another record.
	 *
	 * @param string $command The command which caused pasting - "copy" is targeted in order to determine "reference" pasting.
	 * @param array $row The record to be pasted, by reference. Changes original $row
	 * @param array $parameters List of parameters defining the paste operation target
	 * @param DataHandler $tceMain
	 * @return void
	 */
	public function pasteAfter($command, array &$row, $parameters, DataHandler $tceMain) {
		$id = $row['uid'];
		if (1 < substr_count($parameters[1], '-')) {
			list ($pid, $subCommand, $relativeUid, $parentUid, $possibleArea, $possibleColPos) = explode('-', $parameters[1]);
			$parentUid = intval($parentUid);
			$relativeUid = 0 - $relativeUid;
		} else {
			list ($tablename, $pid, $relativeUid) = $parameters;
		}
		$mappingArray = array();
		if ('copy'!== $command) {
			$mappingArray[$id] = $row;
		} else {
			foreach ($tceMain->copyMappingArray['tt_content'] as $copyFromUid => $copyToUid) {
				$record = $this->loadRecordFromDatabase($copyToUid);
				if ('reference' === $subCommand) {
					$record['CType'] = 'shortcut';
					$record['records'] = $id;
				}
				if ((FALSE === empty($possibleColPos) || 0 === $possibleColPos || '0' === $possibleColPos)) {
					$record['colPos'] = $possibleColPos;
				}
				$mappingArray[$copyFromUid] = $record;
			}
		}

		foreach ($mappingArray as $copyFromUid => $record) {
			if (0 > $relativeUid) {
				$relativeRecord = $this->loadRecordFromDatabase(abs($relativeUid), $record['sys_language_uid']);
			}

			$updateColPos = TRUE;
			if (FALSE === empty($possibleArea) || FALSE === empty($record['tx_flux_column'])) {
                $updateColPos = FALSE;
				if ($copyFromUid === $parentUid) {
					$record['tx_flux_parent'] = $parentUid;
					if (0 > $relativeUid) {
						$record['sorting'] = $tceMain->resorting('tt_content', $relativeRecord['pid'], 'sorting', $relativeRecord['uid']);
					}
					$updateColPos = TRUE;
				} else {
					$parentRecord = $this->loadRecordFromDatabase($parentUid, $record['sys_language_uid']);
					if ($copyFromUid === intval($parentRecord['uid'])) {
						$record['tx_flux_parent'] = $parentRecord['uid'];
						if (0 > $relativeUid) {
							$record['sorting'] = $tceMain->resorting('tt_content', $relativeRecord['pid'], 'sorting', $relativeRecord['uid']);
						}
						$updateColPos = TRUE;
					} elseif (FALSE === empty($record['tx_flux_parent'])) {
						$parentRecord = $this->loadRecordFromDatabase($record['tx_flux_parent'], $record['sys_language_uid']);
						$record['tx_flux_parent'] = $parentRecord['uid'];
					} else {
						$record['tx_flux_parent'] = '';
					}
				}
				if (FALSE === empty($possibleArea)) {
					$record['tx_flux_column'] = $possibleArea;
				}
				$record['colPos'] = self::COLPOS_FLUXCONTENT;
			} elseif (0 > $relativeUid) {
				$record['sorting'] = $tceMain->resorting('tt_content', $relativeRecord['pid'], 'sorting', $relativeRecord['uid']);
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
			if (TRUE === $updateColPos && (FALSE === empty($possibleColPos) || 0 === $possibleColPos || '0' === $possibleColPos)) {
				$record['colPos'] = $possibleColPos;
			}
			if (TRUE === isset($pid) && FALSE === isset($relativeRecord['pid'])) {
				$record['pid'] = $pid;
			}
			$this->updateRecordInDatabase($record, NULL, $tceMain);
		}
	}

	/**
	 * Move the content element depending on various request/row parameters.
	 *
	 * @param array $row The row which may, may not, trigger moving.
	 * @param string $relativeTo If not-zero moves record to after this UID (negative) or top of this colPos (positive)
	 * @param array $parameters List of parameters defining the move operation target
	 * @param DataHandler $tceMain
	 * @return void
	 */
	public function moveRecord(array &$row, &$relativeTo, $parameters, DataHandler $tceMain) {
		if (FALSE !== strpos($relativeTo, 'x')) {
			// EXT:gridelements support
			list($relativeTo, $colPos) = explode('x', $relativeTo);
			$row['tx_flux_column'] = $row['tx_flux_parent'] = NULL;
			$row['colPos'] = $colPos;
			$row['sorting'] = 0;
		} elseif (0 <= intval($relativeTo)) {
			$row['tx_flux_column'] = $row['tx_flux_parent'] = NULL;
			if (FALSE === empty($parameters[1])) {
				list($prefix, $column, $prefix2, $page, $areaUniqid, $relativePosition, $relativeUid, $area) = GeneralUtility::trimExplode('-', $parameters[1]);
				$relativeUid = intval($relativeUid);
				if ('colpos' === $prefix && 'page' === $prefix2) {
					$row['colPos'] = $column;
					if ('top' === $relativePosition && 0 < $relativeUid) {
						$row['tx_flux_parent'] = $relativeUid;
						$row['tx_flux_column'] = $area;
					}
				}
			}
		} elseif (0 > intval($relativeTo)) {
			// inserting a new element after another element. Check column position of that element.
			$relativeToRecord = $this->loadRecordFromDatabase(abs($relativeTo));
			$row['tx_flux_parent'] = $relativeToRecord['tx_flux_parent'];
			$row['tx_flux_column'] = $relativeToRecord['tx_flux_column'];
			$row['colPos'] = $relativeToRecord['colPos'];
		}
		if (0 < $row['tx_flux_parent']) {
			$row['colPos'] = self::COLPOS_FLUXCONTENT;
		}
		$this->updateRecordInDatabase($row, NULL, $tceMain);
	}

	/**
	 * @param String $id
	 * @param array $row
	 * @param DataHandler $tceMain
	 * @return void
	 */
	public function initializeRecord($id, array &$row, DataHandler $tceMain) {
		$origUidFieldName = $GLOBALS['TCA']['tt_content']['ctrl']['origUid'];
		$languageFieldName = $GLOBALS['TCA']['tt_content']['ctrl']['languageField'];

		$newUid = intval($tceMain->substNEWwithIDs[$id]);
		$oldUid = intval($row[$origUidFieldName]);
		$newLanguageUid = intval($row[$languageFieldName]);

		if (0 < $newUid && 0 < $oldUid && 0 < $newLanguageUid) {
			$oldRecord = $this->loadRecordFromDatabase($oldUid);
			if ($oldRecord[$languageFieldName] === $newLanguageUid || $oldRecord['pid'] !== $row['pid']) {
				return;
			}

			$sortbyFieldName = $GLOBALS['TCA']['tt_content']['ctrl']['sortby'];
			$overrideValues = array(
				$sortbyFieldName => $tceMain->resorting('tt_content', $row['pid'], $sortbyFieldName, $oldUid)
			);
			$this->updateRecordInDatabase($overrideValues, $newUid, $tceMain);

			// Perform localization on all children, since this is not handled by the TCA field which otherwise cascades changes
			$children = $this->loadRecordsFromDatabase($oldUid);
			foreach ($children as $child) {
				$overrideValues = array(
					'tx_flux_parent' => $newUid
				);
				$childUid = $tceMain->localize('tt_content', $child['uid'], $newLanguageUid);
				$this->updateRecordInDatabase($overrideValues, $childUid, $tceMain);
			}
		}
	}

	/**
	 * @param integer $uid
	 * @param integer $languageUid
	 * @return array|NULL
	 */
	protected function loadRecordFromDatabase($uid, $languageUid = 0) {
		$uid = intval($uid);
		$languageUid = intval($languageUid);
		if (0 === $languageUid) {
			return BackendUtility::getRecord('tt_content', $uid);
		} else {
			return BackendUtility::getRecordLocalization('tt_content', $uid, $languageUid);
		}
	}

	/**
	 * @param integer $parentUid
	 * @return array|NULL
	 */
	protected function loadRecordsFromDatabase($parentUid) {
		$parentUid = intval($parentUid);
		return BackendUtility::getRecordsByField('tt_content', 'tx_flux_parent', $parentUid);
	}

	/**
	 * @param array $row
	 * @param integer $uid
	 * @param DataHandler $tceMain
	 * @return void
	 */
	protected function updateRecordInDatabase(array $row, $uid = NULL, DataHandler $tceMain) {
		if (NULL === $uid) {
			$uid = $row['uid'];
		}
		$uid = intval($uid);
		if (FALSE === empty($uid)) {
			$tceMain->updateDB('tt_content', $uid, $row);
		}
	}

}
