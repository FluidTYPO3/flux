<?php
namespace FluidTYPO3\Flux\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * Outlet Interface
 *
 * Implemented by all Outlet types.
 */
interface OutletInterface
{
    public function setEnabled(bool $enabled): self;
    public function getEnabled(): bool;
    public function isValid(): bool;
    public function getValidationResults(): Result;
    public function produce(): array;
    public function setPipesIn(array $pipes): self;
    public function fill(array $data): self;

    /**
     * @return ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface
     */
    public function getView();

    /**
     * @param ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     */
    public function setView($view): self;

    /**
     * @return PipeInterface[]
     */
    public function getPipesIn(): array;

    /**
     * @param PipeInterface[] $pipes
     */
    public function setPipesOut(array $pipes): self;

    /**
     * @return PipeInterface[]
     */
    public function getPipesOut(): array;

    public function addPipeIn(PipeInterface $pipe): self;
    public function addPipeOut(PipeInterface $pipe): self;
    public function addArgument(OutletArgument $argument): self;

    /**
     * @param OutletArgument[] $arguments
     */
    public function setArguments(array $arguments): self;

    /**
     * @return OutletArgument[]
     */
    public function getArguments(): array;
}
