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
use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * TCEMain
 */
class TceMain
{
    /**
     * @param array $fieldArray The field names and their values to be processed
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @return void
     */
    public function processDatamap_preProcessFieldArray(&$fieldArray, $table, $id, &$reference)
    {
        if ($table !== 'tt_content' || !is_integer($id)) {
            return;
        }

        // TYPO3 issue https://forge.typo3.org/issues/85013 "colPos not part of $fieldArray when dropping in top column".
        // We catch the special case of a record being moved, but the target pid being "Root" which is the identifying
        // symptom of this bug.
        // TODO: remove when expected solution, the inclusion of colPos in $fieldArray, is merged and released in TYPO3
        if (isset($reference->cmdmap[$table][$id]['move']) && $reference->cmdmap[$table][$id]['move'] === 'Root') {
            $record = $this->getSingleRecordWithoutRestrictions($table, (int) $id, 'pid, colPos, l18n_parent');
            $uidInDefaultLanguage = $record['l18n_parent'];
            if ($uidInDefaultLanguage && isset($reference->datamap[$table][$uidInDefaultLanguage]['colPos'])) {
                $fieldArray['colPos'] = (int)($reference->datamap[$table][$uidInDefaultLanguage]['colPos'] ?? $record['colPos']);
            }
            // A massive assignment: 1) force target PID for move, 2) force update of PID, 3) update input field array.
            // All receive the value of the record's "pid" column.
            $reference->cmdmap[$table][$id]['move'] = $reference->datamap[$table][$id]['pid'] = $fieldArray['pid'] = $record['pid'];
        }

        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        list ($originalRecord, $recordsToUpdate) = $this->getParentAndRecordsNestedInGrid(
            $table,
            (int)$id,
            'uid, colPos, ' .  $languageField
        );

        if (isset($fieldArray[$languageField]) && (int) $originalRecord[$languageField] !== (int) $fieldArray[$languageField]) {
            foreach ($recordsToUpdate as $recordToUpdate) {
                $recordToUpdate[$languageField] = $fieldArray[$languageField];
                $this->processDatamap_preProcessFieldArray($recordToUpdate, $table, $recordToUpdate['uid'], $reference);
                $reference->updateDB($table, $recordToUpdate['uid'], $recordToUpdate);
            }
        }

    }

    /**
     * Command pre processing method
     *
     * Responsible for cascading delete commands to also delete children.
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
    public function processCmdmap_preProcess(&$command, $table, $id, &$relativeTo, &$reference, &$pasteUpdate)
    {
        if ($table !== 'tt_content' || $command !== 'delete') {
            return;
        }

        list (, $recordsToDelete) = $this->getParentAndRecordsNestedInGrid($table, (int)$id, 'uid, pid');

        foreach ($recordsToDelete as $recordToDelete) {
            $reference->deleteAction($table, $recordToDelete['uid']);
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
        if ($table !== 'tt_content' || ($command !== 'localize' && $command !== 'copy' && $command !== 'copyToLanguage')) {
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

        $translationSourceField = $GLOBALS['TCA'][$table]['ctrl']['translationSource'];
        $copySourceField = $GLOBALS['TCA'][$table]['ctrl']['origUid'];
        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];

        foreach ($recordsToCopy as $recordToCopy) {
            if ($command === 'localize') {
                $reference->localize($table, $recordToCopy['uid'], $relativeTo);
            }

            if ($command === 'copyToLanguage') {
                $reference->copyRecord(
                    $table,
                    $recordToCopy['uid'],
                    $originalRecord['pid'],
                    true,
                    [
                        $copySourceField => $recordToCopy['uid'],
                        'colPos' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                            $reference->copyMappingArray[$table][$id],
                            ColumnNumberUtility::calculateLocalColumnNumber($recordToCopy['colPos'])
                        ),
                        $languageField => (int)$reference->cmdmap[$table][$id][$command],
                        $translationSourceField => $recordToCopy['uid']
                    ],
                    '',
                    (int) ($reference->cmdmap[$table][$id][$command]['update'][$languageField] ?? $recordToCopy[$languageField]),
                    true
                );
            }

            if ($command === 'copy') {
                $reference->copyRecord(
                    $table,
                    $recordToCopy['uid'],
                    $originalRecord['pid'],
                    true,
                    [
                        $languageField => (int) ($reference->cmdmap[$table][$id][$command]['update'][$languageField] ?? $recordToCopy[$languageField]),
                        'colPos' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                            $reference->copyMappingArray[$table][$id],
                            ColumnNumberUtility::calculateLocalColumnNumber($recordToCopy['colPos'])
                        )
                    ]
                );
            }
        }
    }

    /**
     * @param string $command
     * @return void
     */
    public function clearCacheCommand($command)
    {
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
