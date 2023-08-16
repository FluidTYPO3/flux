<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Grid container ViewHelper.
 *
 * Use `<flux:grid.row>` with nested `<flux:grid.column>` tags
 * to define a tabular layout.
 *
 * The grid is then rendered automatically in the preview section
 * of the content element, or as page columns if used in page templates.
 *
 * For frontend rendering, use `flux:content.render`.
 *
 * ### Define Page and Content elements
 *
 * Name is used to identify columns and fetch e.g. translations from XLF files.
 *
 *     <flux:grid>
 *         <flux:grid.row>
 *             <flux:grid.column colPos="0" name="Main" colspan="3" style="width: 75%" />
 *             <flux:grid.column colPos="1" name="Secondary" colspan="1" style="width: 25%" />
 *         </flux:grid.row>
 *     </flux:grid>
 *
 * #### Rendering
 *
 *     <v:content.render column="0" />
 *     <v:content.render column="1" />
 */
class GridViewHelper extends AbstractFormViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Optional name of this grid - defaults to "grid"', false, 'grid');
        $this->registerArgument(
            'label',
            'string',
            'Optional label for this grid - defaults to an LLL value (reported if it is missing)'
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
            'If provided, enables overriding the extension context for this and all child nodes. The extension name ' .
            'is otherwise automatically detected from rendering context.'
        );
    }

    protected function callRenderMethod(): string
    {
        $container = static::getContainerFromRenderingContext($this->renderingContext);
        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();
        $extensionName = self::resolveExtensionName($this->renderingContext, $this->arguments);

        /** @var string $gridName */
        $gridName = $this->arguments['name'];
        /** @var string|null $gridLabel */
        $gridLabel = $this->arguments['label'] ?? null;
        $gridVariables = (array) $this->arguments['variables'];

        $grid = static::getGridFromRenderingContext($this->renderingContext, $gridName);
        $grid->setLabel($gridLabel);
        $grid->setVariables($gridVariables);
        $grid->setExtensionName($extensionName);

        $viewHelperVariableContainer->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME, $extensionName);
        static::setContainerInRenderingContext($this->renderingContext, $grid);

        $this->renderChildren();

        $viewHelperVariableContainer->remove(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME);
        static::setContainerInRenderingContext($this->renderingContext, $container);
        return '';
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $container = static::getContainerFromRenderingContext($renderingContext);
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        $extensionName = self::resolveExtensionName($renderingContext, $arguments);

        /** @var string $gridName */
        $gridName = $arguments['name'];
        /** @var string|null $gridLabel */
        $gridLabel = $arguments['label'] ?? null;
        $gridVariables = (array) $arguments['variables'];

        $grid = static::getGridFromRenderingContext($renderingContext, $gridName);
        $grid->setLabel($gridLabel);
        $grid->setVariables($gridVariables);
        $grid->setExtensionName($extensionName);

        $viewHelperVariableContainer->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME, $extensionName);
        static::setContainerInRenderingContext($renderingContext, $grid);

        $renderChildrenClosure();

        $viewHelperVariableContainer->remove(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME);
        static::setContainerInRenderingContext($renderingContext, $container);
        return '';
    }

    private static function resolveExtensionName(RenderingContextInterface $renderingContext, array $arguments): string
    {
        $container = static::getContainerFromRenderingContext($renderingContext);
        return $container->getExtensionName()
            ?? static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments);
    }
}
