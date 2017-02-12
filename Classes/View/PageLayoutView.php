<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

class PageLayoutView extends \TYPO3\CMS\Backend\View\PageLayoutView
{
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

    /**
     * Public access version of parent's method
     *
     * @param array $rowArray
     */
    public function generateTtContentDataArray(array $rowArray)
    {
        parent::generateTtContentDataArray($rowArray);
    }
}
