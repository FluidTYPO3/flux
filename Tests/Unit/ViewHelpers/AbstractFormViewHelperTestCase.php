<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
abstract class AbstractFormViewHelperTestCase extends AbstractViewHelperTestCase {

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
		$instance = $this->getMock($this->getViewHelperClassName(), $methods);
		$mockViewHelperVariableContainer = $this->getMock('TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer', array('exists', 'get', 'add'));
		$mockTemplateVariableContainer = $this->getMock('TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer', array('exists', 'get', 'add'));
		ObjectAccess::setProperty($instance, 'viewHelperVariableContainer', $mockViewHelperVariableContainer, TRUE);
		ObjectAccess::setProperty($instance, 'templateVariableContainer', $mockTemplateVariableContainer, TRUE);
		ObjectAccess::setProperty($instance, 'objectManager', $this->objectManager, TRUE);
		return $instance;
	}

}
