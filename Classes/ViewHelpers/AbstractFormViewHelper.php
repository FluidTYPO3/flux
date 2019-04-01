<?php
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
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Base class for all FlexForm related ViewHelpers
 */
abstract class AbstractFormViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    const SCOPE = FormViewHelper::class;
    const SCOPE_VARIABLE_EXTENSIONNAME = 'extensionName';
    const SCOPE_VARIABLE_FORM = 'form';
    const SCOPE_VARIABLE_CONTAINER = 'container';
    const SCOPE_VARIABLE_GRIDS = 'grids';

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $container = static::getContainerFromRenderingContext($renderingContext);
        $component = static::getComponent($renderingContext, $arguments, $renderChildrenClosure);
        // rendering child nodes with Form's last sheet as active container
        static::setContainerInRenderingContext($renderingContext, $component);
        $renderChildrenClosure();
        static::setContainerInRenderingContext($renderingContext, $container);
    }

    /**
     * @return string
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

    /**
     * @param RenderingContextInterface $renderingContext
     * @param string $name
     */
    protected static function setExtensionNameInRenderingContext(RenderingContextInterface $renderingContext, $name)
    {
        $renderingContext->getViewHelperVariableContainer()
            ->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME, $name);
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return string
     */
    protected static function getExtensionNameFromRenderingContextOrArguments(
        RenderingContextInterface $renderingContext,
        array $arguments
    ) {
        if (true === isset($arguments[static::SCOPE_VARIABLE_EXTENSIONNAME])) {
            return $arguments[static::SCOPE_VARIABLE_EXTENSIONNAME];
        }
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        if (true === $viewHelperVariableContainer->exists(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME)) {
            return $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME);
        }
        $controllerContext = $renderingContext->getControllerContext();
        if (null !== $controllerContext) {
            $controllerExtensionName = $controllerContext->getRequest()->getControllerExtensionName();
            $controllerVendorName = $controllerContext->getRequest()->getControllerVendorName();
            return (!empty($controllerVendorName) ? $controllerVendorName . '.' : '') . $controllerExtensionName;
        }
        return 'FluidTYPO3.Flux';
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return Form
     */
    public static function getFormFromRenderingContext(RenderingContextInterface $renderingContext)
    {
        $form = $renderingContext->getViewHelperVariableContainer()->get(static::SCOPE, static::SCOPE_VARIABLE_FORM);
        if (!$form) {
            $form = Form::create([
                'extensionName' => $renderingContext->getControllerContext()->getRequest()->getControllerExtensionName()
            ]);
            $renderingContext->getViewHelperVariableContainer()->add(static::SCOPE, static::SCOPE_VARIABLE_FORM, $form);
        }
        return $form;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param string $gridName
     * @return Grid
     */
    protected static function getGridFromRenderingContext(
        RenderingContextInterface $renderingContext,
        $gridName = 'grid'
    ) {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        $grids = (array) $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_GRIDS);

        if (!isset($grids[$gridName])) {
            $grids[$gridName] = Grid::create(['name' => $gridName]);
            $viewHelperVariableContainer->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_GRIDS, $grids);
        }
        return $grids[$gridName];
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return Form\ContainerInterface
     */
    protected static function getContainerFromRenderingContext(RenderingContextInterface $renderingContext)
    {
        return $renderingContext->getViewHelperVariableContainer()
            ->get(static::SCOPE, static::SCOPE_VARIABLE_CONTAINER)
            ?? static::getFormFromRenderingContext($renderingContext);
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param FormInterface $container
     * @return void
     */
    protected static function setContainerInRenderingContext(
        RenderingContextInterface $renderingContext,
        FormInterface $container
    ) {
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_CONTAINER, $container);
    }
}
