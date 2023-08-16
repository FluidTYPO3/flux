<?php
declare(strict_types=1);
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
 */
class DateTimeViewHelper extends AbstractFieldViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
    }

    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments): Input
    {
        /** @var Input $input */
        $input = static::getPreparedComponent('DateTime', $renderingContext, $arguments);
        return $input;
    }
}
