<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\DoctrineQueryProxy;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendLayoutView extends \TYPO3\CMS\Backend\View\BackendLayoutView
{
    protected ?GridProviderInterface $provider = null;
    protected array $record = [];

    public function setProvider(GridProviderInterface $provider): void
    {
        $this->provider = $provider;
    }

    public function setRecord(array $record): void
    {
        $this->record = $record;
    }

    /**
     * @param int $pageId
     */
    public function getSelectedBackendLayout($pageId): ?array
    {
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

    /**
     * @codeCoverageIgnore
     */
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
