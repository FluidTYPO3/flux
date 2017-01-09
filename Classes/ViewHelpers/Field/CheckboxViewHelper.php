<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Checkbox;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Checkbox FlexForm field ViewHelper
 */
class CheckboxViewHelper extends AbstractFieldViewHelper
{

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Checkbox
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var Checkbox $checkbox */
        $checkbox = static::getPreparedComponent('Checkbox', $renderingContext, $arguments);
        return $checkbox;
    }
}
