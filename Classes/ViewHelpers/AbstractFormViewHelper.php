<?php
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Form\Container\Grid;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Base class for all FlexForm related ViewHelpers
 */
abstract class AbstractFormViewHelper extends AbstractViewHelper implements CompilableInterface {

	const SCOPE = 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper';
	const SCOPE_VARIABLE_EXTENSIONNAME = 'extensionName';
	const SCOPE_VARIABLE_FORM = 'form';
	const SCOPE_VARIABLE_CONTAINER = 'container';
	const SCOPE_VARIABLE_GRIDS = 'grids';

	/**
	 * @return void
	 */
	public function render() {
		return static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return void
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
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
	public function renderChildren() {
		// Make sure the current extension name always propagates to child nodes
		static::setExtensionNameInRenderingContext($this->renderingContext, $this->getExtensionName());

		return parent::renderChildren();
	}

	/**
	 * @return string
	 */
	protected function getExtensionName() {
		return static::getExtensionNameFromRenderingContextOrArguments($this->renderingContext, $this->arguments);
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param string $name
	 * @throws InvalidVariableException
	 */
	protected static function setExtensionNameInRenderingContext(RenderingContextInterface $renderingContext, $name) {
		$renderingContext->getViewHelperVariableContainer()->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME, $name);
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
		if (TRUE === isset($arguments[static::SCOPE_VARIABLE_EXTENSIONNAME])) {
			return $arguments[static::SCOPE_VARIABLE_EXTENSIONNAME];
		}
		$viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
		if (TRUE === $viewHelperVariableContainer->exists(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME)) {
			return $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME);
		}
		$controllerContext = $renderingContext->getControllerContext();
		if (NULL !== $controllerContext) {
			$controllerExtensionName = $controllerContext->getRequest()->getControllerExtensionName();
			$controllerVendorName = $controllerContext->getRequest()->getControllerVendorName();
			return (FALSE === empty($controllerVendorName) ? $controllerVendorName . '.' : '') . $controllerExtensionName;
		}
		return 'FluidTYPO3.Flux';
	}

	/**
	 * @return Form
	 */
	protected function getForm() {
		return static::getFormFromRenderingContext($this->renderingContext);
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @throws InvalidVariableException
	 * @return FormInterface
	 */
	public static function getFormFromRenderingContext(RenderingContextInterface $renderingContext) {
		$viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
		$templateVariableContainer = $renderingContext->getTemplateVariableContainer();
		if (TRUE === $viewHelperVariableContainer->exists(static::SCOPE, static::SCOPE_VARIABLE_FORM)) {
			$form = $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_FORM);
		} elseif (TRUE === $templateVariableContainer->exists(static::SCOPE_VARIABLE_FORM)) {
			$form = $templateVariableContainer->get(static::SCOPE_VARIABLE_FORM);
		} else {
			$form = Form::create(array(
				'extensionName' => $renderingContext->getControllerContext()->getRequest()->getControllerExtensionName()
			));
			$viewHelperVariableContainer->add(static::SCOPE, static::SCOPE_VARIABLE_FORM, $form);
		}
		return $form;
	}

	/**
	 * @param string $gridName
	 * @return Grid
	 */
	protected function getGrid($gridName = 'grid') {
		return static::getGridFromRenderingContext($this->renderingContext, $gridName);
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param string $gridName
	 * @throws InvalidVariableException
	 * @return Grid
	 */
	protected static function getGridFromRenderingContext(RenderingContextInterface $renderingContext, $gridName = 'grid') {
		$viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
		$form = static::getFormFromRenderingContext($renderingContext);
		if (FALSE === $viewHelperVariableContainer->exists(static::SCOPE, static::SCOPE_VARIABLE_GRIDS)) {
			$grid = $form->createContainer('Grid', $gridName, 'Grid: ' . $gridName);
			$grids = array($gridName => $grid);
			$viewHelperVariableContainer->add(static::SCOPE, static::SCOPE_VARIABLE_GRIDS, $grids);
		} else {
			$grids = $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_GRIDS);
			if (TRUE === isset($grids[$gridName])) {
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
	protected function getContainer() {
		return static::getContainerFromRenderingContext($this->renderingContext);
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @throws InvalidVariableException
	 * @return mixed
	 */
	protected static function getContainerFromRenderingContext(RenderingContextInterface $renderingContext) {
		$viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
		$templateVariableContainer = $renderingContext->getTemplateVariableContainer();
		if (TRUE === $viewHelperVariableContainer->exists(static::SCOPE, static::SCOPE_VARIABLE_CONTAINER)) {
			$container = $viewHelperVariableContainer->get(static::SCOPE, static::SCOPE_VARIABLE_CONTAINER);
		} elseif (TRUE === $templateVariableContainer->exists(static::SCOPE_VARIABLE_CONTAINER)) {
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
	protected function setContainer(FormInterface $container) {
		static::setContainerInRenderingContext($this->renderingContext, $container);
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param FormInterface $container
	 * @throws InvalidVariableException
	 * @return void
	 */
	protected static function setContainerInRenderingContext(RenderingContextInterface $renderingContext, FormInterface $container) {
		$renderingContext->getViewHelperVariableContainer()->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_CONTAINER, $container);
		if (TRUE === $renderingContext->getTemplateVariableContainer()->exists(static::SCOPE_VARIABLE_CONTAINER)) {
			$renderingContext->getTemplateVariableContainer()->remove(static::SCOPE_VARIABLE_CONTAINER);
		}
		$renderingContext->getTemplateVariableContainer()->add(static::SCOPE_VARIABLE_CONTAINER, $container);
	}

}
