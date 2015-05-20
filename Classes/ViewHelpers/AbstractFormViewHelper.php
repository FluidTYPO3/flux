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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Base class for all FlexForm related ViewHelpers
 *
 * @package Flux
 * @subpackage ViewHelpers
 */
abstract class AbstractFormViewHelper extends AbstractViewHelper {

	const SCOPE = 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper';
	const SCOPE_VARIABLE_EXTENSIONNAME = 'extensionName';
	const SCOPE_VARIABLE_FORM = 'form';
	const SCOPE_VARIABLE_CONTAINER = 'container';
	const SCOPE_VARIABLE_GRIDS = 'grids';

	/**
	 * @return void
	 */
	public function render() {
		$component = $this->getComponent();
		$container = $this->getContainer();
		$container->add($component);
		// rendering child nodes with Form's last sheet as active container
		$this->setContainer($component);
		$this->renderChildren();
		$this->setContainer($container);
	}

	/**
	 * @return string
	 */
	public function renderChildren() {
		// Make sure the current extension name always propagates to child nodes
		$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME, $this->getExtensionName());

		return parent::renderChildren();
	}

	/**
	 * @return string
	 */
	protected function getExtensionName() {
		if (TRUE === $this->hasArgument(self::SCOPE_VARIABLE_EXTENSIONNAME)) {
			return $this->arguments[self::SCOPE_VARIABLE_EXTENSIONNAME];
		}
		if (TRUE === $this->viewHelperVariableContainer->exists(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME)) {
			return $this->viewHelperVariableContainer->get(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME);
		}
		if (TRUE === isset($this->controllerContext)) {
			return $this->controllerContext->getRequest()->getControllerExtensionName();
		}
		return 'FluidTYPO3.Flux';
	}

	/**
	 * @return Form
	 */
	protected function getForm() {
		if (TRUE === $this->viewHelperVariableContainer->exists(self::SCOPE, self::SCOPE_VARIABLE_FORM)) {
			$form = $this->viewHelperVariableContainer->get(self::SCOPE, self::SCOPE_VARIABLE_FORM);
		} elseif (TRUE === $this->templateVariableContainer->exists(self::SCOPE_VARIABLE_FORM)) {
			$form = $this->templateVariableContainer->get(self::SCOPE_VARIABLE_FORM);
		} else {
			$form = Form::create();
			$this->viewHelperVariableContainer->add(self::SCOPE, self::SCOPE_VARIABLE_FORM, $form);
		}
		return $form;
	}

	/**
	 * @param string $gridName
	 * @return Form
	 */
	protected function getGrid($gridName = 'grid') {
		$form = $this->getForm();
		if (FALSE === $this->viewHelperVariableContainer->exists(self::SCOPE, self::SCOPE_VARIABLE_GRIDS)) {
			$grid = $form->createContainer('Grid', $gridName, 'Grid: ' . $gridName);
			$grids = array($gridName => $grid);
			$this->viewHelperVariableContainer->add(self::SCOPE, self::SCOPE_VARIABLE_GRIDS, $grids);
		} else {
			$grids = $this->viewHelperVariableContainer->get(self::SCOPE, self::SCOPE_VARIABLE_GRIDS);
			if (TRUE === isset($grids[$gridName])) {
				$grid = $grids[$gridName];
			} else {
				$grid = $form->createContainer('Grid', $gridName, 'Grid: ' . $gridName);
				$grids[$gridName] = $grid;
				$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, self::SCOPE_VARIABLE_GRIDS, $grids);
			}
		}
		return $grid;
	}

	/**
	 * @return ContainerInterface
	 */
	protected function getContainer() {
		if (TRUE === $this->viewHelperVariableContainer->exists(self::SCOPE, self::SCOPE_VARIABLE_CONTAINER)) {
			$container = $this->viewHelperVariableContainer->get(self::SCOPE, self::SCOPE_VARIABLE_CONTAINER);
		} elseif (TRUE === $this->templateVariableContainer->exists(self::SCOPE_VARIABLE_CONTAINER)) {
			$container = $this->templateVariableContainer->get(self::SCOPE_VARIABLE_CONTAINER);
		} else {
			$form = $this->getForm();
			$container = $form->last();
			$this->setContainer($container);
		}
		return $container;
	}

	/**
	 * @param FormInterface $container
	 * @return void
	 */
	protected function setContainer(FormInterface $container) {
		$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, self::SCOPE_VARIABLE_CONTAINER, $container);
		if (TRUE === $this->templateVariableContainer->exists(self::SCOPE_VARIABLE_CONTAINER)) {
			$this->templateVariableContainer->remove(self::SCOPE_VARIABLE_CONTAINER);
		}
		$this->templateVariableContainer->add(self::SCOPE_VARIABLE_CONTAINER, $container);
	}

}
