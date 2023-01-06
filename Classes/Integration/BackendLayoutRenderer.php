<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\View\PageLayoutContext;

class BackendLayoutRenderer extends \TYPO3\CMS\Backend\View\Drawing\BackendLayoutRenderer
{
    /**
     * @var PageLayoutContext
     */
    protected ?PageLayoutContext $transferredContext = null;

    public function getContext(): PageLayoutContext
    {
        $context = null;
        if (property_exists($this, 'context')) {
            $context = $this->context;
        }
        return $context ?? $this->transferredContext;
    }

    public function setContext(PageLayoutContext $context): void
    {
        $this->transferredContext = $context;
        if (property_exists($this, 'context')) {
            $this->context = $context;
        }
    }
}
