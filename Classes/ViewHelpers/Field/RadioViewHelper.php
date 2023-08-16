<?php
declare(strict_types=1);
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
 */
class RadioViewHelper extends SelectViewHelper
{
    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments): Radio
    {
        /** @var array $arguments */
        /** @var Radio $component */
        $component = static::getPreparedComponent(Radio::class, $renderingContext, $arguments);
        $component->setItems($arguments['items']);
        return $component;
    }
}
