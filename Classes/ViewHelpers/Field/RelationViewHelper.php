<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Select one or multiple database records from one table.
 *
 * Features a two-list style that shows all selectable items
 * in a list on the right side,
 * and all selected items in a list on the left side.
 *
 * Related: ``MultiRelationViewHelper``.
 *
 * ### Example: Select a content element
 *
 * If put inside a fluidpages "Configuration" section, the following code
 * allows selecting a content element from the current page:
 *
 *     <flux:field.relation name="settings.content"
 *                          table="tt_content"
 *                          condition="AND tt_content.pid = ###THIS_UID###" />
 *
 * A list of allowed markers for the `condition` can be found in the documentation at:
 *
 * https://docs.typo3.org/typo3cms/TCAReference/ColumnsConfig/Type/Select.html#foreign-table-where
 */
class RelationViewHelper extends AbstractRelationFieldViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
    }
}
