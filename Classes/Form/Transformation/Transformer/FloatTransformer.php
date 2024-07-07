<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Attribute\DataTransformer;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Form\Transformation\DataTransformerInterface;

/**
 * Float Transformer
 */
#[DataTransformer('flux.datatransformer.float')]
class FloatTransformer implements DataTransformerInterface
{
    public function canTransformToType(string $type): bool
    {
        return $type === 'float' || $type === 'decimal' || $type === 'double';
    }

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @param string|array $value
     * @return float
     */
    public function transform(FormInterface $component, string $type, $value)
    {
        return floatval($value);
    }
}
