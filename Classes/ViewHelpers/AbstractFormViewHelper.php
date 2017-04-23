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
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FormInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Base class for all FlexForm related ViewHelpers
 */
abstract class AbstractFormViewHelper extends AbstractViewHelper implements CompilableInterface
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
        if ($component != $container) {
            $container->add($component);
        }
        // rendering child nodes with Form's last sheet as active container
        static::setContainerInRenderingContext($renderingContext, $component);
        $renderChildrenClosure();
        static::setContainerInRenderingContext($renderingContext, $container);
        static::setExtensionNameInRenderingContext(
            $renderingContext,
            static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments)
        );
    }

    /**
     * @return string
     */
    public function renderChildren()
    {
        // Make sure the current extension name always propagates to child nodes
        static::setExtensionNameInRenderingContext($this->renderingContext, $this->getExtensionName());

        return parent::renderChildren();
    }

    /**
     * @return string
     */
    protected function getExtensionName()
    {
        GeneralUtility::logDeprecatedFunction();
        return static::getExtensionNameFromRenderingContextOrArguments($this->renderingContext, $this->arguments);
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param string $name
     * @throws InvalidVariableException
     */
    protected static function setExtensionNameInRenderingContext(RenderingContextInterface $renderingContext, $name)
    {
        $renderingContext->getViewHelperVariableContainer()
            ->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME, $name);
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @throws InvalidVariableException
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
     * @return Form
     */
    protected function getForm()
    {
        GeneralUtility::logDeprecatedFunction();
        return static::getFormFromRenderingContext($this->renderingContext);
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @throws InvalidVariableException
     * @return Form
     */
    public static function getFormFromRenderingContext(RenderingContextInterface $renderingContext)
    {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        $templateVariableContainer = $renderingContext->getTemplateVariableContainer();
        if (true === $viewHelperVariableContainer->exists(static::SCOPE, static::SCOPE_VARIABLE_FORM)) {
            $form = $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_FORM);
        } elseif (true === $templateVariableContainer->exists(static::SCOPE_VARIABLE_FORM)) {
            $form = $templateVariableContainer->get(static::SCOPE_VARIABLE_FORM);
        } else {
            $form = Form::create([
                'extensionName' => $renderingContext->getControllerContext()->getRequest()->getControllerExtensionName()
            ]);
            $viewHelperVariableContainer->add(static::SCOPE, static::SCOPE_VARIABLE_FORM, $form);
        }
        return $form;
    }

    /**
     * @param string $gridName
     * @return Grid
     */
    protected function getGrid($gridName = 'grid')
    {
        GeneralUtility::logDeprecatedFunction();
        return static::getGridFromRenderingContext($this->renderingContext, $gridName);
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param string $gridName
     * @throws InvalidVariableException
     * @return Grid
     */
    protected static function getGridFromRenderingContext(
        RenderingContextInterface $renderingContext,
        $gridName = 'grid'
    ) {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        $form = static::getFormFromRenderingContext($renderingContext);
        if (false === $viewHelperVariableContainer->exists(static::SCOPE, static::SCOPE_VARIABLE_GRIDS)) {
            $grid = $form->createContainer('Grid', $gridName, 'Grid: ' . $gridName);
            $grids = [$gridName => $grid];
            $viewHelperVariableContainer->add(static::SCOPE, static::SCOPE_VARIABLE_GRIDS, $grids);
        } else {
            $grids = $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_GRIDS);
            if (true === isset($grids[$gridName])) {
                $grid = $grids[$gridName];
            } else {
                $grid = $form->createContainer('Grid', $gridName, 'Grid: ' . $gridName);
                $grids[$gridName] = $grid;
                $viewHelperVariableContainer->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_GRIDS, $grids);
            }
        }
        return $grid;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        GeneralUtility::logDeprecatedFunction();
        return static::getContainerFromRenderingContext($this->renderingContext);
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @throws InvalidVariableException
     * @return mixed
     */
    protected static function getContainerFromRenderingContext(RenderingContextInterface $renderingContext)
    {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        $templateVariableContainer = $renderingContext->getTemplateVariableContainer();
        if (true === $viewHelperVariableContainer->exists(static::SCOPE, static::SCOPE_VARIABLE_CONTAINER)) {
            $container = $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_CONTAINER);
        } elseif (true === $templateVariableContainer->exists(static::SCOPE_VARIABLE_CONTAINER)) {
            $container = $templateVariableContainer->get(static::SCOPE_VARIABLE_CONTAINER);
        } else {
            $form = static::getFormFromRenderingContext($renderingContext);
            $container = $form->last();
            static::setContainerInRenderingContext($renderingContext, $container);
        }
        return $container;
    }

    /**
     * @param FormInterface $container
     * @throws InvalidVariableException
     * @return void
     */
    protected function setContainer(FormInterface $container)
    {
        static::setContainerInRenderingContext($this->renderingContext, $container);
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param FormInterface $container
     * @throws InvalidVariableException
     * @return void
     */
    protected static function setContainerInRenderingContext(
        RenderingContextInterface $renderingContext,
        FormInterface $container
    ) {
        $renderingContext->getViewHelperVariableContainer()
            ->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_CONTAINER, $container);
        if (true === $renderingContext->getTemplateVariableContainer()->exists(static::SCOPE_VARIABLE_CONTAINER)) {
            $renderingContext->getTemplateVariableContainer()->remove(static::SCOPE_VARIABLE_CONTAINER);
        }
        $renderingContext->getTemplateVariableContainer()->add(static::SCOPE_VARIABLE_CONTAINER, $container);
    }
}
