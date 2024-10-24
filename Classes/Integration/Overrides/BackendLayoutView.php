<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FormEngine\SelectOption;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use FluidTYPO3\Flux\Utility\DoctrineQueryProxy;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendLayoutView extends \TYPO3\CMS\Backend\View\BackendLayoutView
{
    protected ?GridProviderInterface $provider = null;
    protected array $record = [];
    protected bool $addingItemsForContent = false;

    public function setProvider(GridProviderInterface $provider): void
    {
        $this->provider = $provider;
    }

    public function setRecord(array $record): void
    {
        $this->record = $record;
    }

    /**
     * Gets colPos items to be shown in the forms engine.
     * This method is called as "itemsProcFunc" with the accordant context
     * for tt_content.colPos.
     */
    public function colPosListItemProcFunc(array &$parameters): void
    {
        $this->record = $parameters['row'];
        $this->addingItemsForContent = true;
        parent::colPosListItemProcFunc($parameters);
        $this->addingItemsForContent = false;
    }

    /**
     * @param int $pageId
     */
    public function getSelectedBackendLayout($pageId): ?array
    {
        if ($this->addingItemsForContent) {
            $identifier = $this->getSelectedCombinedIdentifier($pageId);
            if ($identifier === false) {
                return null;
            }

            // Early return parent method's output if selected identifier is not from Flux
            if (substr((string) $identifier, 0, 6) !== 'flux__') {
                return parent::getSelectedBackendLayout($pageId);
            }
            $pageRecord = $this->loadRecordFromTable('pages', (int)$pageId);
            if (!$pageRecord) {
                return null;
            }
            $pageLevelProvider = $this->resolvePrimaryProviderForRecord('pages', $pageRecord);
            if ($pageLevelProvider instanceof GridProviderInterface) {
                return $pageLevelProvider->getGrid($pageRecord)->buildExtendedBackendLayoutArray(0);
            }
        }
        // Delegate resolving of backend layout structure to the Provider, which will return a Grid, which can create
        // a full backend layout data array.
        if ($this->provider instanceof GridProviderInterface) {
            return $this->provider->getGrid($this->record)->buildExtendedBackendLayoutArray(
                $this->resolveParentRecordUid($this->record)
            );
        }
        return parent::getSelectedBackendLayout($pageId);
    }

    /**
     * Extracts the UID to use as parent UID, based on properties of the record
     * and composition of the values within it, to ensure an integer UID.
     */
    protected function resolveParentRecordUid(array $record): int
    {
        $uid = $record['l18n_parent'] ?: $record['uid'];
        if (is_array($uid)) {
            // The record was passed by a third-party integration which read the record from FormEngine's expanded
            // format which stores select-type fields such as the l18n_parent as array values. Extract it from there.
            return $uid = reset($uid);
        }
        return (int) $uid;
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
     */
    protected function addColPosListLayoutItems($pageId, $items): array
    {
        $layout = $this->getSelectedBackendLayout($pageId);
        if (isset($layout, $layout['__items'])) {
            $items = $layout['__items'];
        }
        if ($this->addingItemsForContent) {
            $parentRecordUid = ColumnNumberUtility::calculateParentUid((integer) ($this->record['colPos'] ?? 0));
            if ($parentRecordUid > 0) {
                $parentRecord = $this->loadRecordFromTable('tt_content', $parentRecordUid);
                if (!$parentRecord) {
                    return $items;
                }
                $provider = $this->resolvePrimaryProviderForRecord('tt_content', $parentRecord);
                if ($provider) {
                    $label = $this->getLanguageService()->sL(
                        'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.backendLayout.columnsInParent'
                    );
                    $items = array_merge(
                        $items,
                        [
                            (new SelectOption($label, '--div--'))->toArray()
                        ],
                        $provider->getGrid($parentRecord)->buildExtendedBackendLayoutArray($parentRecordUid)['__items']
                    );
                }
            }
        }
        return $items;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadRecordFromTable(string $table, int $uid): ?array
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
        $query = $queryBuilder->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $uid));
        $query->getRestrictions()->removeAll();
        /** @var array[] $results */
        $results = DoctrineQueryProxy::fetchAllAssociative(DoctrineQueryProxy::executeQueryOnQueryBuilder($query));
        return $results[0] ?? null;
    }

    protected function resolvePrimaryProviderForRecord(string $table, array $record): ?GridProviderInterface
    {
        /** @var ProviderResolver $providerResolver */
        $providerResolver = GeneralUtility::makeInstance(ProviderResolver::class);
        return $providerResolver->resolvePrimaryConfigurationProvider(
            $table,
            null,
            $record,
            null,
            [GridProviderInterface::class]
        );
    }
}
