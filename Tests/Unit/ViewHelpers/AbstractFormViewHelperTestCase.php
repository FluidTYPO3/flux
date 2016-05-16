<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * AbstractFormViewHelperTestCase
 */
abstract class AbstractFormViewHelperTestCase extends AbstractViewHelperTestCase {

	/**
	 * @return void
	 */
	public function setUp() {
		$this->viewHelperVariableContainer = $this->getMock(
			'TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer',
			array('exists', 'get', 'add')
		);
		$this->templateVariableContainer = $this->getMock(
			'TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer',
			array('exists', 'get', 'add')
		);
		$this->renderingContext = $this->getMock(
			'TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface',
			array('getTemplateVariableContainer', 'getViewHelperVariableContainer', 'getControllerContext')
		);
		$this->controllerContext = $this->getMock(
			'TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext',
			array('getRequest')
		);
		$this->controllerContext->expects($this->any())
			->method('getRequest')
			->willReturn(new Request());

		$this->renderingContext->expects($this->any())
			->method('getTemplateVariableContainer')
			->willReturn($this->templateVariableContainer);
		$this->renderingContext->expects($this->any())
			->method('getViewHelperVariableContainer')
			->willReturn($this->viewHelperVariableContainer);
		$this->renderingContext->expects($this->any())
			->method('getControllerContext')
			->willReturn($this->controllerContext);
	}

	/**
	 * @test
	 */
	public function testGetExtensionNameReturnsExtensionNameArgumentIfSet() {
		$instance = $this->buildViewHelperInstance(array_merge($this->defaultArguments, array('extensionName' => 'foobar-ext')));
		$result = $this->callInaccessibleMethod($instance, 'getExtensionName');
		$this->assertEquals('foobar-ext', $result);
	}

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
		$instance->setRenderingcontext($this->renderingContext);
		$this->viewHelperVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(TRUE));
		$this->templateVariableContainer->expects($this->any())->method('get')->will($this->returnValue($form));
		$output = $this->callInaccessibleMethod($instance, 'getForm');
		$this->assertSame($form, $output);
	}

	/**
	 * @test
	 */
	public function canGetContainerInstanceFromTemplateVariables() {
		$sheet = Form\Container\Sheet::create();
		$instance = $this->createMockedInstanceForVariableContainerTests();
		$this->viewHelperVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(FALSE));
		$this->templateVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(TRUE));
		$this->templateVariableContainer->expects($this->any())->method('get')->will($this->returnValue($sheet));
		$output = $this->callInaccessibleMethod($instance, 'getContainer');
		$this->assertSame($sheet, $output);
	}

	/**
	 * @test
	 */
	public function canGetGridWhenItDoesNotExistButStorageDoes() {
		$form = Form::create();
		$instance = $this->createMockedInstanceForVariableContainerTests();
		$this->templateVariableContainer->expects($this->any())->method('exists')->willReturn(FALSE);
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')
			->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_FORM)
			->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')
			->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_FORM)
			->will($this->returnValue($form));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('exists')
			->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS)
			->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(3))->method('get')
			->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS)
			->will($this->returnValue(array()));
		$output = $this->callInaccessibleMethod($instance, 'getGrid', 'test');
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
		$this->viewHelperVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->any())->method('get')->will($this->returnValue($grids));
		$output = $this->callInaccessibleMethod($instance, 'getGrid', 'test');
		$this->assertSame($grid, $output);
	}

	/**
	 * @test
	 */
	public function canGetGridWhenItDoesNotExistAndStorageDoesNotExist() {
		$form = Form::create();
		$instance = $this->createMockedInstanceForVariableContainerTests();
		$this->viewHelperVariableContainer->expects($this->at(0))->method('exists')
			->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_FORM)
			->will($this->returnValue(TRUE));
		$this->viewHelperVariableContainer->expects($this->at(1))->method('get')
			->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_FORM)
			->will($this->returnValue($form));
		$this->viewHelperVariableContainer->expects($this->at(2))->method('exists')
			->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS)
			->will($this->returnValue(FALSE));
		$output = $this->callInaccessibleMethod($instance, 'getGrid', 'test');
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $output);
	}

	/**
	 * @param array $methods
	 * @return object
	 */
	protected function createMockedInstanceForVariableContainerTests($methods = array()) {
		if (TRUE === empty($methods)) {
			$methods[] = 'dummy';
		}
		$instance = $this->getMock($this->getViewHelperClassName(), $methods);
		$instance->setRenderingContext($this->renderingContext);
		return $instance;
	}

}
