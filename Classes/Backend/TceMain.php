<?php

namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\RecordService;
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
        // BUGFIX Typo3 Issue https://forge.typo3.org/issues/85013
        if ($table === 'tt_content' && is_integer($id) && !isset($fieldArray['colPos'])) {
            $record = $this->recordService->get($table, 'sys_language_uid, l18n_parent', "uid = $id");
            $uidInDefaultLanguage = $record[0]['l18n_parent'];
            $fieldArray['colPos'] = (int)($reference->datamap[$table][$uidInDefaultLanguage]['colPos'] ?? 0);
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
        if ($table === 'tt_content') {
            if ('localize' === $command) {
                // TODO: correct the colPos value by reading the original language parent and re-calculating based on new parent
            }

            if ($command === 'copy') {
                // TODO: cascade copy of children with overridden colPos value for each record.
                // Records can be selected with an SQL condition that matches colPos values within the calculated range
                // based on parent's UID.
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
