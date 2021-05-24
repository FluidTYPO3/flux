<?php

namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * TCEMain
 */
class DataHandlerSubscriber
{
    protected static $copiedRecords = [];

    public function clearCacheCommand($command)
    {
        if ($command['cacheCmd'] === 'all' || $command['cacheCmd'] === 'system') {
            GeneralUtility::makeInstance(ContentTypeManager::class)->regenerate();
        }
    }

    /**
     * @param string $command Command that was executed
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param array $fieldArray The field names and their values to be processed
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @return void
     */
    // @phpcs:ignore PSR1.Methods.CamelCapsMethodName
    public function processDatamap_afterDatabaseOperations($command, $table, $id, $fieldArray, $reference)
    {
        if ($table === 'content_types') {
            // Changing records in table "content_types" has to flush the system cache to regenerate various cached
            // definitions of plugins etc. that are based on those "content_types" records.
            GeneralUtility::makeInstance(CacheManager::class)->flushCachesInGroup('system');
            GeneralUtility::makeInstance(ContentTypeManager::class)->regenerate();
        }

        if ($table !== 'tt_content' || $command !== 'new' || !isset($fieldArray['t3_origuid']) || !$fieldArray['t3_origuid']) {
            // The action was not for tt_content, not a "create new" action, or not a "copy" or "copyToLanguage" action.
            return;
        }

        $originalRecord = $this->getSingleRecordWithoutRestrictions($table, $fieldArray['t3_origuid'], 'colPos');
        if ($originalRecord === null) {
            // Original record has been hard-deleted and can no longer be loaded. Processing must stop.
            return;
        }
        $originalParentUid = ColumnNumberUtility::calculateParentUid($originalRecord['colPos']);
        $newColumnPosition = 0;

        if (!empty($fieldArray['l18n_parent'])) {
            // Command was "localize", read colPos value from the translation parent and use directly
            $newColumnPosition = $this->getSingleRecordWithoutRestrictions($table, $fieldArray['l18n_parent'], 'colPos')['colPos'];
        } elseif (isset(static::$copiedRecords[$originalParentUid])) {
            // The parent of the original version of the record that was copied, was also copied in the same request;
            // this means the record that was copied, was copied as a recursion operation. Look up the most recent copy
            // of the original record's parent and create a new column position number based on the new parent.
            $newParentRecord = $this->getMostRecentCopyOfRecord($originalParentUid);
            $newColumnPosition = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                $newParentRecord['uid'],
                ColumnNumberUtility::calculateLocalColumnNumber($originalRecord['colPos'])
            );
        } elseif (($fieldArray['colPos'] ?? 0) >= ColumnNumberUtility::MULTIPLIER) {
            // Record is a child record, the updated field array still indicates it is a child (was not pasted outside
            // of parent, rather, parent was pasted somewhere else).
            // If language of child record is different from resolved parent (copyToLanguage occurred), resolve the
            // right parent for the language and update the column position accordingly.
            $originalParentUid = ColumnNumberUtility::calculateParentUid($fieldArray['colPos']);
            $originalParent = $this->getSingleRecordWithoutRestrictions($table, $originalParentUid, 'sys_language_uid');
            if ($originalParent['sys_language_uid'] !== $fieldArray['sys_language_uid']) {
                // copyToLanguage case. Resolve the most recent translated version of the parent record in language of
                // child record, and calculate the new column position number based on it.
                $newParentRecord = $this->getTranslatedVersionOfParentInLanguageOnPage((int) $fieldArray['sys_language_uid'], (int) $fieldArray['pid'], (int) $originalParentUid);
                $newColumnPosition = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                    $newParentRecord['uid'],
                    ColumnNumberUtility::calculateLocalColumnNumber($fieldArray['colPos'])
                );
            }
        }

        if ($newColumnPosition > 0) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
            $queryBuilder->update($table)->set('colPos', $newColumnPosition, true, \PDO::PARAM_INT)->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($reference->substNEWwithIDs[$id], \PDO::PARAM_INT))
            )->execute();
        }

        static::$copiedRecords[$fieldArray['t3_origuid']] = true;
    }

    /**
     * @param array $fieldArray
     * @param string $table
     * @param int|string $id
     * @param DataHandler $dataHandler
     */
    // @phpcs:ignore PSR1.Methods.CamelCapsMethodName
    public function processDatamap_preProcessFieldArray(array &$fieldArray, $table, $id, DataHandler $dataHandler)
    {
        // Handle "$table.$field" named fields where $table is the valid TCA table name and $field is an existing TCA
        // field. Updated value will still be subject to permission checks.
        $resolver = GeneralUtility::makeInstance(ObjectManager::class)->get(ProviderResolver::class);
        foreach ($fieldArray as $fieldName => $fieldValue) {
            if ($GLOBALS["TCA"][$table]["columns"][$fieldName]["config"]["type"] === 'flex') {
                $primaryConfigurationProvider = $resolver->resolvePrimaryConfigurationProvider(
                    $table,
                    $fieldName
                );

                if ($primaryConfigurationProvider && is_array($fieldArray[$fieldName]) && array_key_exists('data', $fieldArray[$fieldName])) {
                    foreach ($fieldArray[$fieldName]['data'] as $sheet) {
                        foreach ($sheet['lDEF'] as $key => $value) {
                            list ($possibleTableName, $columnName) = explode('.', $key, 2);
                            if ($possibleTableName === $table && isset($GLOBALS['TCA'][$table]['columns'][$columnName])) {
                                $fieldArray[$columnName] = $value['vDEF'];
                            }
                        }
                    }
                }
            }
        }

        if ($table !== 'tt_content' || !is_integer($id)) {
            return;
        }

        // TYPO3 issue https://forge.typo3.org/issues/85013 "colPos not part of $fieldArray when dropping in top column".
        // TODO: remove when expected solution, the inclusion of colPos in $fieldArray, is merged and released in TYPO3
        if (!array_key_exists('colPos', $fieldArray)) {
            $record = $this->getSingleRecordWithoutRestrictions($table, (int) $id, 'pid, colPos, l18n_parent');
            $uidInDefaultLanguage = $record['l18n_parent'];
            if ($uidInDefaultLanguage && isset($dataHandler->datamap[$table][$uidInDefaultLanguage]['colPos'])) {
                $fieldArray['colPos'] = (int)($dataHandler->datamap[$table][$uidInDefaultLanguage]['colPos'] ?? $record['colPos']);
            }
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

    // @phpcs:ignore PSR1.Methods.CamelCapsMethodName
    public function processCmdmap_beforeStart(DataHandler $dataHandler)
    {
        foreach ($dataHandler->cmdmap as $table => $commandSets) {
            if ($table === 'content_types') {
                GeneralUtility::makeInstance(ContentTypeManager::class)->regenerate();
                continue;
            }

            if ($table !== 'tt_content') {
                continue;
            }

            foreach ($commandSets as $id => $commands) {
                foreach ($commands as $command => $value) {
                    switch ($command) {
                        case 'move':
                            // Verify that the target column is not within the element or any child hereof.
                            if (is_array($value) && isset($value['update']['colPos'])) {
                                $invalidColumnNumbers = $this->fetchAllColumnNumbersBeneathParent($id);
                                // Only react to move commands which contain a target colPos
                                if (in_array((int) $value['update']['colPos'], $invalidColumnNumbers, true)) {
                                    // Invalid target detected - delete the "move" command so it does not happen, and
                                    // dispatch an error message.
                                    unset($dataHandler->cmdmap[$table][$id]);
                                    $dataHandler->log($table, $id, 4, 0, 1, 'Record not moved, would become child of self');
                                }
                            }
                            break;
                        case 'delete':
                        case 'undelete':
                        case 'copyToLanguage':
                        case 'localize':
                            $this->cascadeCommandToChildRecords($table, (int)$id, $command, $value, $dataHandler);
                            break;
                        case 'copy':
                            if (is_array($value)) {
                                unset($value['update']['colPos']);
                            }
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
    // @phpcs:ignore PSR1.Methods.CamelCapsMethodName
    public function processCmdmap_postProcess(&$command, $table, $id, &$relativeTo, &$reference, &$pasteUpdate, &$pasteDataMap)
    {

        /*
        if ($table === 'pages' && $command === 'copy') {
            foreach ($reference->copyMappingArray['tt_content'] ?? [] as $originalRecordUid => $copiedRecordUid) {
                $copiedRecord = $this->getSingleRecordWithoutRestrictions('tt_content', $copiedRecordUid, 'colPos');
                if ($copiedRecord['colPos'] < ColumnNumberUtility::MULTIPLIER) {
                    continue;
                }

                $oldParentUid = ColumnNumberUtility::calculateParentUid($copiedRecord['colPos']);
                $newParentUid = $reference->copyMappingArray['tt_content'][$oldParentUid];

                $overrideArray['colPos'] = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                    $newParentUid,
                    ColumnNumberUtility::calculateLocalColumnNumber((int) $copiedRecord['colPos'])
                );

                // Note here: it is safe to directly update the DB in this case, since we filtered out any
                // non-"copy" actions, and "copy" is the only action which requires adjustment.
                $reference->updateDB('tt_content', $copiedRecordUid, $overrideArray);

                // But if we also have a workspace version of the record recorded, it too must be updated:
                if (isset($reference->autoVersionIdMap['tt_content'][$copiedRecordUid])) {
                    $reference->updateDB('tt_content', $reference->autoVersionIdMap['tt_content'][$copiedRecordUid], $overrideArray);
                }
            }
        }
        */

        if ($table !== 'tt_content' || $command !== 'move') {
            return;
        }

        list ($originalRecord, $recordsToProcess) = $this->getParentAndRecordsNestedInGrid(
            $table,
            $id,
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
            $subCommandMap = $this->recursivelyMoveChildRecords($table, $id, $destinationPid, $languageUid, $reference);
        }

        if (!empty($subCommandMap)) {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->copyMappingArray = $reference->copyMappingArray;
            $dataHandler->start([], $subCommandMap);
            $dataHandler->process_cmdmap();
        }
    }

    protected function fetchAllColumnNumbersBeneathParent(int $parentUid): array
    {
        list (, $recordsToProcess, $bannedColumnNumbers) = $this->getParentAndRecordsNestedInGrid(
            'tt_content',
            $parentUid,
            'uid, colPos'
        );
        $invalidColumnPositions = $bannedColumnNumbers;
        foreach ($recordsToProcess as $childRecord) {
            $invalidColumnPositions += $this->fetchAllColumnNumbersBeneathParent($childRecord['uid']);
        }
        return (array) $invalidColumnPositions;
    }

    protected function recursivelyMoveChildRecords(string $table, int $parentUid, int $pageUid, int $languageUid, DataHandler $dataHandler): array
    {
        $dataMap = [];

        list (, $recordsToProcess) = $this->getParentAndRecordsNestedInGrid(
            $table,
            $parentUid,
            'uid, colPos'
        );

        foreach ($recordsToProcess as $recordToProcess) {
            $recordUid = $recordToProcess['uid'];
            $dataMap[$table][$recordUid]['move'] = [
                'action' => 'paste',
                'target' => $pageUid,
                'update' => [
                    $GLOBALS['TCA']['tt_content']['ctrl']['languageField'] => $languageUid,
                ],
            ];
        }
        return $dataMap;
    }

    protected function getSingleRecordWithoutRestrictions(string $table, int $uid, string $fieldsToSelect): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)));
        return $queryBuilder->execute()->fetch() ?: null;
    }

    protected function getMostRecentCopyOfRecord(int $uid, string $fieldsToSelect = 'uid'): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from('tt_content')
            ->orderBy('uid', 'DESC')
            ->setMaxResults(1)
            ->where($queryBuilder->expr()->eq('t3_origuid', $uid));
        return $queryBuilder->execute()->fetch() ?: null;
    }

    protected function getTranslatedVersionOfParentInLanguageOnPage(int $languageUid, int $pageUid, int $originalParentUid, string $fieldsToSelect = '*'): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from('tt_content')
            ->setMaxResults(1)
            ->orderBy('uid', 'DESC')
            ->where(
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($languageUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('l10n_source', $queryBuilder->createNamedParameter($originalParentUid, \PDO::PARAM_INT))
            );
        return $queryBuilder->execute()->fetch() ?: null;
    }

    protected function getParentAndRecordsNestedInGrid(string $table, int $parentUid, string $fieldsToSelect, bool $respectPid = false)
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

        $query = $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from($table)
            ->andWhere(
                $queryBuilder->expr()->in('colPos', $queryBuilder->createNamedParameter($childColPosValues, Connection::PARAM_INT_ARRAY)),
                $queryBuilder->expr()->eq($languageField, (int)$originalRecord[$languageField]),
                $queryBuilder->expr()->in('t3ver_wsid', $queryBuilder->createNamedParameter([0, $GLOBALS['BE_USER']->workspace], Connection::PARAM_INT_ARRAY))
            )->orderBy('sorting', 'DESC');

        if ($respectPid) {
            $query->andWhere($queryBuilder->expr()->eq('pid', $originalRecord['pid']));
        } else {
            $query->andWhere($queryBuilder->expr()->neq('pid', -1));
        }

        $records = $query->execute()->fetchAll();

        // Selecting records to return. The "sorting DESC" is very intentional; copy operations will place records
        // into the top of columns which means reading records in reverse order causes the correct final order.
        return [
            $originalRecord,
            $records,
            $childColPosValues
        ];
    }
}
