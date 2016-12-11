<?php
namespace FluidTYPO3\Flux\ViewHelpers\Grid;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Flexform Grid Row ViewHelper
 *
 * Used inside `<flux:grid>` tags.
 * Usually contains `<flux:grid.column>` tags.
 *
 * See `<flux:grid>` for an example.
 */
class RowViewHelper extends AbstractFormViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Optional name of this row - defaults to "row"', false, 'row');
        $this->registerArgument(
            'label',
            'string',
            'Optional label for this row - defaults to an LLL value (reported if it is missing)'
        );
        $this->registerArgument(
            'variables',
            'array',
            'Freestyle variables which become assigned to the resulting Component - can then be read from that ' .
            'Component outside this Fluid template and in other templates using the Form object from this template',
            false,
            []
        );
        $this->registerArgument(
            'extensionName',
            'string',
            'If provided, enables overriding the extension context for this and all child nodes. The extension ' .
            'name is otherwise automatically detected from rendering context.'
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Row
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        $name = ('row' === $arguments['name'] ? uniqid('row', true) : $arguments['name']);
        /** @var Row $column */
        $row = static::getFormFromRenderingContext($renderingContext)
            ->createContainer('Row', $name, $arguments['label']);
        $row->setExtensionName(static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments));
        $row->setVariables($arguments['variables']);
        return $row;
    }
}
