<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\RelationFieldInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Select one or more database records from several tables.
 *
 * In comparison to ``RelationViewHelper``, only the list with selected
 * items is visible on the left.
 * The right side contains a "browse" button that opens a popup.
 *
 * Related: ``RelationViewHelper``.
 *
 * ### Example: Select from multiple tables
 *
 * Select pages and content elements:
 *
 *     <flux:field.multiRelation name="settings.records"
 *                               table="pages,tt_content"
 *                               maxItems="5" />
 *
 * ### Example: Content element selector with autocomplete
 *
 * Add a wizard to search for content elements, instead of opening a popup:
 *
 *     <flux:field.multiRelation name="settings.elements"
 *                               table="tt_content"
 *                               maxItems="5">
 *         <flux:wizard.suggest />
 *     </flux:field.multiRelation>
 *
 * DEPRECATED - use flux:field instead
 * @deprecated Will be removed in Flux 10.0
 */
class MultiRelationViewHelper extends AbstractRelationFieldViewHelper
{

    /**
     * @param RenderingContextInterface $renderingContext
     * @param iterable $arguments
     * @return RelationFieldInterface
     */
    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments)
    {
        return static::getPreparedComponent('MultiRelation', $renderingContext, $arguments);
    }
}
