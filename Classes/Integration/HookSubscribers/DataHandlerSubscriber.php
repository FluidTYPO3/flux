<?php

namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Enum\ExtensionOption;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProcessingProvider;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use FluidTYPO3\Flux\Utility\DoctrineQueryProxy;
use FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerSubscriber
{
    protected static array $copiedRecords = [];

    public function clearCacheCommand(array $command): void
    {
        if (($command['cacheCmd'] ?? null) === 'all' || ($command['cacheCmd'] ?? null) === 'system') {
            $this->regenerateContentTypes();
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
            /** @var CacheManager $cacheManager */
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $cacheManager->flushCachesInGroup('system');
            $this->regenerateContentTypes();
            return;
        }

        if ($GLOBALS['BE_USER']->workspace) {
            $record = BackendUtility::getRecord($table, (integer) $id);
        } else {
            $record = $reference->datamap[$table][$id] ?? null;
        }

        if ($record !== null) {
            /** @var RecordProcessingProvider[] $providers */
            $providers = $this->getProviderResolver()->resolveConfigurationProviders(
                $table,
                null,
                $record,
                null,
                [RecordProcessingProvider::class]
            );

            foreach ($providers as $provider) {
                if ($provider->postProcessRecord($command, (integer) $id, $record, $reference, [])) {
                    break;
                }
            }
        }

        if ($table !== 'tt_content'
            || $command !== 'new'
            || !isset($fieldArray['t3_origuid'])
            || !$fieldArray['t3_origuid']
        ) {
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
            $newColumnPosition = $this->getSingleRecordWithoutRestrictions(
                $table,
                $fieldArray['l18n_parent'],
                'colPos'
            )['colPos'] ?? null;
        } elseif (isset(static::$copiedRecords[$originalParentUid])) {
            // The parent of the original version of the record that was copied, was also copied in the same request;
            // this means the record that was copied, was copied as a recursion operation. Look up the most recent copy
            // of the original record's parent and create a new column position number based on the new parent.
            $newParentRecord = $this->getMostRecentCopyOfRecord($originalParentUid);
            if ($newParentRecord !== null) {
                $newColumnPosition = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                    $newParentRecord['uid'],
                    ColumnNumberUtility::calculateLocalColumnNumber($originalRecord['colPos'])
                );
            }
        } elseif (($fieldArray['colPos'] ?? 0) >= ColumnNumberUtility::MULTIPLIER) {
            // Record is a child record, the updated field array still indicates it is a child (was not pasted outside
            // of parent, rather, parent was pasted somewhere else).
            // If language of child record is different from resolved parent (copyToLanguage occurred), resolve the
            // right parent for the language and update the column position accordingly.
            $recordUid = (integer) ($record['uid'] ?? $id);
            $originalParentUid = ColumnNumberUtility::calculateParentUid($fieldArray['colPos']);
            $originalParent = $this->getSingleRecordWithoutRestrictions($table, $originalParentUid, 'sys_language_uid');
            $currentRecordLanguageUid = $fieldArray['sys_language_uid']
                ?? $this->getSingleRecordWithoutRestrictions($table, $recordUid, 'sys_language_uid')['sys_language_uid']
                ?? 0;
            if ($originalParent !== null && $originalParent['sys_language_uid'] !== $currentRecordLanguageUid) {
                // copyToLanguage case. Resolve the most recent translated version of the parent record in language of
                // child record, and calculate the new column position number based on it.
                $newParentRecord = $this->getTranslatedVersionOfParentInLanguageOnPage(
                    (int) $currentRecordLanguageUid,
                    (int) $fieldArray['pid'],
                    $originalParentUid
                );
                if ($newParentRecord !== null) {
                    $newColumnPosition = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                        $newParentRecord['uid'],
                        ColumnNumberUtility::calculateLocalColumnNumber($fieldArray['colPos'])
                    );
                }
            }
        }

        if ($newColumnPosition > 0) {
            $queryBuilder = $this->createQueryBuilderForTable($table);
            $expr = $queryBuilder->expr();
            $andMethodName = method_exists($expr, 'andX') ? 'andX' : 'and';
            $queryBuilder->update($table)->set('colPos', $newColumnPosition, true, Connection::PARAM_INT)->where(
                $expr->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($reference->substNEWwithIDs[$id], Connection::PARAM_INT)
                )
            )->orWhere(
                $expr->$andMethodName(
                    $expr->eq(
                        't3ver_oid',
                        $queryBuilder->createNamedParameter($reference->substNEWwithIDs[$id], Connection::PARAM_INT)
                    ),
                    $expr->eq(
                        't3ver_wsid',
                        $queryBuilder->createNamedParameter($GLOBALS['BE_USER']->workspace, Connection::PARAM_INT)
                    )
                )
            );
            DoctrineQueryProxy::executeStatementOnQueryBuilder($queryBuilder);
        }

        static::$copiedRecords[$fieldArray['t3_origuid']] = true;
    }

    /**
     * @param array $fieldArray
     * @param string $table
     * @param int|string $id
     * @param DataHandler $dataHandler
     * @return void
     */
    // @phpcs:ignore PSR1.Methods.CamelCapsMethodName
    public function processDatamap_preProcessFieldArray(array &$fieldArray, $table, $id, DataHandler $dataHandler)
    {
        $isNewRecord = strpos((string) $id, 'NEW') === 0;
        $isTranslatedRecord = ($fieldArray['l10n_source'] ?? 0) > 0;
        $pageIntegrationEnabled = ExtensionConfigurationUtility::getOption(ExtensionOption::OPTION_PAGE_INTEGRATION);
        $isPageRecord = $table === 'pages';
        if ($pageIntegrationEnabled && $isPageRecord && $isNewRecord && $isTranslatedRecord) {
            // Record is a newly created page and is a translation of a page. In all likelyhood (but we can't actually
            // know for sure since TYPO3 uses a nested DataHandler for this...) this record is the result of a blank
            // initial copy of the original language's record. We may want to copy the "Page Configuration" fields'
            // values from the original record.
            if (!isset($fieldArray[PageProvider::FIELD_NAME_MAIN], $fieldArray[PageProvider::FIELD_NAME_SUB])) {
                // To make completely sure, we only want to copy those values if both "Page Configuration" fields are
                // completely omitted from the incoming field array.
                $originalLanguageRecord = $this->getSingleRecordWithoutRestrictions(
                    'pages',
                    $fieldArray['l10n_source'],
                    PageProvider::FIELD_NAME_MAIN . ',' . PageProvider::FIELD_NAME_SUB
                );
                if ($originalLanguageRecord) {
                    $fieldArray[PageProvider::FIELD_NAME_MAIN] = $originalLanguageRecord[PageProvider::FIELD_NAME_MAIN];
                    $fieldArray[PageProvider::FIELD_NAME_SUB] = $originalLanguageRecord[PageProvider::FIELD_NAME_SUB];
                }
            }
        }

        // Handle "$table.$field" named fields where $table is the valid TCA table name and $field is an existing TCA
        // field. Updated value will still be subject to permission checks.
        $resolver = $this->getProviderResolver();
        foreach ($fieldArray as $fieldName => $fieldValue) {
            if (($GLOBALS["TCA"][$table]["columns"][$fieldName]["config"]["type"] ?? '') === 'flex') {
                $primaryConfigurationProvider = $resolver->resolvePrimaryConfigurationProvider(
                    $table,
                    $fieldName,
                    $fieldArray
                );

                if ($primaryConfigurationProvider
                    && is_array($fieldArray[$fieldName])
                    && array_key_exists('data', $fieldArray[$fieldName])
                ) {
                    foreach ($fieldArray[$fieldName]['data'] as $sheet) {
                        foreach ($sheet['lDEF'] as $key => $value) {
                            if (strpos($key, '.') !== false) {
                                [$possibleTableName, $columnName] = explode('.', $key, 2);
                                if ($possibleTableName === $table
                                    && isset($GLOBALS['TCA'][$table]['columns'][$columnName])
                                ) {
                                    $fieldArray[$columnName] = $value['vDEF'];
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($table !== 'tt_content' || !is_integer($id)) {
            return;
        }

        // TYPO3 issue https://forge.typo3.org/issues/85013 "colPos not part of $fieldArray when dropping in top column"
        // TODO: remove when expected solution, the inclusion of colPos in $fieldArray, is merged and released in TYPO3
        if (!array_key_exists('colPos', $fieldArray)) {
            $record = $this->getSingleRecordWithoutRestrictions($table, (int) $id, 'pid, colPos, l18n_parent');
            $uidInDefaultLanguage = $record['l18n_parent'] ?? null;
            if ($uidInDefaultLanguage && isset($dataHandler->datamap[$table][$uidInDefaultLanguage]['colPos'])) {
                $fieldArray['colPos'] = (integer) $dataHandler->datamap[$table][$uidInDefaultLanguage]['colPos'];
            }
        }
    }

    /**
     * @param string $table
     * @param int $id
     * @param string $command
     * @param mixed $value
     * @param DataHandler $dataHandler
     * @return void
     */
    protected function cascadeCommandToChildRecords(
        string $table,
        int $id,
        string $command,
        $value,
        DataHandler $dataHandler
    ) {
        [, $childRecords] = $this->getParentAndRecordsNestedInGrid(
            $table,
            (int)$id,
            'uid, pid',
            false,
            $command
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

    /**
     * @param DataHandler $dataHandler
     * @return void
     */
    // @phpcs:ignore PSR1.Methods.CamelCapsMethodName
    public function processCmdmap_beforeStart(DataHandler $dataHandler)
    {
        foreach ($dataHandler->cmdmap as $table => $commandSets) {
            if ($table === 'content_types') {
                $this->regenerateContentTypes();
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
                                $invalidColumnNumbers = $this->fetchAllColumnNumbersBeneathParent((integer) $id);
                                // Only react to move commands which contain a target colPos
                                if (in_array((int) $value['update']['colPos'], $invalidColumnNumbers, true)) {
                                    // Invalid target detected - delete the "move" command so it does not happen, and
                                    // dispatch an error message.
                                    unset($dataHandler->cmdmap[$table][$id]);
                                    $dataHandler->log(
                                        $table,
                                        (integer) $id,
                                        4,
                                        0,
                                        1,
                                        'Record not moved, would become child of self'
                                    );
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
    public function processCmdmap_postProcess(
        &$command,
        $table,
        $id,
        &$relativeTo,
        &$reference,
        &$pasteUpdate,
        &$pasteDataMap
    ) {

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
                    $reference->updateDB(
                        'tt_content',
                        $reference->autoVersionIdMap['tt_content'][$copiedRecordUid],
                        $overrideArray
                    );
                }
            }
        }
        */

        if ($table !== 'tt_content' || $command !== 'move') {
            return;
        }

        [$originalRecord, $recordsToProcess] = $this->getParentAndRecordsNestedInGrid(
            $table,
            (integer) $id,
            'uid, pid, colPos',
            false,
            $command
        );

        if (empty($recordsToProcess)) {
            return;
        }

        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
        $languageUid = (int)($reference->cmdmap[$table][$id][$command]['update'][$languageField]
            ?? $originalRecord[$languageField]);

        if ($relativeTo > 0) {
            $destinationPid = $relativeTo;
        } else {
            $relativeRecord = $this->getSingleRecordWithoutRestrictions(
                $table,
                (integer) abs((integer) $relativeTo),
                'pid'
            );
            $destinationPid = $relativeRecord['pid'] ?? $relativeTo;
        }

        $this->recursivelyMoveChildRecords(
            $table,
            $recordsToProcess,
            (integer) $destinationPid,
            $languageUid,
            $reference
        );
    }

    protected function fetchAllColumnNumbersBeneathParent(int $parentUid): array
    {
        [, $recordsToProcess, $bannedColumnNumbers] = $this->getParentAndRecordsNestedInGrid(
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

    protected function recursivelyMoveChildRecords(
        string $table,
        array $recordsToProcess,
        int $pageUid,
        int $languageUid,
        DataHandler $dataHandler
    ): void {
        $subCommandMap = [];

        foreach ($recordsToProcess as $recordToProcess) {
            $recordUid = $recordToProcess['uid'];
            $subCommandMap[$table][$recordUid]['move'] = [
                'action' => 'paste',
                'target' => $pageUid,
                'update' => [
                    $GLOBALS['TCA']['tt_content']['ctrl']['languageField'] => $languageUid,
                ],
            ];
        }

        if (!empty($subCommandMap)) {
            /** @var DataHandler $dataHandler */
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->copyMappingArray = $dataHandler->copyMappingArray;
            $dataHandler->start([], $subCommandMap);
            $dataHandler->process_cmdmap();
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getSingleRecordWithRestrictions(string $table, int $uid, string $fieldsToSelect): ?array
    {
        /** @var DeletedRestriction $deletedRestriction */
        $deletedRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
        $queryBuilder = $this->createQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll()->add($deletedRestriction);
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)));
        /** @var array|false $firstResult */
        $firstResult = DoctrineQueryProxy::fetchAssociative(
            DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder)
        );
        return $firstResult ?: null;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getSingleRecordWithoutRestrictions(string $table, int $uid, string $fieldsToSelect): ?array
    {
        $queryBuilder = $this->createQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)));
        /** @var array|false $firstResult */
        $firstResult = DoctrineQueryProxy::fetchAssociative(
            DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder)
        );
        return $firstResult ?: null;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getMostRecentCopyOfRecord(int $uid, string $fieldsToSelect = 'uid'): ?array
    {
        $queryBuilder = $this->createQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from('tt_content')
            ->orderBy('uid', 'DESC')
            ->setMaxResults(1)
            ->where(
                $queryBuilder->expr()->eq('t3_origuid', $uid),
                $queryBuilder->expr()->neq('t3ver_state', -1)
            );
        /** @var array|false $firstResult */
        $firstResult = DoctrineQueryProxy::fetchAssociative(
            DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder)
        );
        return $firstResult ?: null;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getTranslatedVersionOfParentInLanguageOnPage(
        int $languageUid,
        int $pageUid,
        int $originalParentUid,
        string $fieldsToSelect = '*'
    ): ?array {
        /** @var DeletedRestriction $deletedRestriction */
        $deletedRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
        $queryBuilder = $this->createQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add($deletedRestriction);
        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from('tt_content')
            ->setMaxResults(1)
            ->orderBy('uid', 'DESC')
            ->where(
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($languageUid, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq(
                    'l10n_source',
                    $queryBuilder->createNamedParameter($originalParentUid, Connection::PARAM_INT)
                )
            );
        /** @var array|false $firstResult */
        $firstResult = DoctrineQueryProxy::fetchAssociative(
            DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder)
        );
        return $firstResult ?: null;
    }

    protected function getParentAndRecordsNestedInGrid(
        string $table,
        int $parentUid,
        string $fieldsToSelect,
        bool $respectPid = false,
        ?string $command = null
    ):array {
        // A Provider must be resolved which implements the GridProviderInterface
        $resolver = $this->getProviderResolver();
        $originalRecord = (array) $this->getSingleRecordWithoutRestrictions($table, $parentUid, '*');

        $primaryProvider = $resolver->resolvePrimaryConfigurationProvider(
            $table,
            null,
            $originalRecord,
            null,
            [GridProviderInterface::class]
        );

        if (!$primaryProvider) {
            return [
                $originalRecord,
                [],
                [],
            ];
        }

        // The Grid this Provider returns must contain at least one column
        $childColPosValues = $primaryProvider->getGrid($originalRecord)->buildColumnPositionValues($originalRecord);

        if (empty($childColPosValues)) {
            return [
                $originalRecord,
                [],
                [],
            ];
        }

        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        $queryBuilder = $this->createQueryBuilderForTable($table);
        if ($command === 'undelete') {
            $queryBuilder->getRestrictions()->removeAll();
        } else {
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        }

        $query = $queryBuilder->select(...GeneralUtility::trimExplode(',', $fieldsToSelect))
            ->from($table)
            ->andWhere(
                $queryBuilder->expr()->in(
                    'colPos',
                    $queryBuilder->createNamedParameter($childColPosValues, Connection::PARAM_INT_ARRAY)
                ),
                $queryBuilder->expr()->eq($languageField, (int)$originalRecord[$languageField]),
                $queryBuilder->expr()->in(
                    't3ver_wsid',
                    $queryBuilder->createNamedParameter(
                        [0, $GLOBALS['BE_USER']->workspace],
                        Connection::PARAM_INT_ARRAY
                    )
                )
            )->orderBy('sorting', 'DESC');

        if ($respectPid) {
            $query->andWhere($queryBuilder->expr()->eq('pid', $originalRecord['pid']));
        } else {
            $query->andWhere($queryBuilder->expr()->neq('pid', -1));
        }

        $records = DoctrineQueryProxy::fetchAllAssociative(DoctrineQueryProxy::executeQueryOnQueryBuilder($query));

        // Selecting records to return. The "sorting DESC" is very intentional; copy operations will place records
        // into the top of columns which means reading records in reverse order causes the correct final order.
        return [
            $originalRecord,
            $records,
            $childColPosValues
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getProviderResolver(): ProviderResolver
    {
        /** @var ProviderResolver $providerResolver */
        $providerResolver = GeneralUtility::makeInstance(ProviderResolver::class);
        return $providerResolver;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createQueryBuilderForTable(string $table): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($table);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function regenerateContentTypes(): void
    {
        /** @var ContentTypeManager $contentTypeManager */
        $contentTypeManager = GeneralUtility::makeInstance(ContentTypeManager::class);
        $contentTypeManager->regenerate();
    }
}
