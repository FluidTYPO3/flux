<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Input;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Input FlexForm field ViewHelper
 *
 * DEPRECATED - use flux:field instead
 * @deprecated Will be removed in Flux 10.0
 */
class DateTimeViewHelper extends AbstractFieldViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param iterable $arguments
     * @return Input
     */
    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments)
    {
        /** @var Input $input */
        $input = static::getPreparedComponent('DateTime', $renderingContext, $arguments);
        return $input;
    }
}
