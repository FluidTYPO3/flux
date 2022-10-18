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

    /**
     * @param boolean $enabled
     * @return $this
     * @abstract
     */
    public function setEnabled($enabled);

    /**
     * @return boolean
     * @abstract
     */
    public function getEnabled();

    /**
     * @param array $data
     * @return mixed
     * @abstract
     */
    public function fill($data);

    /**
     * @return mixed
     * @abstract
     */
    public function produce();

    /**
     * @param PipeInterface[] $pipes
     * @return $this
     */
    public function setPipesIn(array $pipes);

    /**
     * @return PipeInterface[]
     */
    public function getPipesIn();

    /**
     * @param PipeInterface[] $pipes
     * @return $this
     */
    public function setPipesOut(array $pipes);

    /**
     * @return PipeInterface[]
     */
    public function getPipesOut();

    /**
     * @param PipeInterface $pipe
     * @return $this
     */
    public function addPipeIn(PipeInterface $pipe);

    /**
     * @param PipeInterface $pipe
     * @return $this
     */
    public function addPipeOut(PipeInterface $pipe);

    /**
     * @param OutletArgument $argument
     * @return $this
     */
    public function addArgument(OutletArgument $argument);

    /**
     * @param OutletArgument[] $arguments
     * @return $this
     */
    public function setArguments(array $arguments);

    /**
     * @return OutletArgument[]
     */
    public function getArguments();

    /**
     * @param ViewInterface $view
     * @return $this
     */
    public function setView($view);

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @return Result Validation errors which have occurred.
     */
    public function getValidationResults();
}
