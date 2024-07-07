<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Form\FormInterface;

interface DataTransformerInterface
{
    public function canTransformToType(string $type): bool;
    public function getPriority(): int;

    /**
     * @param FieldInterface|ContainerInterface $component
     * @param mixed $value
     * @return mixed
     */
    public function transform(FormInterface $component, string $type, $value);
}
