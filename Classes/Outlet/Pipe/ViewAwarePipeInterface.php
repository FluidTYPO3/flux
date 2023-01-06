<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * Interface for Pipes which process data for Outlets.
 */
interface ViewAwarePipeInterface extends PipeInterface
{
    /**
     * @param ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     */
    public function setView($view): self;

    /**
     * @return ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface
     */
    public function getView();
}
