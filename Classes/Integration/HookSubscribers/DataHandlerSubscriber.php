<?php

namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * TCEMain
 */
class DataHandlerSubscriber
{
    /**
     * @param string $command Command that was executed
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param array $fieldArray The field names and their values to be processed
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @return void
     */
    public function processDatamap_afterDatabaseOperations($command, $table, $id, $fieldArray, $reference)
    {
        if ($table === 'tt_content' && $command === 'new') {
            if (isset($fieldArray['colPos'], $fieldArray['l18n_parent'], $fieldArray['t3_origuid'], $fieldArray['sys_language_uid'])) {
                $originalRecordUid = (int)$fieldArray['t3_origuid'];
                // Only trigger if:
                // 1) Record is a copy of another record
                // 2) record is not in connected translation mode
                // 3) it is likely a nested record
                if ((int)$fieldArray['l18n_parent'] === 0 && $originalRecordUid > 0 && $fieldArray['colPos'] >= ColumnNumberUtility::MULTIPLIER) {
                    $localColumnPosition = ColumnNumberUtility::calculateLocalColumnNumber($fieldArray['colPos']);
                    $originalRecord = $this->getSingleRecordWithoutRestrictions($table, $originalRecordUid, 'colPos');
                    if ((int)$originalRecord['colPos'] === (int)$fieldArray['colPos']) {
                        // The record was copied (or copied to a language) but the column position is the same as the
                        // original record, which is not intended. The value needs to be re-calculated based on the
                        // translated version of the original parent record in the same language as the child record.
                        $originalParentRecordUid = ColumnNumberUtility::calculateParentUid($originalRecord['colPos']);
                        $mostRecentCopyOfParentRecord = $this->getLastCopiedVersionOfRecordInLanguage(
                            $table,
                            (int)$originalParentRecordUid,
                            (int)$fieldArray['sys_language_uid'],
                            'uid,pid'
                        );

                        $newRecordUid = (int)($reference->substNEWwithIDs[$id] ?? $id);
                        $newColumnPosition = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                            (int)$mostRecentCopyOfParentRecord['uid'],
                            $localColumnPosition
                        );
                        $reference->updateDB($table, $newRecordUid, ['colPos' => $newColumnPosition, 'pid' => $mostRecentCopyOfParentRecord['pid']]);
                    }
                }
            }
        }
    }

    /**
     * @param array $fieldArray
     * @param string $table
     * @param int|string $id
     * @param DataHandler $dataHandler
     */
    public function processDatamap_preProcessFieldArray(array &$fieldArray, $table, $id, DataHandler $dataHandler)
    {
        if ($table !== 'tt_content' || !is_integer($id)) {
            return;
        }

        // TYPO3 issue https://forge.typo3.org/issues/85013 "colPos not part of $fieldArray when dropping in top column".
        // TODO: remove when expected solution, the inclusion of colPos in $fieldArray, is merged and released in TYPO3
        if (!array_key_exists('colPos', $fieldArray)) {
            $record = $this->getSingleRecordWithoutRestrictions($table, (int) $id, 'pid, colPos, l18n_parent');
            $uidInDefaultLanguage = $record['l18n_parent'];
            if ($uidInDefaultLanguage && isset($dataHandler->datamap[$table][$uidInDefaultLanguage]['colPos']) && isset($dataHandler->cmdmap[$table][$uidInDefaultLanguage]['move'])) {
                $fieldArray['colPos'] = (int)($dataHandler->datamap[$table][$uidInDefaultLanguage]['colPos'] ?? $record['colPos']);
            }
            // A massive assignment: 1) force target PID for move, 2) force update of PID, 3) update input field array.
            // All receive the value of the record's "pid" column.
            $dataHandler->cmdmap[$table][$id]['move'] = $dataHandler->datamap[$table][$id]['pid'] = $fieldArray['pid'] = $record['pid'];
        }
    }

    protected function cascadeCommandToChildRecords(string $table, int $id, string $command, $value, DataHandler $dataHandler)
    {
        list (, $childRecords) = $this->getParentAndRecordsNestedInGrid(
            $table,
            (int)$id,
            'uid, pid'
        );

        if (empty($childRecords)) {
            return;
        }

        foreach ($childRecords as $childRecord) {
            $childRecordUid = $childRecord['uid'];
            $dataHandler->cmdmap[$table][$childRecordUid][$command] = $value;
            $this->cascadeCommandToChildRecords($table, $childRecordUid, $command, $value, $dataHandler);
        }
    }

    public function processCmdmap_beforeStart(DataHandler $dataHandler)
    {
        foreach ($dataHandler->cmdmap as $table => $commandSets) {
            if ($table !== 'tt_content') {
                continue;
            }

            foreach ($commandSets as $id => $commands) {
                foreach ($commands as $command => $value) {
                    switch ($command) {
                        case 'delete':
                        case 'undelete':
                        case 'localize':
                        case 'copyToLanguage':
                            $this->cascadeCommandToChildRecords($table, (int)$id, $command, $value, $dataHandler);
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }

    /**
     * Command post processing method
     *
     * Like other pre/post methods this method calls the corresponding
     * method on Providers which match the table/id(record) being passed.
     *
     * In addition, this method also listens for paste commands executed
     * via the TYPO3 clipboard, since such methods do not necessarily
     * trigger the "normal" record move hooks (which we also subscribe
     * to and react to in moveRecord_* methods).
     *
     * @param string $command The TCEmain operation status, fx. 'update'
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param string $relativeTo Filled if command is relative to another element
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @param array $pasteUpdate
     * @param array $pasteDataMap
     * @return void
     */
    public function processCmdmap_postProcess(&$command, $table, $id, &$relativeTo, &$reference, &$pasteUpdate, &$pasteDataMap)
    {
        if ($table !== 'tt_content' || ($command !== 'copy' && $command !== 'move')) {
            return;
        }

        list ($originalRecord, $recordsToProcess) = $this->getParentAndRecordsNestedInGrid(
            $table,
            (int)$id,
            'uid, pid, colPos'
        );

        if (empty($recordsToProcess)) {
            return;
        }

        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
        $languageUid = (int)($reference->cmdmap[$table][$id][$command]['update'][$languageField] ?? $originalRecord[$languageField]);

        if ($relativeTo > 0) {
            $destinationPid = $relativeTo;
        } else {
            $relativeRecord = $this->getSingleRecordWithoutRestrictions($table, abs($relativeTo), 'pid');
            $destinationPid = (int)($relativeRecord['pid'] ?? $relativeTo);
        }

        if ($command === 'move') {
            $this->recursivelyMoveChildRecords($table, (int)$id, $destinationPid, $languageUid, $reference);
        }

        if ($command === 'copy') {
            $this->recursivelyCopyChildRecords($table, (int)$id, (int)$reference->copyMappingArray[$table][$id], $destinationPid, $languageUid, $reference);
            /*
            foreach ($recordsToProcess as $recordToProcess) {

                $languageUid = (int) ($reference->cmdmap[$table][$id][$command]['update'][$languageField] ?? $recordToProcess[$languageField]);

                if ($command === 'copy') {
                    $newChildUid = $reference->copyRecord(
                        $table,
                        $recordToProcess['uid'],
                        $destinationPid,
                        true,
                        [
                            $languageField => $languageUid,
                            'colPos' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                                $reference->copyMappingArray[$table][$id],
                                ColumnNumberUtility::calculateLocalColumnNumber($recordToProcess['colPos'])
                            ),
                            'pid' => $destinationPid
                        ]
                    );
                    $this->recursivelyCopyChildRecords($table, $recordToProcess['uid'], $newChildUid, $destinationPid, $languageUid, $reference);
                }
            }
            */
        }
    }

    /**
     * @param string $command
     * @return void
     */
    public function clearCacheCommand($command)
    {
    }

    protected function recursivelyMoveChildRecords(string $table, int $parentUid, int $pageUid, int $languageUid, DataHandler $dataHandler)
    {
        list (, $recordsToProcess) = $this->getParentAndRecordsNestedInGrid(
            $table,
            $parentUid,
            'uid, colPos'
        );

        if (empty($recordsToProcess)) {
            return;
        }

        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        foreach ($recordsToProcess as $recordToProcess) {
            $dataHandler->updateDB(
                $table,
                $recordToProcess['uid'],
                [
                    $languageField => $languageUid,
                    'pid' => $pageUid
                ]
            );
            $this->recursivelyMoveChildRecords($table, $recordToProcess['uid'], $pageUid, $languageUid, $dataHandler);
        }
    }

    protected function recursivelyCopyChildRecords(string $table, int $parentUid, int $newParentUid, int $pageUid, int $languageUid, DataHandler $dataHandler)
    {
        list (, $recordsToCopy) = $this->getParentAndRecordsNestedInGrid(
            $table,
            $parentUid,
            'uid, colPos'
        );

        if (empty($recordsToCopy)) {
            return;
        }

        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        foreach ($recordsToCopy as $recordToCopy) {
            $newChildUid = $dataHandler->copyRecord(
                $table,
                $recordToCopy['uid'],
                $pageUid,
                true,
                [
                    $languageField => $languageUid,
                    'colPos' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                        $newParentUid,
                        ColumnNumberUtility::calculateLocalColumnNumber($recordToCopy['colPos'])
                    ),
                    'pid' => $pageUid
                ]
            );
            $this->recursivelyCopyChildRecords($table, $recordToCopy['uid'], $newChildUid, $pageUid, $languageUid, $dataHandler);
        }
    }

    protected function getLastCopiedVersionOfRecordInLanguage(string $table, int $uid, int $languageUid, string $fieldsToSelect)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from($table)
            ->andWhere($queryBuilder->expr()->eq('l10n_source', $uid), $queryBuilder->expr()->eq('sys_language_uid', $languageUid));
        $queryBuilder->setMaxResults(1)->orderBy('crdate', 'DESC');
        return $queryBuilder->execute()->fetch();
    }

    protected function getSingleRecordWithoutRestrictions(string $table, int $uid, string $fieldsToSelect)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $uid));
        return $queryBuilder->execute()->fetch();
    }

    protected function getParentAndRecordsNestedInGrid(string $table, int $parentUid, string $fieldsToSelect)
    {
        // A Provider must be resolved which implements the GridProviderInterface
        $resolver = GeneralUtility::makeInstance(ObjectManager::class)->get(ProviderResolver::class);
        $originalRecord = $this->getSingleRecordWithoutRestrictions($table, $parentUid, '*');
        $primaryProvider = $resolver->resolvePrimaryConfigurationProvider(
            $table,
            null,
            $originalRecord,
            null,
            GridProviderInterface::class
        );

        if (!$primaryProvider) {
            return [
                $originalRecord,
                []
            ];
        }

        // The Grid this Provider returns must contain at least one column
        $childColPosValues = $primaryProvider->getGrid($originalRecord)->buildColumnPositionValues($originalRecord);

        if (empty($childColPosValues)) {
            return [
                $originalRecord,
                []
            ];
        }

        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from($table)
            ->andWhere(
                $queryBuilder->expr()->in('colPos', $childColPosValues),
                $queryBuilder->expr()->eq($languageField, $originalRecord[$languageField])
            )->orderBy('sorting', 'DESC');
        $records = $queryBuilder->execute()->fetchAll();

        // Selecting records to return. The "sorting DESC" is very intentional; copy operations will place records
        // into the top of columns which means reading records in reverse order causes the correct final order.
        return [
            $originalRecord,
            $records
        ];
    }
}
