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

require_once t3lib_extMgm::extPath('flux', 'Tests/Fixtures/Class/BasicFluxController.php');

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Controller_FluxControllerTest extends Tx_Flux_Tests_AbstractFunctionalTest {

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
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		Tx_Extbase_Reflection_ObjectAccess::getProperty($instance, 'configurationManager', TRUE)->setContentObject($frontend->cObj);
		return $instance;
	}

	/**
	 * @param string $controllerName
	 * @return array
	 */
	protected function createDummyRequestAndResponseForFluxController($controllerName = 'Content') {
		/** @var Tx_Extbase_MVC_Web_Request $request */
		$request = $this->objectManager->get('Tx_Extbase_MVC_Web_Request');
		$request->setControllerExtensionName('Flux');
		$request->setControllerActionName('render');
		$request->setControllerName($controllerName);
		$request->setControllerObjectName(Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', $controllerName));
		$request->setFormat('html');
		/** @var Tx_Extbase_MVC_Web_Response $response */
		$response = $this->objectManager->get('Tx_Extbase_MVC_Web_Response');
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
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView('Flux', 'Content');
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		$this->setExpectedException('Tx_Fluid_View_Exception_InvalidTemplateResourceException', NULL, 1257246929);
		$this->callInaccessibleMethod($instance, 'performSubRendering', 'Flux', 'Content', 'render', 'tx_flux_content');
	}

	/**
	 * @disabledtest
	 */
	public function canPerformSubRenderingWithForeignExtensionNameWhichContainsAlternativeController() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		class_alias('Tx_Flux_Controller_ContentController', 'Tx_Other_Controller_ContentController');
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView('Other', 'Content');
		list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'request', $request, TRUE);
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
		$previousSettings = Tx_Extbase_Reflection_ObjectAccess::getProperty($instance, 'settings', TRUE);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'settings', $settings, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
		$this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
		$overriddenSettings = Tx_Extbase_Reflection_ObjectAccess::getProperty($instance, 'settings', TRUE);
		$this->assertNotSame($previousSettings, $overriddenSettings);
	}

	/**
	 * @disabledtest
	 */
	public function canCallSubControllerErrorAction() {
		list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		$class = get_class($instance);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$this->callInaccessibleMethod($instance, 'callSubControllerAction', $class, 'error', 'tx_flux_api');
	}

	/**
	 * @test
	 */
	public function throwsRuntimeExceptionWhenInitializingProviderAndNoneIsDetected() {
		$instance = $this->canCreateInstanceOfCustomRegisteredControllerForContent();
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'fluxTableName', 'void', TRUE);
		$this->setExpectedException('RuntimeException', NULL, 1377458581);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
	}

	/**
	 * @disabledtest
	 */
	public function canExecuteBasicRequestUsingCustomController() {
		list ($request, $response) = $this->createDummyRequestAndResponseForFluxController('Content');
		/** @var Tx_Extbase_MVC_Dispatcher $dispatcher */
		$dispatcher = $this->objectManager->get('Tx_Extbase_MVC_Dispatcher');
		$this->setExpectedException('Tx_Fluid_View_Exception_InvalidTemplateResourceException', NULL, 1257246929);
		$dispatcher->dispatch($request, $response);
	}

}
