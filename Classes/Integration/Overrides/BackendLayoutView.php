<?php
namespace FluidTYPO3\Flux\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderCollection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class BackendLayoutView extends \TYPO3\CMS\Backend\View\BackendLayoutView
{
    /**
     * @var GridProviderInterface
     */
    protected $provider;

    /**
     * @var array
     */
    protected $record;

    protected $addingItemsForContent = false;

    public function setProvider(GridProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function setRecord(array $record)
    {
        $this->record = $record;
    }

    protected function initializeDataProviderCollection()
    {
        // This is an override designed to perform no operations except create a valid data provider collection instance
        $this->setDataProviderCollection(GeneralUtility::makeInstance(DataProviderCollection::class));
    }

    /**
     * Gets colPos items to be shown in the forms engine.
     * This method is called as "itemsProcFunc" with the accordant context
     * for tt_content.colPos.
     *
     * @param array $parameters
     */
    public function colPosListItemProcFunc(array $parameters)
    {
        $this->record = $parameters['row'];
        $this->addingItemsForContent = true;
        parent::colPosListItemProcFunc($parameters);
        $this->addingItemsForContent = false;
    }

    public function getSelectedBackendLayout($pageId)
    {
        if ($this->addingItemsForContent) {
            $pageRecord = $this->loadRecordFromTable('pages', (int)$pageId);
            $pageLevelProvider = $this->resolvePrimaryProviderForRecord('pages', $pageRecord);
            if ($pageLevelProvider instanceof GridProviderInterface) {
                return $pageLevelProvider->getGrid($pageRecord)->buildExtendedBackendLayoutArray(0);
            }
        }
        // Delegate resolving of backend layout structure to the Provider, which will return a Grid, which can create
        // a full backend layout data array.
        if ($this->provider instanceof GridProviderInterface) {
            return $this->provider->getGrid($this->record)->buildExtendedBackendLayoutArray($this->record['l18n_parent'] ?: $this->record['uid']);
        }
        return parent::getSelectedBackendLayout($pageId);
    }

    /**
     * Override which will merge allowed colPos values from two places:
     *
     * 1) The currently selected backend layout (which may be a Flux-based
     *    or any other type).
     * 2) If a Provider can be resolved for the parent record and it has
     *    a grid, items from that grid are included.
     *
     * The result is a "colPos" items collection which includes page columns
     * and columns directly inside the current parent.
     *
     * @param int $pageId
     * @param array $items
     * @return array
     */
    protected function addColPosListLayoutItems($pageId, $items)
    {
        $layout = $this->getSelectedBackendLayout($pageId);
        if ($layout && $layout['__items']) {
            $items = $layout['__items'];
        }
        if ($this->addingItemsForContent) {
            $parentRecordUid = ColumnNumberUtility::calculateParentUid($this->record['colPos']);
            if ($parentRecordUid > 0) {
                $parentRecord = $this->loadRecordFromTable('tt_content', $parentRecordUid);
                $provider = $this->resolvePrimaryProviderForRecord('tt_content', $parentRecord);
                if ($provider) {
                    $items = array_merge(
                        $items,
                        [
                            [
                                $this->getLanguageService()->sL(
                                    'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.backendLayout.columnsInParent'
                                ),
                                '--div--'
                            ]
                        ],
                        $provider->getGrid($parentRecord)->buildExtendedBackendLayoutArray($parentRecordUid)['__items']
                    );
                }
            }
        }
        return $items;
    }

    protected function loadRecordFromTable(string $table, int $uid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $query = $queryBuilder->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $uid));
        $query->getRestrictions()->removeAll();
        return $query->execute()->fetchAll()[0];
    }

    protected function resolvePrimaryProviderForRecord(string $table, array $record)
    {
        return GeneralUtility::makeInstance(ObjectManager::class)
            ->get(FluxService::class)
            ->resolvePrimaryConfigurationProvider($table, null, $record, null, GridProviderInterface::class);
    }
}
