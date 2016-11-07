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
 * trait to implement the ViewAwarePipeInterface. must be paired with the ViewAwarePipeInterface to be recognised
 */
trait ViewAwarePipeTrait
{

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * @param ViewInterface $view
     * @return void
     */
    public function setView($view)
    {
        $this->view = $view;
    }
}
