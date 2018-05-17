<?php
namespace FluidTYPO3\Flux\View;

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
}
