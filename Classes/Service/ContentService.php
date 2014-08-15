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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Service\RecordService;

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
	 * @var RecordService
	 */
	protected $recordService;

	/**
	 * @var WorkspacesAwareRecordService
	 */
	protected $workspacesAwareRecordService;

	/**
	 * @param RecordService $recordService
	 * @return void
	 */
	public function injectRecordService(RecordService $recordService) {
		$this->recordService = $recordService;
	}

	/**
	 * @param WorkspacesAwareRecordService $workspacesAwareRecordService
	 * @return void
	 */
	public function injectWorkspacesAwareRecordService(WorkspacesAwareRecordService $workspacesAwareRecordService) {
		$this->workspacesAwareRecordService = $workspacesAwareRecordService;
	}

	/*
	 * @param String $id
	 * @param array $row
	 * @param array $parameters
	 * @param DataHandler $tceMain
	 * @return void
	 */
	public function affectRecordByRequestParameters($id, array &$row, $parameters, DataHandler $tceMain) {
		if (FALSE === empty($parameters['overrideVals']['tt_content']['tx_flux_parent'])) {
			$row['tx_flux_parent'] = (integer) $parameters['overrideVals']['tt_content']['tx_flux_parent'];
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
			$parentUid = (integer) $parentUid;
			$relativeUid = 0 - $relativeUid;
		} else {
			list ($tablename, $pid, $relativeUid) = $parameters;
		}
		$mappingArray = array();
		if ('copy' !== $command) {
			$mappingArray[$id] = $row;
		} else {
			foreach ($tceMain->copyMappingArray['tt_content'] as $copyFromUid => $copyToUid) {
				$record = $this->loadRecordFromDatabase($copyToUid);
				if ('reference' === $subCommand) {
					$record['CType'] = 'shortcut';
					$record['records'] = $id;
				}

				$mappingArray[$copyFromUid] = $record;
			}
		}

		foreach ($mappingArray as $copyFromUid => $record) {
			if (0 > $relativeUid) {
				$relativeRecord = $this->loadRecordFromDatabase(abs($relativeUid), $record['sys_language_uid']);
			}

			if (FALSE === empty($possibleArea) || FALSE === empty($record['tx_flux_column'])) {
				if ($copyFromUid === $parentUid) {
					$record['tx_flux_parent'] = $parentUid;
					if (0 > $relativeUid) {
						$record['sorting'] = $tceMain->resorting('tt_content', $relativeRecord['pid'], 'sorting', $relativeRecord['uid']);
					}
				} else {
					$parentRecord = $this->loadRecordFromDatabase($parentUid, $record['sys_language_uid']);
					if ($copyFromUid === (integer) $parentRecord['uid']) {
						$record['tx_flux_parent'] = $parentRecord['uid'];
						if (0 > $relativeUid) {
							$record['sorting'] = $tceMain->resorting('tt_content', $relativeRecord['pid'], 'sorting', $relativeRecord['uid']);
						}
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
			} elseif (0 <= $relativeUid) {
				$record['sorting'] = 0;
				$record['pid'] = $relativeUid;
				$record['tx_flux_column'] = '';
				$record['tx_flux_parent'] = '';
			}
			if (TRUE === isset($pid) && FALSE === isset($relativeRecord['pid'])) {
				$record['pid'] = $pid;
			}
			if ((FALSE === empty($possibleColPos) || 0 === $possibleColPos || '0' === $possibleColPos)) {
				$record['colPos'] = $possibleColPos;
			}
			if (self::COLPOS_FLUXCONTENT !== (integer) $possibleColPos) {
				$record['tx_flux_parent'] = 0;
				$record['tx_flux_column'] = '';
			}
			$record['tx_flux_parent'] = (integer) $record['tx_flux_parent'];
			$this->updateRecordInDatabase($record, NULL, $tceMain);
			$tceMain->registerDBList['tt_content'][$record['uid']];
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
		if (FALSE !== strpos($relativeTo, 'x') && TRUE === ExtensionManagementUtility::isLoaded('gridelements')) {
			// EXT:gridelements support: when dropping elements on a gridelements container drop zone, the
			// current relationships to a Flux parent element, if one is defined, must be cleared.
			// Note: this support may very well be temporary, depending on the level to which gridelements
			// adopts Flux usage.
			$row['tx_flux_column'] = $row['tx_flux_parent'] = NULL;
		} elseif (0 <= (integer) $relativeTo) {
			$row['tx_flux_column'] = $row['tx_flux_parent'] = NULL;
			if (FALSE === empty($parameters[1])) {
				list($prefix, $column, $prefix2, $page, $areaUniqid, $relativePosition, $relativeUid, $area) = GeneralUtility::trimExplode('-', $parameters[1]);
				$relativeUid = (integer) $relativeUid;
				if ('colpos' === $prefix && 'page' === $prefix2) {
					$row['colPos'] = $column;
					if ('top' === $relativePosition && 0 < $relativeUid) {
						$row['tx_flux_parent'] = $relativeUid;
						$row['tx_flux_column'] = $area;
					}
				}
			}
		} elseif (0 > (integer) $relativeTo) {
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
	 * @param integer $uid
	 * @param integer $languageUid
	 * @return array|NULL
	 */
	protected function loadRecordFromDatabase($uid, $languageUid = 0) {
		$uid = (integer) $uid;
		$languageUid = (integer) $languageUid;
		if (0 === $languageUid) {
			$record = BackendUtility::getRecord('tt_content', $uid);
		} else {
			$record = BackendUtility::getRecordLocalization('tt_content', $uid, $languageUid);
		}
		$record = $this->workspacesAwareRecordService->getSingle('tt_content', '*', $record['uid']);
		return $record;
	}

	/**
	 * @param integer $parentUid
	 * @return array|NULL
	 */
	protected function loadRecordsFromDatabase($parentUid) {
		$parentUid = (integer) $parentUid;
		return $this->workspacesAwareRecordService->get('tt_content', '*', "tx_flux_parent = '" . $parentUid . "'");
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
		$uid = (integer) $uid;
		if (FALSE === empty($uid)) {
			$row['uid'] = $uid;
			$this->workspacesAwareRecordService->update('tt_content', $row);
			// reload our record for the next bits to have access to all fields
			$row = $this->recordService->getSingle('tt_content', '*', $uid);
		}
		$versionedRecordUid = (integer) (TRUE === isset($row['t3ver_oid']) && 0 < (integer) $row['t3ver_oid'] ? $row['t3ver_oid'] : 0);
		if (0 < $versionedRecordUid) {
			// temporary record; duplicate key values of original record into temporary one.
			// Note: will continue to call this method until all temporary records in chain have been processed.
			$placeholder = $this->recordService->getSingle('tt_content', '*', $row['t3ver_oid']);
			$placeholder['tx_flux_parent'] = (integer) $row['tx_flux_parent'];
			$placeholder['tx_flux_column'] = $row['tx_flux_column'];
			$this->updateRecordInDatabase($placeholder, $row['t3ver_oid'], $tceMain);
		}
	}

}
