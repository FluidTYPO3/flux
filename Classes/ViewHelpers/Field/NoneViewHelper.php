<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\None;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * None field ViewHelper
 *
 * Makes a read-only component which supports a default value
 * but which cannot be edited.
 *
 * DEPRECATED - use flux:field instead
 * @deprecated Will be removed in Flux 10.0
 */
class NoneViewHelper extends AbstractFieldViewHelper
{

    /**
     * @param RenderingContextInterface $renderingContext
     * @param iterable $arguments
     * @return None
     */
    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments)
    {
        return static::getPreparedComponent('None', $renderingContext, $arguments);
    }
}
