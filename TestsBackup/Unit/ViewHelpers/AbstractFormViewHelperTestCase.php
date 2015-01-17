<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;
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
 * ************************************************************* */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase as BaseTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
abstract class AbstractFormViewHelperTestCase extends BaseTestCase {

	/**
	 * @test
	 */
	public function canCreateViewHelperInstanceAndRenderWithoutArguments() {
		$instance = $this->buildViewHelperInstance($this->defaultArguments);
		$this->assertInstanceOf($this->getViewHelperClassName(), $instance);
		$instance->render();
	}

	/**
	 * @test
	 */
	public function canGetFormInstanceFromTemplateVariables() {
		$form = Form::create();
		$instance = $this->createMockedInstanceForVariableContainerTests();
		ObjectAccess::getProperty($instance, 'viewHelperVariableContainer', TRUE)->expects($this->any())->method('exists')->will($this->returnValue(FALSE));
		ObjectAccess::getProperty($instance, 'templateVariableContainer', TRUE)->expects($this->any())->method('exists')->will($this->returnValue(TRUE));
		ObjectAccess::getProperty($instance, 'templateVariableContainer', TRUE)->expects($this->any())->method('get')->will($this->returnValue($form));
		$output = $this->callInaccessibleMethod($instance, 'getForm');
		$this->assertSame($form, $output);
	}

	/**
	 * @test
	 */
	public function canGetContainerInstanceFromTemplateVariables() {
		$sheet = Form\Container\Sheet::create();
		$instance = $this->createMockedInstanceForVariableContainerTests();
		ObjectAccess::getProperty($instance, 'viewHelperVariableContainer', TRUE)->expects($this->any())->method('exists')->will($this->returnValue(FALSE));
		ObjectAccess::getProperty($instance, 'templateVariableContainer', TRUE)->expects($this->any())->method('exists')->will($this->returnValue(TRUE));
		ObjectAccess::getProperty($instance, 'templateVariableContainer', TRUE)->expects($this->any())->method('get')->will($this->returnValue($sheet));
		$output = $this->callInaccessibleMethod($instance, 'getContainer');
		$this->assertSame($sheet, $output);
	}

	/**
	 * @test
	 */
	public function canGetGridWhenItDoesNotExistButStorageDoes() {
		$form = Form::create();
		$instance = $this->createMockedInstanceForVariableContainerTests(array('getForm'));
		$instance->expects($this->once())->method('getForm')->will($this->returnValue($form));
		ObjectAccess::getProperty($instance, 'viewHelperVariableContainer', TRUE)->expects($this->any())->method('exists')->will($this->returnValue(TRUE));
		ObjectAccess::getProperty($instance, 'viewHelperVariableContainer', TRUE)->expects($this->any())->method('get')->will($this->returnValue(array()));
		$output = $this->callInaccessibleMethod($instance, 'getGrid');
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $output);
	}

	/**
	 * @test
	 */
	public function canGetGridWhenItExistInStorage() {
		$form = Form::create();
		$grid = Form\Container\Grid::create();
		$grid->setName('test');
		$grids = array(
			'test' => $grid
		);
		$instance = $this->createMockedInstanceForVariableContainerTests(array('getForm'));
		$instance->expects($this->any())->method('getForm')->will($this->returnValue($form));
		ObjectAccess::getProperty($instance, 'viewHelperVariableContainer', TRUE)->expects($this->any())->method('exists')->will($this->returnValue(TRUE));
		ObjectAccess::getProperty($instance, 'viewHelperVariableContainer', TRUE)->expects($this->any())->method('get')->will($this->returnValue($grids));
		$output = $this->callInaccessibleMethod($instance, 'getGrid', 'test');
		$this->assertSame($grid, $output);
	}

	/**
	 * @test
	 */
	public function canGetGridWhenItDoesNotExistAndStorageDoesNotExist() {
		$form = Form::create();
		$instance = $this->createMockedInstanceForVariableContainerTests();
		ObjectAccess::getProperty($instance, 'viewHelperVariableContainer', TRUE)->expects($this->any())->method('exists')->will($this->returnValue(FALSE));
		ObjectAccess::getProperty($instance, 'templateVariableContainer', TRUE)->expects($this->any())->method('exists')->will($this->returnValue(TRUE));
		ObjectAccess::getProperty($instance, 'templateVariableContainer', TRUE)->expects($this->any())->method('get')->will($this->returnValue($form));
		$output = $this->callInaccessibleMethod($instance, 'getGrid');
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $output);
	}

	/**
	 * @param array $methods
	 * @return object
	 */
	protected function createMockedInstanceForVariableContainerTests($methods = array()) {
		$instance = $this->getMock(substr(get_class($this), 0, -4), $methods);
		$mockViewHelperVariableContainer = $this->getMock('TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer', array('exists', 'get', 'add'));
		$mockTemplateVariableContainer = $this->getMock('TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer', array('exists', 'get', 'add'));
		ObjectAccess::setProperty($instance, 'viewHelperVariableContainer', $mockViewHelperVariableContainer, TRUE);
		ObjectAccess::setProperty($instance, 'templateVariableContainer', $mockTemplateVariableContainer, TRUE);
		ObjectAccess::setProperty($instance, 'objectManager', $this->objectManager, TRUE);
		return $instance;
	}

}
