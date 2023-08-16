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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageLayoutView extends \TYPO3\CMS\Backend\View\PageLayoutView
{
    protected array $record = [];

    /**
     * @var GridProviderInterface
     */
    protected $provider;

    public function setPageinfo(array $pageinfo): void
    {
        $this->pageinfo = $pageinfo;
    }

    public function getPageinfo(): array
    {
        return $this->pageinfo;
    }

    public function setProvider(GridProviderInterface $provider): void
    {
        $this->provider = $provider;
    }

    public function setRecord(array $record): void
    {
        $this->record = $record;
    }

    protected function getBackendLayoutView(): BackendLayoutView
    {
        /** @var BackendLayoutView $view */
        $view = GeneralUtility::makeInstance(BackendLayoutView::class);
        $view->setProvider($this->provider);
        $view->setRecord($this->record);
        return $view;
    }

    /**
     * @param string $table
     * @param int $id
     * @param array $columns
     * @param string $additionalWhereClause
     * @return array
     * @codeCoverageIgnore
     */
    protected function getContentRecordsPerColumn(
        $table,
        $id,
        array $columns,
        $additionalWhereClause = ''
    ): array {
        // Vital recursion prevention: each instance of PageLayoutView will attempt to render all records every
        // time - something which has started happening since the "unused content" feature was introduced. To avoid
        // the infinite recursion that happens because of this combined with the recursive usage of PageLayoutView,
        // we restrict the content elements this sub-view is capable of loading.
        $columns = array_filter($columns, function ($item) {
            return ctype_digit($item) || is_int($item);
        });
        if (empty($columns)) {
            return [];
        }
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $additionalWhereClause .= ' AND ' . (string) $queryBuilder->expr()->in('colPos', $columns);
        return parent::getContentRecordsPerColumn($table, $id, $columns, $additionalWhereClause);
    }
}
