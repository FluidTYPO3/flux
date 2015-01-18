<?php
namespace FluidTYPO3\Flux\ViewHelpers;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
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
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @return void
	 */
	public function render() {
		$component = $this->getComponent();
		$container = $this->getContainer();
		$container->add($component);
		$this->setContainer($component);
		if (FALSE === $this->hasArgument(self::SCOPE_VARIABLE_EXTENSIONNAME)) {
			$this->renderChildren();
		} else {
			// render with stored extension context, backing up any stored variable from parents.
			$extensionName = NULL;
			if (TRUE === $this->viewHelperVariableContainer->exists(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME)) {
				$extensionName = $this->viewHelperVariableContainer->get(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME);
			}
			$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME, $extensionName);
			$this->renderChildren();
			$this->viewHelperVariableContainer->remove(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME);
			if (NULL !== $extensionName) {
				$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME, $extensionName);
			}
		}
		$this->setContainer($container);
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
		return $this->controllerContext->getRequest()->getControllerExtensionName();
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
			$form = $this->objectManager->get('FluidTYPO3\Flux\Form');
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
