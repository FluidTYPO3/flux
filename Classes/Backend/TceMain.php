<?php

namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Service\FluxService;
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
     * @var FluxService
     */
    protected $configurationService;

    /**
     * @var boolean
     */
    static private $cachesCleared = false;

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        $this->configurationService = $configurationService;
    }


    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->injectObjectManager(GeneralUtility::makeInstance(ObjectManager::class));
        $this->injectConfigurationService($this->objectManager->get(FluxService::class));
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
        // BUGFIX Typo3 Issue https://forge.typo3.org/issues/85013#change-365564
        foreach ($reference->datamap[$table] as $i => $incomingFieldArray) {

            if (array_key_exists('colPos', $incomingFieldArray)) {
                $synchronizedColPos = $incomingFieldArray['colPos'];
            }

            if(is_array($reference->cmdmap)){
                $incomingCmdArray = $reference->cmdmap[$table][$i];
                foreach ($incomingCmdArray as $command => $value) {
                    if ('move' == $command && $synchronizedColPos >= 0) {
                        $fieldArray['colPos'] = $synchronizedColPos;
                    }
                }
            }

            if('update' == $status){
                $fieldArray['colPos'] = $synchronizedColPos;
            }
        }
    }

    /**
     * Perform various cleanup operations upon clearing cache
     *
     * @param string $command
     * @return void
     */
    public function clearCacheCommand($command)
    {
        if (true === static::$cachesCleared) {
            return;
        }
        $tables = array_keys($GLOBALS['TCA']);
        foreach ($tables as $table) {
            $providers = $this->configurationService->resolveConfigurationProviders($table, null);
            foreach ($providers as $provider) {
                /** @var $provider ProviderInterface */
                $provider->clearCacheCommand($command);
            }
        }
        static::$cachesCleared = true;
        HookHandler::trigger(
            HookHandler::CACHES_CLEARED,
            [
                'command' => $command
            ]
        );
    }


    /**
     * @return array
     */
    protected function getClipboardCommand()
    {
        $command = GeneralUtility::_GET('CB');
        return (array)$command;
    }

}
