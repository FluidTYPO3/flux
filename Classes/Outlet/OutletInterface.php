<?php
namespace FluidTYPO3\Flux\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;

/**
 * Outlet Interface
 *
 * Implemented by all Outlet types.
 */
interface OutletInterface
{

    /**
     * @param boolean $enabled
     * @return void
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
     * @return OutletInterface
     * @return void
     */
    public function setPipesIn(array $pipes);

    /**
     * @return PipeInterface[]
     */
    public function getPipesIn();

    /**
     * @param PipeInterface[] $pipes
     * @return OutletInterface
     * @return void
     */
    public function setPipesOut(array $pipes);

    /**
     * @return PipeInterface[]
     */
    public function getPipesOut();

    /**
     * @param PipeInterface $pipe
     * @return OutletInterface
     */
    public function addPipeIn(PipeInterface $pipe);

    /**
     * @param PipeInterface $pipe
     * @return OutletInterface
     */
    public function addPipeOut(PipeInterface $pipe);
}
