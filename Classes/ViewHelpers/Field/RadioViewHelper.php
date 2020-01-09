<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Radio;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Radio FlexForm field ViewHelper
 *
 * DEPRECATED - use flux:field instead
 * @deprecated Will be removed in Flux 10.0
 */
class RadioViewHelper extends SelectViewHelper
{

    /**
     * @param RenderingContextInterface $renderingContext
     * @param iterable $arguments
     * @return Radio
     */
    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments)
    {
        /** @var Radio $component */
        $component = static::getPreparedComponent('Radio', $renderingContext, $arguments);
        $component->setItems($arguments['items']);
        return $component;
    }
}
