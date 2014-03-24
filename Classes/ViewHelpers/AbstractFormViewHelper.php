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
		$this->renderChildren();
		$this->setContainer($container);
	}

	/**
	 * @return Form
	 */
	protected function getForm() {
		if (TRUE === $this->viewHelperVariableContainer->exists(self::SCOPE, 'form')) {
			$form = $this->viewHelperVariableContainer->get(self::SCOPE, 'form');
		} elseif (TRUE === $this->templateVariableContainer->exists('form')) {
			$form = $this->templateVariableContainer->get('form');
		} else {
			$form = $this->objectManager->get('FluidTYPO3\Flux\Form');
			$this->viewHelperVariableContainer->add(self::SCOPE, 'form', $form);
		}
		return $form;
	}

	/**
	 * @param string $gridName
	 * @return Form
	 */
	protected function getGrid($gridName = 'grid') {
		$form = $this->getForm();
		if (FALSE === $this->viewHelperVariableContainer->exists(self::SCOPE, 'grids')) {
			$grid = $form->createContainer('Grid', $gridName, 'Grid: ' . $gridName);
			$grids = array($gridName => $grid);
			$this->viewHelperVariableContainer->add(self::SCOPE, 'grids', $grids);
		} else {
			$grids = $this->viewHelperVariableContainer->get(self::SCOPE, 'grids');
			if (TRUE === isset($grids[$gridName])) {
				$grid = $grids[$gridName];
			} else {
				$grid = $form->createContainer('Grid', $gridName, 'Grid: ' . $gridName);
				$grids[$gridName] = $grid;
				$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, 'grids', $grids);
			}
		}
		return $grid;
	}

	/**
	 * @return ContainerInterface
	 */
	protected function getContainer() {
		if (TRUE === $this->viewHelperVariableContainer->exists(self::SCOPE, 'container')) {
			$container = $this->viewHelperVariableContainer->get(self::SCOPE, 'container');
		} elseif (TRUE === $this->templateVariableContainer->exists('container')) {
			$container = $this->templateVariableContainer->get('container');
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
		$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, 'container', $container);
		if (TRUE === $this->templateVariableContainer->exists('container')) {
			$this->templateVariableContainer->remove('container');
		}
		$this->templateVariableContainer->add('container', $container);
	}

}
