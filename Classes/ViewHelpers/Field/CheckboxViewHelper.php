<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Checkbox;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Checkbox FlexForm field ViewHelper
 *
 * DEPRECATED - use flux:field instead
 * @deprecated Will be removed in Flux 10.0
 */
class CheckboxViewHelper extends AbstractFieldViewHelper
{
    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments): Checkbox
    {
        /** @var Checkbox $checkbox */
        $checkbox = static::getPreparedComponent(Checkbox::class, $renderingContext, $arguments);
        return $checkbox;
    }
}
