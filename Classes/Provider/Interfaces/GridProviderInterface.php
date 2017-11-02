<?php
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;

/**
 * Interface GridProviderInterface
 *
 * Contract for Providers which are capable of returning
 * Grid instances.
 */
interface GridProviderInterface
{
    /**
     * Returns a \FluidTYPO3\Flux\Form\Container\Grid as required by this record.
     *
     * @param array $row
     * @return Form\Container\Grid
     */
    public function getGrid(array $row);

    /**
     * @param Form\Container\Grid $grid
     * @return $this
     */
    public function setGrid(Form\Container\Grid $grid);
}
