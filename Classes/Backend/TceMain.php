<?php

namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
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
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var RecordService
     */
    protected $recordService;

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param RecordService $recordService
     * @return void
     */
    public function injectRecordService(RecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->injectObjectManager(GeneralUtility::makeInstance(ObjectManager::class));
        $this->injectRecordService($this->objectManager->get(RecordService::class));
    }

    /**
     * @param string $status The TCEmain operation status, fx. 'update'
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param array $fieldArray The field names and their values to be processed
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @return void
     */
    public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference)
    {
        // TYPO3 issue https://forge.typo3.org/issues/85013 "colPos not part of $fieldArray when dropping in top column"
        // TODO: remove when expected solution, the inclusion of colPos in $fieldArray, is merged and released in TYPO3
        if ($table === 'tt_content' && is_integer($id) && !isset($fieldArray['colPos'])) {
            $record = $this->recordService->get($table, 'colPos, sys_language_uid, l18n_parent', "uid = $id");
            $uidInDefaultLanguage = $record[0]['l18n_parent'];
            if ($uidInDefaultLanguage) {
                $fieldArray['colPos'] = (int)($reference->datamap[$table][$uidInDefaultLanguage]['colPos'] ?? $record['colPos']);
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
        if ($table === 'tt_content' && ($command === 'localize' || $command === 'copy' || $command === 'copyToLanguage')) {
            // A copy, or localisation (which is also a copy) was made. Cascade copy operations for child records.
            $recordsToCopy = [];
            $resolver = $this->objectManager->get(ProviderResolver::class);
            $originalRecord = $this->recordService->getSingle($table, '*', $id);
            $primaryProvider = $resolver->resolvePrimaryConfigurationProvider(
                $table,
                null,
                $originalRecord
            );
            if ($primaryProvider) {
                $childColPosValues = [];
                foreach ($primaryProvider->getGrid($originalRecord)->getRows() as $row) {
                    foreach ($row->getColumns() as $column) {
                        $childColPosValues[] = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                            $id,
                            $column->getColumnPosition()
                        );
                    }
                }

                // Selecting records to copy. The "sorting DESC" is very intentional, since we are copying children
                // into columns by consistently placing them in the topmost position. When copying is complete,
                // children will have the exact opposite order of the "sorting DESC" result - which means they are
                // sorted correctly, ascending, as the original child records were.
                if (!empty($childColPosValues)) {
                    $recordsToCopy = $this->recordService->get(
                        $table,
                        'uid, colPos, pid, sorting',
                        sprintf('colPos IN (%s', implode(', ', $childColPosValues) . ')'),
                        null,
                        'sorting DESC'
                    );
                }
            }

            if ($command === 'localize' || $command === 'copyToLanguage') {
                // Records copying loop. We force "colPos" to have a new, re-calculated value. Each record is copied
                // as if it were placed into the top of a column and the loop is in reverse order of "sorting", so
                // the end result is same sorting as originals (but with new sorting values bound to new "colPos").
                foreach ($recordsToCopy as $recordToCopy) {
                    $overrideValues = [];
                    // https://docs.typo3.org/typo3cms/TCAReference/latest/singlehtml/#origuid
                    $overrideValues['t3_origuid'] = $recordToCopy['uid'];
                    if('copyToLanguage' == $command){
                        $newChildRecord_parentUid = $reference->copyMappingArray[$table][$id];
                    }else{
                        $newChildRecord_parentUid = $originalRecord['uid'];
                    }
                    $newChildRecord_ColPos = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                        $newChildRecord_parentUid,
                        ColumnNumberUtility::calculateLocalColumnNumber($recordToCopy['colPos'])
                    );
                    $overrideValues['colPos'] = $newChildRecord_ColPos;
                    $overrideValues['sys_language_uid'] = (int)$reference->cmdmap[$table][$id][$command];
                    if($command == 'copyToLanguage'){
                        $overrideValues['l10n_source'] = $originalRecord['uid'];
                    }else{
                        if($recordToCopy['sys_language_uid'] > 0){
                            $overrideValues['l18n_parent'] = $recordToCopy['l18n_parent'];
                        }else{
                            $overrideValues['l18n_parent'] = $recordToCopy['uid'];
                        }
                    }
                    $reference->copyRecord(
                        $table,
                        $recordToCopy['uid'],
                        $originalRecord['pid'],
                        true,
                        $overrideValues
                    );
                }
            }

            if ($command === 'copy') {
                // Records copying loop. We force "colPos" to have a new, re-calculated value. Each record is copied
                // as if it were placed into the top of a column and the loop is in reverse order of "sorting", so
                // the end result is same sorting as originals (but with new sorting values bound to new "colPos").
                foreach ($recordsToCopy as $recordToCopy) {
                    $reference->copyRecord(
                        $table,
                        $recordToCopy['uid'],
                        $originalRecord['pid'],
                        true,
                        [
                            'colPos' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                                $reference->copyMappingArray[$table][$id],
                                ColumnNumberUtility::calculateLocalColumnNumber($recordToCopy['colPos'])
                            )
                        ]
                    );
                }
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
}
