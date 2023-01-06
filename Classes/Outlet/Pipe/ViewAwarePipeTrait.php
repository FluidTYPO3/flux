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
 * trait to implement the ViewAwarePipeInterface. must be paired with the ViewAwarePipeInterface to be recognised
 */
trait ViewAwarePipeTrait
{
    /**
     * @var ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface
     */
    protected $view;

    /**
     * @param ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     */
    public function setView($view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @return ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface
     */
    public function getView()
    {
        return $this->view;
    }
}
