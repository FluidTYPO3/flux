<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
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

	/**
	 * @param mixed $id
	 * @param array $row
	 * @param array $parameters
	 * @param DataHandler $tceMain
	 * @return void
	 */
	public function affectRecordByRequestParameters($id, array &$row, $parameters, DataHandler $tceMain) {
		unset($id, $tceMain);
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
		$tablename = 'tt_content';
		$subCommand = NULL;
		$possibleArea = NULL;
		$parentUid = NULL;
		$relativeRecord = NULL;
		$possibleColPos = NULL;
		if (1 < substr_count($parameters[1], '-')) {
			// Parameters were passed in a hyphen-glued string, created by Flux and passed into command.
			list ($pid, $subCommand, $relativeUid, $parentUid, $possibleArea, $possibleColPos) = explode('-', $parameters[1]);
			$parentUid = (integer) $parentUid;
			$relativeUid = 0 - (integer) $relativeUid;
			if (FALSE === empty($possibleArea)) {
				// Flux content area detected, override colPos to virtual Flux column number.
				// The $possibleColPos variable may or may not already be set but must be
				// overridden regardless.
				$possibleColPos = self::COLPOS_FLUXCONTENT;
			}
		} else {
			// Parameters are directly from TYPO3 and it almost certainly is a paste to page column.
			list ($tablename, $pid, $relativeUid) = $parameters;
			// Third parameter is not passed by every context. If not set and $pid is negative,
			// we must assume that the positive value of $pid is our relative target UID.
			$relativeUid = (integer) (0 >= (integer) $pid) ? $pid : $relativeUid;
		}

		// Creating the copy mapping array. Initial processing of all records being pasted,
		// either simply assigning them (copy action) or adjusting the copies to become
		// "insert records" elements which then render the original record (paste reference).
		$mappingArray = $this->createMappingArray($command, $subCommand, $id, $row, $tceMain);

		// If copying is performed relative to another element we must assume the values of
		// that element and use them as target relation values regardless of earlier parameters.
		if (0 > $relativeUid) {
			$relativeRecord = $this->loadRecordFromDatabase(abs($relativeUid));
			$possibleColPos = (integer) $relativeRecord['colPos'];
			$possibleArea = $relativeRecord['tx_flux_column'];
			$parentUid = (integer) $relativeRecord['tx_flux_parent'];
		}
		$this->applyMappingArray($mappingArray, $pid, $possibleColPos, $possibleArea, $parentUid, $tablename, $relativeUid,
			$relativeRecord, $tceMain);
	}

	/**
	 * @param array $mappingArray
	 * @param integer $pid
	 * @param integer $colPos
	 * @param string $area
	 * @param integer $parentUid
	 * @param string $table
	 * @param integer $relativeUid
	 * @param array|NULL $relativeRecord
	 * @param DataHandler $tceMain
	 * @return void
	 */
	protected function applyMappingArray($mappingArray, $pid, $colPos, $area, $parentUid, $table, $relativeUid, $relativeRecord, DataHandler $tceMain) {
		foreach ($mappingArray as $record) {
			if (0 < $pid) {
				$record['pid'] = $pid;
			}
			if ((FALSE === empty($colPos) || 0 === $colPos || '0' === $colPos)) {
				$record['colPos'] = $colPos;
			}
			$record['tx_flux_column'] = (string) (self::COLPOS_FLUXCONTENT === (integer) $colPos ? $area : '');
			$record['tx_flux_parent'] = (integer) (self::COLPOS_FLUXCONTENT === (integer) $colPos ? $parentUid : 0);
			if (0 > $relativeUid) {
				$record['sorting'] = $tceMain->resorting($table, $relativeRecord['pid'], 'sorting', abs($relativeUid));
			}
			$this->updateRecordInDatabase($record);
			$tceMain->registerDBList[$table][$record['uid']];
		}
	}

	/**
	 * @param string $command
	 * @param string $subCommand
	 * @param integer $id
	 * @param array $row
	 * @param DataHandler $tceMain
	 * @return array
	 */
	protected function createMappingArray($command, $subCommand, $id, array $row, DataHandler $tceMain) {
		$mappingArray = array();
		if ('copy' !== $command) {
			$mappingArray[$id] = $row;
		} else {
			// Only override values from content elements in cmdmap to prevent that child elements "inherits"
			// tx_flux_parent and tx_flux_column which would position them outside their tx_flux_parent.
			foreach ($tceMain->cmdmap['tt_content'] as $copyFromUid => $cmdMapValues) {
				$copyToUid = $tceMain->copyMappingArray['tt_content'][$copyFromUid];
				$record = $this->loadRecordFromDatabase($copyToUid);
				if ('reference' === $subCommand) {
					$record['CType'] = 'shortcut';
					$record['records'] = $id;
				}

				$mappingArray[$copyFromUid] = $record;
			}
		}
		return $mappingArray;
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
		$row['tx_flux_column'] = $row['tx_flux_parent'] = NULL;
		// Note: this condition is here in order to NOT perform any actions if
		// the $relativeTo variable was passed by EXT:gridelements in which case
		// it is invalid (not a negative/positive integer but a string).
		if (FALSE === strpos($relativeTo, 'x')) {
			if (0 - MiscellaneousUtility::UNIQUE_INTEGER_OVERHEAD > $relativeTo) {
				// Fake relative to value - we can get the target from a session variable
				list ($parent, $column) = $this->getTargetAreaStoredInSession($relativeTo);
				$row['tx_flux_parent'] = $parent;
				$row['tx_flux_column'] = $column;
			} elseif (0 <= (integer) $relativeTo && FALSE === empty($parameters[1])) {
				list($prefix, $column, $prefix2, , , $relativePosition, $relativeUid, $area) = GeneralUtility::trimExplode('-', $parameters[1]);
				$relativeUid = (integer) $relativeUid;
				if ('colpos' === $prefix && 'page' === $prefix2) {
					$row['colPos'] = $column;
					if ('top' === $relativePosition && 0 < $relativeUid) {
						$row['tx_flux_parent'] = $relativeUid;
						$row['tx_flux_column'] = $area;
					}
				}
			} elseif (0 > (integer) $relativeTo) {
				// inserting a new element after another element. Check column position of that element.
				$relativeToRecord = $this->loadRecordFromDatabase(abs($relativeTo));
				$row['tx_flux_parent'] = $relativeToRecord['tx_flux_parent'];
				$row['tx_flux_column'] = $relativeToRecord['tx_flux_column'];
				$row['colPos'] = $relativeToRecord['colPos'];
				$row['sorting'] = $tceMain->resorting('tt_content', $relativeToRecord['pid'], 'sorting', abs($relativeTo));
			}
		}
		if (0 < $row['tx_flux_parent']) {
			$row['colPos'] = self::COLPOS_FLUXCONTENT;
		}
		$this->updateRecordInDatabase($row);
		$this->updateMovePlaceholder($row);
	}

	/**
	 * @param array $row
	 * @return void
	 */
	protected function updateMovePlaceholder(array $row) {
		$movePlaceholder = $this->getMovePlaceholder($row['uid']);
		if (FALSE !== $movePlaceholder) {
			$movePlaceholder['tx_flux_parent'] = $row['tx_flux_parent'];
			$movePlaceholder['tx_flux_column'] = $row['tx_flux_column'];
			$movePlaceholder['colPos'] = $row['colPos'];
			$this->updateRecordInDatabase($movePlaceholder);
		}
	}

	/**
	 * @param integer $recordUid
	 * @return array
	 */
	protected function getMovePlaceholder($recordUid) {
		return BackendUtility::getMovePlaceholder('tt_content', $recordUid);
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
		$newUid = (integer) $tceMain->substNEWwithIDs[$id];
		$oldUid = (integer) $row[$origUidFieldName];
		$newLanguageUid = (integer) $row[$languageFieldName];
		$this->initializeRecordByNewAndOldAndLanguageUids($row, $newUid, $oldUid, $newLanguageUid, $languageFieldName, $tceMain);
	}

	/**
	 * @param array $row
	 * @param integer $newUid
	 * @param integer $oldUid
	 * @param integer $newLanguageUid
	 * @param string $languageFieldName
	 * @param DataHandler $tceMain
	 */
	protected function initializeRecordByNewAndOldAndLanguageUids($row, $newUid, $oldUid, $newLanguageUid, $languageFieldName, DataHandler $tceMain) {
		if (0 < $newUid && 0 < $oldUid && 0 < $newLanguageUid) {
			$oldRecord = $this->loadRecordFromDatabase($oldUid);
			if ($oldRecord[$languageFieldName] !== $newLanguageUid && $oldRecord['pid'] === $row['pid']) {
				// look for the translated version of the parent record indicated
				// in this new, translated record. Below, we adjust the parent UID
				// so it has the UID of the translated parent if one exists.
				$translatedParents = (array) $this->workspacesAwareRecordService->get('tt_content', 'uid', "t3_origuid = '" . $oldRecord['tx_flux_parent'] . "'");
				$translatedParent = reset($translatedParents);
				$sortbyFieldName = TRUE === isset($GLOBALS['TCA']['tt_content']['ctrl']['sortby']) ?
					$GLOBALS['TCA']['tt_content']['ctrl']['sortby'] : 'sorting';
				$overrideValues = array(
					$sortbyFieldName => $tceMain->resorting('tt_content', $row['pid'], $sortbyFieldName, $oldUid),
					'tx_flux_parent' => NULL !== $translatedParent ? $translatedParent['uid'] : $oldRecord['tx_flux_parent']
				);
				$this->updateRecordInDatabase($overrideValues, $newUid);
			}
		}
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
	 * @return void
	 */
	protected function updateRecordInDatabase(array $row, $uid = NULL) {
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
			$this->updateRecordInDatabase($placeholder, $row['t3ver_oid']);
		}
	}

	/**
	 * @codeCoverageIgnore
	 * @param integer $relativeTo
	 * @return array
	 */
	protected function getTargetAreaStoredInSession($relativeTo) {
		'' !== session_id() ?  : session_start();
		return $_SESSION['target' . $relativeTo];
	}

}
