<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Class/BasicFluxController.php');

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Controller_BasicControllerTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @return void
	 */
	protected function performDummyRegistration() {
		Tx_Flux_Core::registerProviderExtensionKey('flux', 'Content');
		$this->assertContains('flux', Tx_Flux_Core::getRegisteredProviderExtensionKeys('Content'));
	}

	/**
	 * @return Tx_Flux_Controller_AbstractFluxController
	 */
	protected function createAndTestDummyControllerInstance() {
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Tx_Flux_Tests_Fixtures_Data_Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		$record['tx_fed_fcefile'] = 'Flux:Default.html';
		$frontend = new tslib_fe($GLOBALS['TYPO3_CONF_VARS'], 1, 0);
		$frontend->cObj = new tslib_cObj();
		$frontend->cObj->start($record);
		$this->performDummyRegistration();
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		/** @var Tx_Flux_Controller_AbstractFluxController $instance */
		$instance = $this->objectManager->get($controllerClassName);
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($instance, 'configurationManager', TRUE)->setContentObject($frontend->cObj);
		return $instance;
	}

	/**
	 * @param string $controllerName
	 * @return array
	 */
	protected function createDummyRequestAndResponseForFluxController($controllerName = 'Content') {
		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Request $request */
		$request = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Request');
		$request->setControllerExtensionName('Flux');
		$request->setControllerActionName('render');
		$request->setControllerName($controllerName);
		$request->setControllerObjectName(Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', $controllerName));
		$request->setFormat('html');
		/** @var Tx_Extbase_MVC_Web_Response $response */
		$response = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response');
		return array($request, $response);
	}

	/**
	 * @test
	 */
	public function canRegisterCustomControllerForContent() {
		$this->performDummyRegistration();
	}

	/**
	 * @test
	 */
	public function canDetectPresenceOfRegisteredCustomControllerForContent() {
		$this->performDummyRegistration();
		$hasController = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$this->assertTrue(class_exists($hasController));
	}

	/**
	 * @test
	 * @return Tx_Flux_Controller_AbstractFluxController
	 */
	public function canCreateInstanceOfCustomRegisteredControllerForContent() {
		$instance = $this->createAndTestDummyControllerInstance();
		$this->assertInstanceOf('Tx_Flux_Controller_AbstractFluxController', $instance);
		return $instance;
	}

	/**
	 * @test
	 */
	public function canGetData() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$data = $this->callInaccessibleMethod($instance, 'getData');
		$this->assertIsArray($data);
	}

	/**
	 * @test
	 */
	public function canGetSetup() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$setup = $this->callInaccessibleMethod($instance, 'getSetup');
		$this->assertIsArray($setup);
	}

	/**
	 * @test
	 */
	public function canGetRecord() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$record = $this->callInaccessibleMethod($instance, 'getRecord');
		$this->assertIsArray($record);
	}

	/**
	 * @test
	 */
	public function canGetFluxRecordField() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$field = $this->callInaccessibleMethod($instance, 'getFluxRecordField');
		$this->assertSame('pi_flexform', $field);
	}

	/**
	 * @test
	 */
	public function canGetFluxTableName() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$table = $this->callInaccessibleMethod($instance, 'getFluxTableName');
		$this->assertSame('tt_content', $table);
	}

	/**
	 * @disabledtest
	 */
	public function canPerformSubRenderingWithMatchingExtensionName() {
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$instance = $this->getMock($controllerClassName, array('hasSubControllerActionOnForeignController'));
		$instance->expects($this->once())->method('hasSubControllerActionOnForeignController')->will($this->returnValue(FALSE));
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView('Flux', 'Content');
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		$this->setExpectedException('Tx_Fluid_View_Exception_InvalidTemplateResourceException', NULL, 1257246929);
		$this->callInaccessibleMethod($instance, 'performSubRendering', 'Flux', 'Content', 'render', 'tx_flux_content');
	}

	/**
	 * @test
	 */
	public function canPerformSubRenderingWithNotMatchingExtensionName() {
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$instance = $this->getMock($controllerClassName, array('hasSubControllerActionOnForeignController', 'callSubControllerAction'));
		$instance->expects($this->once())->method('hasSubControllerActionOnForeignController')->will($this->returnValue(TRUE));
		$instance->expects($this->once())->method('callSubControllerAction');
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView('Flux', 'Content');
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		$this->callInaccessibleMethod($instance, 'performSubRendering', 'Flux', 'Content', 'render', 'tx_flux_content');
	}

	/**
	 * @test
	 */
	public function canInitializeView() {
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$view = $this->getMock('Tx_Flux_View_ExposedTemplateView', array(), array(), '', FALSE);
		$this->inject($view, 'objectManager', $this->objectManager);
		$instance = $this->getMock($controllerClassName, array('initializeProvider', 'initializeSettings', 'initializeOverriddenSettings', 'initializeViewObject', 'initializeViewVariables'));
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
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$instance = $this->getMock($controllerClassName, array('getRecord'));
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$provider = $this->getMock('Tx_Flux_Provider_Provider', array('getExtensionKey', 'getFlexFormValues', 'getTemplatePaths'));
		$provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue('flux'));
		$provider->expects($this->once())->method('getFlexFormValues')->with($row)->will($this->returnValue(array()));
		$provider->expects($this->once())->method('getTemplatePaths')->with($row)->will($this->returnValue(array()));
		$configurationManager = $this->getMock('Tx_Extbase_Configuration_ConfigurationManager', array('getConfiguration'));
		$configurationManager->expects($this->once())->method('getConfiguration')->with(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'Flux', 'void')->will($this->returnValue(array()));
		$request = $this->getMock('Tx_Extbase_MVC_Web_Request', array('getPluginName'));
		$request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationManager', $configurationManager, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeSettings');
	}

	/**
	 * @test
	 */
	public function canInitializeViewObject() {
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$instance = $this->getMock($controllerClassName, array('getRecord'));
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$provider = $this->getMock('Tx_Flux_Provider_Provider', array('getExtensionKey', 'getTemplatePathAndFilename'));
		$provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue('flux'));
		$provider->expects($this->once())->method('getTemplatePathAndFilename')->with($row)->will($this->returnValue('/dev/null'));
		$view = $this->getMock('Tx_Flux_View_ExposedTemplateView', array('setTemplatePathAndFilename'));
		$view->expects($this->once())->method('setTemplatePathAndFilename')->with('/dev/null');
		$configurationService = $this->getMock('Tx_Flux_Service_FluxService', array('getPreparedExposedTemplateView'));
		$configurationService->expects($this->once())->method('getPreparedExposedTemplateView')->will($this->returnValue($view));
		$request = $this->getMock('Tx_Extbase_MVC_Web_Request', array('getControllerName'));
		$request->expects($this->once())->method('getControllerName')->will($this->returnValue('Test'));
		$controllerContext = $this->getMock('Tx_Extbase_Mvc_Controller_ControllerContext');
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'controllerContext', $controllerContext, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeViewObject');
	}

	/**
	 * @test
	 */
	public function callingRenderActionExecutesExpectedMethodsOnNestedObjects() {
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$instance = $this->getMock($controllerClassName, array('getRecord', 'performSubRendering'));
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$instance->expects($this->once())->method('performSubRendering')->with('Flux', 'Void', NULL, 'tx_flux_void')->will($this->returnValue('test'));
		$provider = $this->getMock('Tx_Flux_Provider_Provider', array('getExtensionKey', 'getControllerExtensionKeyFromRecord'));
		$provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue('flux'));
		$provider->expects($this->once())->method('getControllerExtensionKeyFromRecord')->with($row)->will($this->returnValue('flux'));
		$request = $this->getMock('Tx_Extbase_MVC_Web_Request', array('getPluginName', 'getControllerName'));
		$request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
		$request->expects($this->once())->method('getControllerName')->will($this->returnValue('Void'));
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		$result = $instance->renderAction();
		$this->assertEquals($result, 'test');
	}

	/**
	 * @test
	 */
	public function performSubRenderingCallsViewRenderOnNativeTarget() {
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$instance = $this->getMock($controllerClassName, array('callSubControllerAction'));
		$instance->expects($this->never())->method('callSubControllerAction');
		$view = $this->getMock('Tx_Flux_View_ExposedTemplateView', array('render'));
		$view->expects($this->once())->method('render')->will($this->returnValue('test'));
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		$result = $this->callInaccessibleMethod($instance, 'performSubRendering', 'Flux', 'Content', 'render', 'tx_flux_content');
		$this->assertEquals('test', $result);
	}

	/**
	 * @test
	 */
	public function callingSubControllerActionExecutesExpectedMethodsOnNestedObjects() {
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$instance = $this->getMock($controllerClassName, array('processRequest'));
		$objectManager = $this->getMock(get_class($this->objectManager), array('get'));
		$responseClassName = 'TYPO3\CMS\Extbase\Mvc\Web\Response';
		$response = $this->getMock($responseClassName, array('getContent'));
		$response->expects($this->once())->method('getContent')->will($this->returnValue('test'));
		$objectManager->expects($this->at(0))->method('get')->with($responseClassName)->will($this->returnValue($response));
		$objectManager->expects($this->at(1))->method('get')->with($controllerClassName)->will($this->returnValue($instance));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', array('setControllerActionName', 'setArguments'));
		$request->expects($this->once())->method('setControllerActionName')->with('render');
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'objectManager', $objectManager, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$instance->expects($this->once())->method('processRequest')->with($request, $response);
		$result = $this->callInaccessibleMethod($instance, 'callSubControllerAction', 'Flux', $controllerClassName, 'render', 'tx_flux_content');
		$this->assertEquals('test', $result);
	}

	/**
	 * @test
	 */
	public function canInitializeViewVariables() {
		$controllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$data = array('test' => 'test');
		$variables = array('foo' => 'bar');
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$instance = $this->getMock($controllerClassName, array('getRecord'));
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$view = $this->getMock('Tx_Flux_View_ExposedTemplateView', array('assign', 'assignMultiple'));
		$provider = $this->getMock('Tx_Flux_Provider_Provider', array('getTemplatePaths', 'getTemplateVariables'));
		$provider->expects($this->once())->method('getTemplateVariables')->with($row)->will($this->returnValue($variables));
		$view->expects($this->atLeastOnce())->method('assignMultiple');
		$view->expects($this->atLeastOnce())->method('assign');
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'data', $data, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeViewVariables');
	}

	/**
	 * @disabledtest
	 */
	public function canPerformSubRenderingWithForeignExtensionNameWhichContainsAlternativeController() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		class_alias('Tx_Flux_Controller_ContentController', 'Tx_Other_Controller_ContentController');
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView('Other', 'Content');
		list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$this->setExpectedException('Tx_Fluid_View_Exception_InvalidTemplateResourceException', NULL, 1257246929);
		$this->callInaccessibleMethod($instance, 'performSubRendering', 'Other', 'Content', 'render', 'tx_flux_content');
	}

	/**
	 * @test
	 */
	public function canUseTypoScriptSettingsInsteadOfFlexFormDataWhenRequested() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$settings = array(
			'useTypoScript' => TRUE
		);
		$previousSettings = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($instance, 'settings', TRUE);
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($instance, 'settings', $settings, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
		$this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
		$overriddenSettings = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($instance, 'settings', TRUE);
		$this->assertNotSame($previousSettings, $overriddenSettings);
	}

	/**
	 * @test
	 */
	public function canUseFlexFormDataWhenPresent() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$settings = array(
			'settings' => array(
				'test' => 'test'
			)
		);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'data', $settings, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
		$this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
		$overriddenSettings = Tx_Extbase_Reflection_ObjectAccess::getProperty($instance, 'settings', TRUE);
		$this->assertEquals($settings['settings']['test'], $overriddenSettings['test']);
	}

	/**
	 * @disabledtest
	 */
	public function canCallSubControllerErrorAction() {
		list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$class = get_class($instance);
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$this->callInaccessibleMethod($instance, 'callSubControllerAction', $class, 'error', 'tx_flux_api');
	}

	/**
	 * @test
	 */
	public function throwsRuntimeExceptionWhenInitializingProviderAndNoneIsDetected() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($instance, 'fluxTableName', 'void', TRUE);
		$this->setExpectedException('RuntimeException', NULL, 1377458581);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
	}

	/**
	 * @disabledtest
	 */
	public function canExecuteBasicRequestUsingCustomController() {
		list ($request, $response) = $this->createDummyRequestAndResponseForFluxController('Content');
		/** @var \TYPO3\CMS\Extbase\Mvc\Dispatcher $dispatcher */
		$dispatcher = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Dispatcher');
		$this->setExpectedException('Tx_Fluid_View_Exception_InvalidTemplateResourceException', NULL, 1257246929);
		$dispatcher->dispatch($request, $response);
	}

}
