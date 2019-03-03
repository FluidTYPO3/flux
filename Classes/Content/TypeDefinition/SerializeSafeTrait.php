<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content\TypeDefinition;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;

/**
 * Accompanying trait for same-named interface
 *
 * Ensures that getter methods for grid and form have been
 * called before the object is serialized.
 */
trait SerializeSafeTrait
{
    abstract public function getForm(): Form;
    abstract public function getGrid(): ?Form\Container\Grid;

    public function __sleep()
    {
        $this->getForm();
        $this->getGrid();
        return array_keys(get_class_vars(static::class));
    }
}
