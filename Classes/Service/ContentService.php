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
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Flux FlexForm integration Service
 *
 * Main API Service for interacting with Flux-based FlexForms
 */
class ContentService implements SingletonInterface
{

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
    public function injectRecordService(RecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * @param WorkspacesAwareRecordService $workspacesAwareRecordService
     * @return void
     */
    public function injectWorkspacesAwareRecordService(WorkspacesAwareRecordService $workspacesAwareRecordService)
    {
        $this->workspacesAwareRecordService = $workspacesAwareRecordService;
    }

    /**
     * @param mixed $id
     * @param array $row
     * @param array $parameters
     * @param DataHandler $tceMain
     * @return void
     */
    public function affectRecordByRequestParameters($id, array &$row, $parameters, DataHandler $tceMain)
    {
        unset($id, $tceMain);
        if (false === empty($parameters['overrideVals']['tt_content']['tx_flux_parent'])) {
            $row['tx_flux_parent'] = (integer) $parameters['overrideVals']['tt_content']['tx_flux_parent'];
            if (0 < $row['tx_flux_parent']) {
                $row['colPos'] = self::COLPOS_FLUXCONTENT;
            }
        }
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
    protected function applyMappingArray(
        $mappingArray,
        $pid,
        $colPos,
        $area,
        $parentUid,
        $table,
        $relativeUid,
        $relativeRecord,
        DataHandler $tceMain
    ) {
        foreach ($mappingArray as $record) {
            if (0 < $pid) {
                $record['pid'] = $pid;
            }
            if ((false === empty($colPos) || 0 === $colPos || '0' === $colPos)) {
                $record['colPos'] = $colPos;
            }
            $record['tx_flux_column'] = (string) (self::COLPOS_FLUXCONTENT === (integer) $colPos ? $area : '');
            $record['tx_flux_parent'] = (integer) (self::COLPOS_FLUXCONTENT === (integer) $colPos ? $parentUid : 0);
            if (0 > $relativeUid) {
                $record['sorting'] = $tceMain->resorting($table, $relativeRecord['pid'], 'sorting', abs($relativeUid));
            }
            $this->updateRecordInDataMap($record, null, $tceMain);
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
    protected function createMappingArray($command, $subCommand, $id, array $row, DataHandler $tceMain)
    {
        $mappingArray = [];
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
    public function moveRecord(array &$row, &$relativeTo, $parameters, DataHandler $tceMain)
    {
        // Note: this condition is here in order to NOT perform any actions if
        // the $relativeTo variable was passed by EXT:gridelements in which case
        // it is invalid (not a negative/positive integer but a string).
        if (false === strpos($relativeTo, 'x')) {
            if (MiscellaneousUtility::UNIQUE_INTEGER_OVERHEAD < $relativeTo) {
                // Fake relative to value - we can get the target from a session variable
                list ($parent, $column) = $this->getTargetAreaStoredInSession($relativeTo);
                $row['tx_flux_parent'] = $parent;
                $row['tx_flux_column'] = $column;
                $row['colPos'] = static::COLPOS_FLUXCONTENT;
                $row['sorting'] = 0;
            } elseif (0 <= (integer) $relativeTo && false === empty($parameters[1])) {
                // Special case for clipboard commands only. This special case also requires a new
                // sorting value to re-sort after a possibly invalid sorting value is received.
                list (, , $relativeTo, $parentUid, $area, ) = GeneralUtility::trimExplode('-', $parameters[1]);
                if ($relativeTo <> 0) {
                    $sorting = $tceMain->getSortNumber('tt_content', $row['uid'], -(integer) $relativeTo);
                    $row['sorting'] = is_array($sorting) ? $sorting['sortNumber'] : $sorting;
                } else {
                    $row['sorting'] = 0;
                }
                $row['tx_flux_parent'] = $parentUid;
                $row['tx_flux_column'] = $area;
            } elseif (0 > (integer) $relativeTo) {
                // inserting a new element after another element. Check column position of that element.
                // Get the desired sorting value after the relative record.
                $relativeUid = abs($relativeTo);
                $relativeToRecord = $this->loadRecordFromDatabase($relativeUid);

                if ((integer) $relativeToRecord['t3ver_oid'] === 0) {
                    BackendUtility::workspaceOL('tt_content', $relativeToRecord);
                    $movePlaceholder = BackendUtility::getMovePlaceholder('tt_content', $relativeUid);
                    if ($movePlaceholder) {
                        $relativeToRecord = $movePlaceholder;
                    }
                }
                $sorting = $tceMain->getSortNumber('tt_content', $row['uid'], $relativeTo);
                $row['tx_flux_parent'] = $relativeToRecord['tx_flux_parent'];
                $row['tx_flux_column'] = $relativeToRecord['tx_flux_column'];
                $row['colPos'] = $relativeToRecord['colPos'];
                $row['sorting'] = is_array($sorting) ? $sorting['sortNumber'] : $sorting;
            } elseif (0 <= (integer) $relativeTo) {
                // moving to first position in colPos, means that $relativeTo is the target colPos. PID is already set!
                $row['tx_flux_parent'] = null;
                $row['tx_flux_column'] = null;
                $row['colPos'] = $relativeTo;
            } else {
                $row['tx_flux_parent'] = null;
                $row['tx_flux_column'] = null;
            }
        } else {
            // $relativeTo variable was passed by EXT:gridelements
            $row['tx_flux_parent'] = null;
            $row['tx_flux_column'] = null;
        }
        if (0 < $row['tx_flux_parent']) {
            $row['colPos'] = static::COLPOS_FLUXCONTENT;
        }
    }

    /**
     * @param String $id
     * @param array $row
     * @param DataHandler $tceMain
     * @return void
     */
    public function initializeRecord($id, array &$row, DataHandler $tceMain)
    {
        $origUidFieldName = $GLOBALS['TCA']['tt_content']['ctrl']['origUid'];
        $languageFieldName = $GLOBALS['TCA']['tt_content']['ctrl']['languageField'];
        $newUid = (integer) $tceMain->substNEWwithIDs[$id];
        $oldUid = (integer) $row[$origUidFieldName];
        $newLanguageUid = (integer) $row[$languageFieldName];
        $this->initializeRecordByNewAndOldAndLanguageUids(
            $row,
            $newUid,
            $oldUid,
            $newLanguageUid,
            $languageFieldName,
            $tceMain
        );
    }

    /**
     * @param array $row
     * @param integer $newUid
     * @param integer $oldUid
     * @param integer $newLanguageUid
     * @param string $languageFieldName
     * @param DataHandler $tceMain
     */
    protected function initializeRecordByNewAndOldAndLanguageUids(
        $row,
        $newUid,
        $oldUid,
        $newLanguageUid,
        $languageFieldName,
        DataHandler $tceMain
    ) {
        if (0 < $newUid && 0 < $oldUid && 0 < $newLanguageUid) {
            // Get the origin record of the current record to find out if it has any
            // parameters we need to adapt in the current record.
            $oldRecord = $this->loadRecordFromDatabase($oldUid);
            if (
              $oldRecord[$languageFieldName] !== $newLanguageUid
              && $oldRecord['pid'] === $row['pid']
              && MathUtility::canBeInterpretedAsInteger($oldRecord['tx_flux_parent'])
              && $oldRecord['tx_flux_parent'] > 0
            ) {
                // If the origin record has a flux parent assigned, then look for the
                // translated, very last version this parent record and, if any valid record was found,
                // assign its UID as flux parent to the current record.
                $translatedParents = (array) $this->workspacesAwareRecordService->get(
                    'tt_content',
                    'uid,sys_language_uid',
                    '1=1 ' . BackendUtility::deleteClause('tt_content')
                );
                foreach ($translatedParents as $translatedParent) {
                    if ($translatedParent['sys_language_uid'] == $newLanguageUid) {
                        // set $translatedParent to the right language ($newLanguageUid):
                        break;
                    }
                    unset($translatedParent);
                }
                $sortbyFieldName = true === isset($GLOBALS['TCA']['tt_content']['ctrl']['sortby']) ?
                    $GLOBALS['TCA']['tt_content']['ctrl']['sortby'] : 'sorting';
                $overrideValues = [
                    $sortbyFieldName => $tceMain->resorting('tt_content', $row['pid'], $sortbyFieldName, $oldUid),
                    'tx_flux_parent' => $translatedParent ? $translatedParent['uid'] : $oldRecord['tx_flux_parent']
                ];
                $this->updateRecordInDataMap($overrideValues, $newUid, $tceMain);
            }
        }
    }

    /**
     * @param integer $uid
     * @param integer $languageUid
     * @return array|NULL
     */
    protected function loadRecordFromDatabase($uid, $languageUid = 0)
    {
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
    protected function loadRecordsFromDatabase($parentUid)
    {
        $parentUid = (integer) $parentUid;
        return $this->workspacesAwareRecordService->get('tt_content', '*', "tx_flux_parent = '" . $parentUid . "'");
    }

    /**
     * @param array $row
     * @param integer $uid
     * @param DataHandler $dataHandler
     * @return void
     */
    protected function updateRecordInDataMap(array $row, $uid = null, DataHandler $dataHandler)
    {
        if (null === $uid) {
            $uid = $row['uid'];
        }
        $uid = (integer) $uid;
        if (empty($uid)) {
            throw new \RuntimeException('Attempt to update unidentified record in data map');
        }
        unset($row['uid']);
        if (isset($dataHandler->datamap['tt_content'][$uid])) {
            $dataHandler->datamap['tt_content'][$uid] = array_replace(
                $dataHandler->datamap['tt_content'][$uid],
                $row
            );
        } else {
            $dataHandler->datamap['tt_content'][$uid] = $row;
        }
    }

    /**
     * @codeCoverageIgnore
     * @param integer $relativeTo
     * @return array
     */
    public function getTargetAreaStoredInSession($relativeTo)
    {
        '' !== session_id() ? : session_start();
        return $_SESSION['target' . $relativeTo];
    }

    /**
     * @param integer $uid uid of record in chosen source language
     * @param integer $languageUid sys_language_uid of language for the localized record
     * @param array $sourceRecord record in chosen source language (from table tt_content)
     * @param DataHandler $reference
     */
    public function fixPositionInLocalization($uid, $languageUid, &$sourceRecord, DataHandler $reference)
    {
        $previousLocalizedRecordUid = $this->getPreviousLocalizedRecordUid($uid, $languageUid, $reference);
        if (!empty($sourceRecord['l18n_parent'])) {
            $defaultRecordUid = $sourceRecord['l18n_parent'];
        } else {
            $defaultRecordUid = $uid;
        }
        $localizedRecord = BackendUtility::getRecordLocalization('tt_content', $defaultRecordUid, $languageUid);
        $sortingRow = $GLOBALS['TCA']['tt_content']['ctrl']['sortby'];
        if (null === $previousLocalizedRecordUid) {
            // moving to first position in tx_flux_column
            $localizedRecord[0][$sortingRow] = $reference->getSortNumber(
                'tt_content',
                0,
                $sourceRecord['pid']
            );
        } else {
            $localizedRecord[0][$sortingRow] = $reference->resorting(
                'tt_content',
                $sourceRecord['pid'],
                $sortingRow,
                $previousLocalizedRecordUid
            );
        }
        $this->updateRecordInDataMap($localizedRecord[0], null, $reference);
    }

    /**
     * Returning uid of previous localized record, if any, for tables with a "sortby" column
     * Used when new localized records are created so that localized records are sorted in the same order
     * as the default language records
     *
     * This is a port from DataHandler::getPreviousLocalizedRecordUid that respects tx_flux_parent and tx_flux_column!
     *
     * @param integer $uid Uid of default language record
     * @param integer $language Language of localization
     * @param DataHandler $reference
     * @return integer uid of record after which the localized record should be inserted
     */
    protected function getPreviousLocalizedRecordUid($uid, $language, DataHandler $reference)
    {
        $table = 'tt_content';
        $previousLocalizedRecordUid = $uid;
        $sortRow = $GLOBALS['TCA'][$table]['ctrl']['sortby'];
        $select = $sortRow . ',pid,uid,colPos,tx_flux_parent,tx_flux_column';
        // Get the sort value of the default language record
        $row = BackendUtility::getRecord($table, $uid, $select);
        if (is_array($row)) {
            // Find the previous record in default language on the same page
            $where = sprintf(
                'pid=%d AND sys_language_uid=0 AND %s < %d',
                (integer) $row['pid'],
                $sortRow,
                (integer) $row[$sortRow]
            );
            // Respect the colPos for content elements
            if ($table === 'tt_content') {
                $where .= sprintf(
                    ' AND colPos=%d AND tx_flux_column=\'%s\' AND tx_flux_parent=%d',
                    (integer) $row['colPos'],
                    $row['tx_flux_column'],
                    (integer) $row['tx_flux_parent']
                );
            }
            $where .= $reference->deleteClause($table);
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, '', $sortRow . ' DESC', '1');
            // If there is an element, find its localized record in specified localization language
            if ($previousRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $previousLocalizedRecord = BackendUtility::getRecordLocalization(
                    $table,
                    $previousRow['uid'],
                    $language
                );
                if (is_array($previousLocalizedRecord[0])) {
                    $previousLocalizedRecordUid = $previousLocalizedRecord[0]['uid'];
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
        }
        return $previousLocalizedRecordUid;
    }
}
