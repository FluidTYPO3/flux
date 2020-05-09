<?php
namespace FluidTYPO3\Flux\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageLayoutView extends \TYPO3\CMS\Backend\View\PageLayoutView
{
    /**
     * @var array
     */
    protected $record = [];

    /**
     * @var GridProviderInterface
     */
    protected $provider;

    /**
     * @param array $pageinfo
     */
    public function setPageinfo($pageinfo)
    {
        $this->pageinfo = $pageinfo;
    }

    /**
     * @return array
     */
    public function getPageinfo()
    {
        return $this->pageinfo;
    }


    public function setProvider(GridProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function setRecord(array $record)
    {
        $this->record = $record;
    }

    /**
     * @return BackendLayoutView
     */
    protected function getBackendLayoutView()
    {
        /** @var BackendLayoutView $view */
        $view = GeneralUtility::makeInstance(BackendLayoutView::class);
        $view->setProvider($this->provider);
        $view->setRecord($this->record);
        return $view;
    }

    protected function getContentRecordsPerColumn($table, $id, array $columns, $additionalWhereClause = '')
    {
        // Vital recursion prevention: each instance of PageLayoutView will attempt to render all records every
        // time - something which has started happening since the "unused content" feature was introduced. To avoid
        // the infinite recursion that happens because of this combined with the recursive usage of PageLayoutView,
        // we restrict the content elements this sub-view is capable of loading.
        $columns = array_filter($columns, 'is_numeric');
        if (empty($columns)) {
            return [];
        }
        $additionalWhereClause .= ' AND colPos IN (' . implode(',', $columns) . ') ';
        return parent::getContentRecordsPerColumn($table, $id, $columns, $additionalWhereClause);
    }
}
