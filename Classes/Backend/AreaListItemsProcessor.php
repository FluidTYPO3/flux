<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Returns options for a "content area" selector box
 */
class AreaListItemsProcessor
{

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var FluxService
     */
    protected $fluxService;

    /**
     * @var RecordService
     */
    protected $recordService;

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->fluxService = $this->objectManager->get(FluxService::class);
        $this->recordService = $this->objectManager->get(RecordService::class);
    }

    /**
     * ItemsProcFunc - adds items to tt_content.colPos selector (first, pipes through EXT:gridelements)
     *
     * @param array $params
     * @return void
     */

    public function itemsProcFunc(&$params)
    {
        $rawRecord = $this->recordService->getSingle('tt_content', 'colPos', $params['row']['uid']);
        $parentUid = (int) ($rawRecord['colPos'] / 100);

        if ($parentUid > 0) {
            $items = $this->getContentAreasDefinedInContentElement($parentUid);
        } else {
            $items = [];
        }
        // adds an empty option in the beginning of the item list
        array_unshift($items, ['', '']);

        $params['items'] = $items;
    }

    /**
     * @param integer $uid
     * @return array
     */
    public function getContentAreasDefinedInContentElement($uid)
    {
        $uid = (integer) $uid;
        $record = $this->recordService->getSingle('tt_content', '*', $uid);
        BackendUtility::workspaceOL('tt_content', $record);
        /** @var $providers ProviderInterface[] */
        $providers = $this->fluxService->resolveConfigurationProviders('tt_content', null, $record);
        $columns = [];
        foreach ($providers as $provider) {
            $grid = $provider->getGrid($record);
            if (true === empty($grid)) {
                continue;
            }
            $gridConfiguration = $grid->build();
            foreach ($gridConfiguration['rows'] as $row) {
                foreach ($row['columns'] as $column) {
                    if (strpos($column['label'], 'LLL:') !== 0) {
                        $label = $column['label'] . ' (' . $column['name'] . ')';
                    } else {
                        $label = $column['label'];
                    }
                    array_push($columns, [$label, $column['name']]);
                }
            }
        }
        return array_unique($columns, SORT_REGULAR);
    }
}
