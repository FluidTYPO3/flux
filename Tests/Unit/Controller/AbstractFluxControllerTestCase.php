<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@wildside.dk>
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

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case for Flux-enabled controllers
 *
 * @package Flux
 */
class AbstractFluxControllerTestCase extends AbstractTestCase {

	/**
	 * @var string
	 */
	protected $extensionName = 'FluidTYPO3.Flux';

	/**
	 * @var string
	 */
	protected $defaultAction = 'render';

	/**
	 * @var string
	 */
	protected $extensionKey = 'flux';

	/**
	 * @var string
	 */
	protected $shortExtensionName = 'Flux';

	/**
	 * Setup test case
	 */
	public function setup() {
		$controllerClassName = substr(get_class($this), 0, -4);
		if (TRUE === empty($controllerClassName) || FALSE === class_exists($controllerClassName)) {
			$this->getMockForAbstractClass('FluidTYPO3\Flux\Controller\AbstractFluxController', array(), $controllerClassName);
		}
	}

	/**
	 * @param string $action
	 * @return void
	 */
	protected function assertSimpleActionCallsRenderOnView($action) {
		$instance = $this->objectManager->get(substr(get_class($this), 0, -4));
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', array('render', 'assign'));
		$response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
		$view->expects($this->once())->method('render')->will($this->returnValue('rendered'));
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'response', $response, TRUE);
		ObjectAccess::setProperty($instance, 'actionMethodName', $action . 'Action', TRUE);
		$this->callInaccessibleMethod($instance, 'callActionMethod');
		$output = $response->getcontent();
		$this->assertEquals('rendered', $output);
	}

	/**
	 * @return string
	 */
	protected function getControllerName() {
		if (TRUE === strpos(get_class($this), '\\')) {
			$parts = explode('\\', get_class($this));
		} else {
			$parts = explode('_', get_class($this));
		}
		$name = substr(array_pop($parts), 0, -9);
		return $name;
	}

	/**
	 * @test
	 * @return AbstractFluxController
	 */
	public function canCreateInstanceOfCustomRegisteredController() {
		$instance = $this->createAndTestDummyControllerInstance();
		$this->assertInstanceOf('FluidTYPO3\Flux\Controller\AbstractFluxController', $instance);
		return $instance;
	}

	/**
	 * @return void
	 */
	protected function performDummyRegistration() {
		Core::registerProviderExtensionKey($this->extensionName, $this->getControllerName());
		$this->assertContains($this->extensionName, Core::getRegisteredProviderExtensionKeys($this->getControllerName()));
	}

	/**
	 * @return AbstractFluxController
	 */
	protected function createAndTestDummyControllerInstance() {
		return $this->objectManager->get(substr(get_class($this), 0, -4));
	}

	/**
	 * @param string $controllerName
	 * @return array
	 */
	protected function createDummyRequestAndResponseForFluxController($controllerName = 'Content') {
		/** @var Request $request */
		$request = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Request');
		$request->setControllerExtensionName('Flux');
		$request->setControllerActionName($this->defaultAction);
		$request->setControllerName($controllerName);
		$request->setControllerObjectName(ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction($this->extensionName, $this->defaultAction, $controllerName));
		$request->setFormat('html');
		/** @var Response $response */
		$response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
		return array($request, $response);
	}

	/**
	 * @test
	 */
	public function canGetData() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$data = $this->callInaccessibleMethod($instance, 'getData');
		$this->assertIsArray($data);
	}

	/**
	 * @test
	 */
	public function canGetSetup() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$setup = $this->callInaccessibleMethod($instance, 'getSetup');
		$this->assertIsArray($setup);
	}

	/**
	 * @test
	 */
	public function canGetRecord() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$record = $this->callInaccessibleMethod($instance, 'getRecord');
		$this->assertIsArray($record);
	}

	/**
	 * @test
	 */
	public function canGetFluxRecordField() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$field = $this->callInaccessibleMethod($instance, 'getFluxRecordField');
		$this->assertSame('pi_flexform', $field);
	}

	/**
	 * @test
	 */
	public function canGetFluxTableName() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$table = $this->callInaccessibleMethod($instance, 'getFluxTableName');
		$this->assertSame('tt_content', $table);
	}

	/**
	 * @disabledtest
	 */
	public function canPerformSubRenderingWithMatchingExtensionName() {
		$controllerName = $this->getControllerName();
		$controllerClassName = substr(get_class($this), 0, -4);
		$instance = $this->getMock($controllerClassName, array('hasSubControllerActionOnForeignController'));
		$instance->expects($this->once())->method('hasSubControllerActionOnForeignController')->will($this->returnValue(FALSE));
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView($this->extensionName, $controllerName);
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		$this->setExpectedException('TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException', NULL, 1257246929);
		$this->callInaccessibleMethod($instance, 'performSubRendering', $this->shortExtensionName, $controllerName, $this->defaultAction, 'tx_flux_content');
	}

	/**
	 * @test
	 */
	public function canPerformSubRenderingWithNotMatchingExtensionName() {
		$controllerName = $this->getControllerName();
		$controllerClassName = substr(get_class($this), 0, -4);
		$instance = $this->getMock($controllerClassName, array('hasSubControllerActionOnForeignController', 'callSubControllerAction'));
		$instance->expects($this->once())->method('hasSubControllerActionOnForeignController')->will($this->returnValue(TRUE));
		$instance->expects($this->once())->method('callSubControllerAction');
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView($this->extensionName, $controllerName);
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'extensionName', $this->extensionName, TRUE);
		$this->callInaccessibleMethod($instance, 'performSubRendering', $this->extensionName, $controllerName, $this->defaultAction, 'tx_flux_content');
	}

	/**
	 * @test
	 */
	public function canInitializeView() {
		$controllerClassName = substr(get_class($this), 0, -4);
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', array(), array(), '', FALSE);
		ObjectAccess::setProperty($view, 'objectManager', $this->objectManager, TRUE);
		$instance = $this->getMock($controllerClassName, array('initializeProvider', 'initializeSettings', 'initializeOverriddenSettings', 'initializeViewObject', 'initializeViewVariables'));
		ObjectAccess::setProperty($instance, 'configurationManager', $this->objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManager'), TRUE);
		$instance->expects($this->at(0))->method('intiializeProvider');
		$instance->expects($this->at(1))->method('initializeSettings');
		$instance->expects($this->at(2))->method('initializeOverriddenSettings');
		$instance->expects($this->at(3))->method('initializeViewObject');
		$instance->expects($this->at(4))->method('initializeViewVariables');
		$instance->initializeView($view);
	}

	/**
	 * @test
	 */
	public function canInitializeSettings() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$controllerClassName = substr(get_class($this), 0, -4);
		$instance = $this->getMock($controllerClassName, array('getRecord'));
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$provider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', array('getExtensionKey', 'getFlexFormValues', 'getTemplatePaths'));
		$provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue($this->extensionKey));
		$provider->expects($this->once())->method('getFlexFormValues')->with($row)->will($this->returnValue(array()));
		$provider->expects($this->once())->method('getTemplatePaths')->with($row)->will($this->returnValue(array()));
		$configurationManager = $this->getMock('Tx_Extbase_Configuration_ConfigurationManager', array('getConfiguration'));
		$configurationManager->expects($this->once())->method('getConfiguration')->with(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $this->shortExtensionName, 'void')->will($this->returnValue(array()));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', array('getPluginName'));
		$request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		ObjectAccess::setProperty($instance, 'configurationManager', $configurationManager, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeSettings');
	}

	/**
	 * @test
	 */
	public function canInitializeViewObject() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$controllerClassName = substr(get_class($this), 0, -4);
		$instance = $this->getMock($controllerClassName, array('getRecord'));
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$provider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', array('getExtensionKey', 'getTemplatePathAndFilename'));
		$provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue($this->extensionKey));
		$provider->expects($this->once())->method('getTemplatePathAndFilename')->with($row)->will($this->returnValue('/dev/null'));
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', array('setTemplatePathAndFilename'));
		$view->expects($this->once())->method('setTemplatePathAndFilename')->with('/dev/null');
		$configurationService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('getPreparedExposedTemplateView'));
		$configurationService->expects($this->once())->method('getPreparedExposedTemplateView')->will($this->returnValue($view));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', array('getControllerName'));
		$request->expects($this->once())->method('getControllerName')->will($this->returnValue('Test'));
		$controllerContext = $this->getMock('Tx_Extbase_Mvc_Controller_ControllerContext');
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		ObjectAccess::setProperty($instance, 'controllerContext', $controllerContext, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeViewObject');
	}

	/**
	 * @test
	 */
	public function callingRenderActionExecutesExpectedMethodsOnNestedObjects() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$controllerClassName = substr(get_class($this), 0, -4);
		$instance = $this->getMock($controllerClassName, array('getRecord', 'performSubRendering'));
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$instance->expects($this->once())->method('performSubRendering')->with($this->extensionKey, 'Void', NULL, 'tx_flux_void')->will($this->returnValue('test'));
		$provider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', array('getExtensionKey', 'getControllerExtensionKeyFromRecord'));
		$provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue('flux'));
		$provider->expects($this->once())->method('getControllerExtensionKeyFromRecord')->with($row)->will($this->returnValue($this->extensionKey));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', array('getPluginName', 'getControllerName'));
		$request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
		$request->expects($this->once())->method('getControllerName')->will($this->returnValue('Void'));
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		$result = $instance->renderAction();
		$this->assertEquals($result, 'test');
	}

	/**
	 * @test
	 */
	public function performSubRenderingCallsViewRenderOnNativeTarget() {
		$controllerName = $this->getControllerName();
		$controllerClassName = substr(get_class($this), 0, -4);
		$instance = $this->getMock($controllerClassName, array('callSubControllerAction'));
		$instance->expects($this->never())->method('callSubControllerAction');
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', array('render'));
		$view->expects($this->once())->method('render')->will($this->returnValue('test'));
		ObjectAccess::setProperty($instance, 'extensionName', $this->shortExtensionName, TRUE);
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		$result = $this->callInaccessibleMethod($instance, 'performSubRendering', $this->shortExtensionName, $controllerName, $this->defaultAction, 'tx_flux_content');
		$this->assertEquals('test', $result);
	}

	/**
	 * @test
	 */
	public function callingSubControllerActionExecutesExpectedMethodsOnNestedObjects() {
		$controllerClassName = substr(get_class($this), 0, -4);
		$instance = $this->getMock($controllerClassName, array('processRequest'));
		$objectManager = $this->getMock(get_class($this->objectManager), array('get'));
		$responseClassName = 'TYPO3\CMS\Extbase\Mvc\Web\Response';
		$response = $this->getMock($responseClassName, array('getContent'));
		$response->expects($this->once())->method('getContent')->will($this->returnValue('test'));
		$objectManager->expects($this->at(0))->method('get')->with($responseClassName)->will($this->returnValue($response));
		$objectManager->expects($this->at(1))->method('get')->with($controllerClassName)->will($this->returnValue($instance));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', array('setControllerActionName', 'setArguments'));
		$request->expects($this->once())->method('setControllerActionName')->with($this->defaultAction);
		ObjectAccess::setProperty($instance, 'objectManager', $objectManager, TRUE);
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$instance->expects($this->once())->method('processRequest')->with($request, $response);
		$result = $this->callInaccessibleMethod($instance, 'callSubControllerAction', $this->shortExtensionName, $controllerClassName, $this->defaultAction, 'tx_flux_content');
		$this->assertEquals('test', $result);
	}

	/**
	 * @test
	 */
	public function canInitializeViewVariables() {
		$controllerClassName = substr(get_class($this), 0, -4);
		$data = array('test' => 'test');
		$variables = array('foo' => 'bar');
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$instance = $this->getMock($controllerClassName, array('getRecord'));
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', array('assign', 'assignMultiple'));
		$provider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', array('getTemplatePaths', 'getTemplateVariables'));
		$provider->expects($this->once())->method('getTemplateVariables')->with($row)->will($this->returnValue($variables));
		$view->expects($this->atLeastOnce())->method('assignMultiple');
		$view->expects($this->atLeastOnce())->method('assign');
		ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'data', $data, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeViewVariables');
	}

	/**
	 * @disabledtest
	 */
	public function canPerformSubRenderingWithForeignExtensionNameWhichContainsAlternativeController() {
		$controllerName = $this->getControllerName();
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		class_alias('FluidTYPO3\Flux\Controller\ContentController', 'FluidTYPO3\Other\Controller\ContentController');
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView('FluidTYPO3.Other', $controllerName);
		list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'extensionName', $this->shortExtensionName, TRUE);
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$this->setExpectedException('TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException', NULL, 1257246929);
		$this->callInaccessibleMethod($instance, 'performSubRendering', 'Other', $controllerName, $this->defaultAction, 'tx_flux_content');
	}

	/**
	 * @test
	 */
	public function canUseTypoScriptSettingsInsteadOfFlexFormDataWhenRequested() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$settings = array(
			'useTypoScript' => TRUE
		);
		$previousSettings = ObjectAccess::getProperty($instance, 'settings', TRUE);
		ObjectAccess::setProperty($instance, 'settings', $settings, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
		$this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
		$overriddenSettings = ObjectAccess::getProperty($instance, 'settings', TRUE);
		$this->assertNotSame($previousSettings, $overriddenSettings);
	}

	/**
	 * @test
	 */
	public function canUseFlexFormDataWhenPresent() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$settings = array(
			'settings' => array(
				'test' => 'test'
			)
		);
		ObjectAccess::setProperty($instance, 'data', $settings, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
		$this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
		$overriddenSettings = ObjectAccess::getProperty($instance, 'settings', TRUE);
		$this->assertEquals($settings['settings']['test'], $overriddenSettings['test']);
	}

	/**
	 * @disabledtest
	 */
	public function canCallSubControllerErrorAction() {
		list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$class = get_class($instance);
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$this->callInaccessibleMethod($instance, 'callSubControllerAction', $class, 'error', 'tx_flux_api');
	}

	/**
	 * @test
	 */
	public function throwsRuntimeExceptionWhenInitializingProviderAndNoneIsDetected() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		ObjectAccess::setProperty($instance, 'fluxTableName', 'void', TRUE);
		$this->setExpectedException('RuntimeException', NULL, 1377458581);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
	}

	/**
	 * @disabledtest
	 */
	public function canExecuteBasicRequestUsingCustomController() {
		$controllerName = $this->getControllerName();
		list ($request, $response) = $this->createDummyRequestAndResponseForFluxController($controllerName);
		/** @var Dispatcher $dispatcher */
		$dispatcher = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Dispatcher');
		$this->setExpectedException('TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException', NULL, 1257246929);
		$dispatcher->dispatch($request, $response);
	}

}
