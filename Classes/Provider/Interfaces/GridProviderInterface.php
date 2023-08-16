<?php
declare(strict_types=1);
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
    public function getGrid(array $row): Form\Container\Grid;
    public function setGrid(Form\Container\Grid $grid): self;
}
