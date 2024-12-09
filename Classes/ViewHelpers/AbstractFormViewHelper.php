<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\FormInterface;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Base class for all FlexForm related ViewHelpers
 */
abstract class AbstractFormViewHelper extends AbstractViewHelper
{
    const SCOPE = FormViewHelper::class;
    const SCOPE_VARIABLE_EXTENSIONNAME = 'extensionName';
    const SCOPE_VARIABLE_FORM = 'form';
    const SCOPE_VARIABLE_CONTAINER = 'container';
    const SCOPE_VARIABLE_GRIDS = 'grids';

    /**
     * @return string
     */
    public function render()
    {
        return static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    protected function callRenderMethod(): string
    {
        return static::renderStatic(
            $this->arguments instanceof ArgumentCollection ? $this->arguments->getArrayCopy() : $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $container = static::getContainerFromRenderingContext($renderingContext);
        if (method_exists(static::class, 'getComponent')) {
            $component = static::getComponent($renderingContext, $arguments, $renderChildrenClosure);
            // rendering child nodes with Form's last sheet as active container
            static::setContainerInRenderingContext($renderingContext, $component);
        }
        $renderChildrenClosure();
        static::setContainerInRenderingContext($renderingContext, $container);

        return '';
    }

    public static function getComponent(
        RenderingContextInterface $renderingContext,
        iterable $arguments
    ): FormInterface {
        return Form::create();
    }

    /**
     * @return mixed
     */
    public function renderChildren()
    {
        // Make sure the current extension name always propagates to child nodes
        static::setExtensionNameInRenderingContext(
            $this->renderingContext,
            static::getExtensionNameFromRenderingContextOrArguments($this->renderingContext, $this->arguments)
        );

        return parent::renderChildren();
    }

    protected static function setExtensionNameInRenderingContext(
        RenderingContextInterface $renderingContext,
        string $name
    ): void {
        $renderingContext->getViewHelperVariableContainer()
            ->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME, $name);
    }

    protected static function getExtensionNameFromRenderingContextOrArguments(
        RenderingContextInterface $renderingContext,
        array $arguments
    ): string {
        if ($extensionName = $arguments[static::SCOPE_VARIABLE_EXTENSIONNAME] ?? false) {
            return (string) $extensionName;
        }
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        if ($extensionName = $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME)) {
            return is_scalar($extensionName) ? (string) $extensionName : 'FluidTYPO3.Flux';
        }
        $request = null;
        $controllerContext = null;
        if (method_exists($renderingContext, 'getControllerContext')) {
            $controllerContext = $renderingContext->getControllerContext();
            if ($controllerContext && $controllerContext->getRequest()) {
                $request = $controllerContext->getRequest();
            }
        } elseif (method_exists($renderingContext, 'getRequest')) {
            $request = $renderingContext->getRequest();
        }
        if (!$request && $controllerContext) {
            $request = $controllerContext->getRequest();
            /** @var string|null $controllerExtensionName */
            $controllerExtensionName = $request->getControllerExtensionName();
            return $controllerExtensionName ?? 'FluidTYPO3.Flux';
        }
        return 'FluidTYPO3.Flux';
    }

    public static function getFormFromRenderingContext(RenderingContextInterface $renderingContext): Form
    {
        /** @var Form|null $form */
        $form = $renderingContext->getViewHelperVariableContainer()->get(static::SCOPE, static::SCOPE_VARIABLE_FORM);
        if (!$form) {
            $form = Form::create([
                'extensionName' => $renderingContext->getViewHelperVariableContainer()->get(
                    FormViewHelper::SCOPE,
                    FormViewHelper::SCOPE_VARIABLE_EXTENSIONNAME
                )
            ]);
            $renderingContext->getViewHelperVariableContainer()->add(static::SCOPE, static::SCOPE_VARIABLE_FORM, $form);
        }
        return $form;
    }

    protected static function getGridFromRenderingContext(
        RenderingContextInterface $renderingContext,
        string $gridName = 'grid'
    ): Grid {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        /** @var Grid[] $grids */
        $grids = (array) $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_GRIDS);

        if (!isset($grids[$gridName])) {
            $grids[$gridName] = Grid::create(['name' => $gridName]);
            $viewHelperVariableContainer->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_GRIDS, $grids);
        }
        /** @var Grid $grid */
        $grid = $grids[$gridName];
        return $grid;
    }

    protected static function getContainerFromRenderingContext(
        RenderingContextInterface $renderingContext
    ): FormInterface {
        /** @var Form\FormInterface|null $container */
        $container = $renderingContext->getViewHelperVariableContainer()->get(
            static::SCOPE,
            static::SCOPE_VARIABLE_CONTAINER
        );
        return $container ?? static::getFormFromRenderingContext($renderingContext);
    }

    protected static function setContainerInRenderingContext(
        RenderingContextInterface $renderingContext,
        FormInterface $container
    ): void {
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(
            static::SCOPE,
            static::SCOPE_VARIABLE_CONTAINER,
            $container
        );
    }
}
