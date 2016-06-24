<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\RelationFieldInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Multi-table-relation FlexForm field ViewHelper
 */
class MultiRelationViewHelper extends AbstractRelationFieldViewHelper
{

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return RelationFieldInterface
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        return static::getPreparedComponent('MultiRelation', $renderingContext, $arguments);
    }
}
