<?php

namespace FluidTYPO3\Flux\Backend;

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
class TceMain
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
        #return;
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
                            'uid'
                        );

                        $newRecordUid = (int)($reference->substNEWwithIDs[$id] ?? $id);
                        $newColumnPosition = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                            (int)$mostRecentCopyOfParentRecord['uid'],
                            $localColumnPosition
                        );
                        $reference->updateDB($table, $newRecordUid, ['colPos' => $newColumnPosition]);
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
        // We catch the special case of a record being moved, but the target pid being "Root" which is the identifying
        // symptom of this bug.
        // TODO: remove when expected solution, the inclusion of colPos in $fieldArray, is merged and released in TYPO3
        if (isset($dataHandler->cmdmap[$table][$id]['move']) && $dataHandler->cmdmap[$table][$id]['move'] === 'Root') {
            $record = $this->getSingleRecordWithoutRestrictions($table, (int) $id, 'pid, colPos, l18n_parent');
            $uidInDefaultLanguage = $record['l18n_parent'];
            if ($uidInDefaultLanguage && isset($dataHandler->datamap[$table][$uidInDefaultLanguage]['colPos'])) {
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
        if ($table !== 'tt_content' || $command !== 'copy') {
            return;
        }

        list ($originalRecord, $recordsToCopy) = $this->getParentAndRecordsNestedInGrid(
            $table,
            (int)$id,
            'uid, colPos'
        );

        if (empty($recordsToCopy)) {
            return;
        }

        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];


        foreach ($recordsToCopy as $recordToCopy) {
            $languageUid = (int) ($reference->cmdmap[$table][$id][$command]['update'][$languageField] ?? $recordToCopy[$languageField]);
            $newChildUid = $reference->copyRecord(
                $table,
                $recordToCopy['uid'],
                $originalRecord['pid'],
                true,
                [
                    $languageField => $languageUid,
                    'colPos' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                        $reference->copyMappingArray[$table][$id],
                        ColumnNumberUtility::calculateLocalColumnNumber($recordToCopy['colPos'])
                    )
                ]
            );
            $this->recursivelyCopyChildRecords($table, $recordToCopy['uid'], $newChildUid, $languageUid, $reference);
        }
    }

    /**
     * @param string $command
     * @return void
     */
    public function clearCacheCommand($command)
    {
    }

    protected function recursivelyCopyChildRecords(string $table, int $parentUid, int $newParentUid, int $languageUid, DataHandler $dataHandler)
    {
        list ($originalRecord, $recordsToCopy) = $this->getParentAndRecordsNestedInGrid(
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
                $originalRecord['pid'],
                true,
                [
                    $languageField => $languageUid,
                    'colPos' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                        $newParentUid,
                        ColumnNumberUtility::calculateLocalColumnNumber($recordToCopy['colPos'])
                    )
                ]
            );
            $this->recursivelyCopyChildRecords($table, $recordToCopy['uid'], $newChildUid, $languageUid, $dataHandler);
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

        echo '';

        // Selecting records to return. The "sorting DESC" is very intentional; copy operations will place records
        // into the top of columns which means reading records in reverse order causes the correct final order.
        return [
            $originalRecord,
            $records
        ];
    }
}
