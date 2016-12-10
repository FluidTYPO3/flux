<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Interface for Pipes which process data for Outlets.
 */
interface ViewAwarePipeInterface extends PipeInterface
{

    /**
     * @param ViewInterface $view
     * @return void
     */
    public function setView($view);
}
