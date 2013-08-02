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
	 * @param string templatePathAndFilename
	 * @return Tx_Flux_Controller_AbstractFluxController
	 */
	protected function createAndTestDummyControllerInstance($templatePathAndFilename) {
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
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$instance = $this->createAndTestDummyControllerInstance($templatePathAndFilename);
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
	public function canExecuteBasicRequestUsingCustomController() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors'] = 0;
		list ($request, $response) = $this->createDummyRequestAndResponseForFluxController('Content');
		/** @var Tx_Extbase_MVC_Dispatcher $dispatcher */
		$dispatcher = $this->objectManager->get('Tx_Extbase_MVC_Dispatcher');
		$this->setExpectedException('RuntimeException', NULL, 1364741158);
		$dispatcher->dispatch($request, $response);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors'] = $backup;
	}

	/**
	 * @disabledtest
	 */
	public function canExecuteBasicRequestUsingCustomControllerToRenderErrorAction() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors'] = 0;
		list ($request, $response) = $this->createDummyRequestAndResponseForFluxController('Content');
		$request->setControllerActionName('error');
		/** @var Tx_Extbase_MVC_Dispatcher $dispatcher */
		$dispatcher = $this->objectManager->get('Tx_Extbase_MVC_Dispatcher');
		$this->setExpectedException('RuntimeException', NULL, 1364741158);
		$dispatcher->dispatch($request, $response);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors'] = $backup;
	}

	/**
	 * @disabledtest
	 */
	public function canExecuteBasicRequestUsingCustomControllerAndHandleError() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors'] = 1;
		list ($request, $response) = $this->createDummyRequestAndResponseForFluxController('Content');
		/** @var Tx_Extbase_MVC_Dispatcher $dispatcher */
		$dispatcher = $this->objectManager->get('Tx_Extbase_MVC_Dispatcher');
		$this->setExpectedException('Tx_Fluid_View_Exception_InvalidTemplateResourceException', '"" is not a valid template resource URI');
		$dispatcher->dispatch($request, $response);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors'] = $backup;
	}

}
