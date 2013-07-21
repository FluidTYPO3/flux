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
class Tx_Flux_Tests_Functional_Controller_BasicControllerTest extends Tx_Flux_Tests_AbstractFunctionalTest {

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
		$service = $this->createFluxServiceInstance();
		$controllerClassName = $service->buildControllerClassNameFromExtensionKeyAndControllerType('flux', 'Content');
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
		$service = $this->createFluxServiceInstance();
		$hasController = $service->detectControllerClassPresenceFromExtensionKeyAndControllerType('flux', 'Content');
		$this->assertTrue($hasController);
	}

	/**
	 * @test
	 */
	public function canCreateInstanceOfCustomRegisteredControllerForContent() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$instance = $this->createAndTestDummyControllerInstance($templatePathAndFilename);
		$this->assertInstanceOf('Tx_Flux_Controller_AbstractFluxController', $instance);
	}

	/**
	 * @test
	 */
	public function canExecuteBasicRequestUsingCustomController() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$instance = $this->createAndTestDummyControllerInstance($templatePathAndFilename);
		$this->assertInstanceOf('Tx_Flux_Controller_AbstractFluxController', $instance);
		/** @var Tx_Flux_MVC_View_ExposedTemplateView $view */
		$view = $this->objectManager->get(Tx_Extbase_Reflection_ObjectAccess::getProperty($instance, 'defaultViewObjectName', TRUE));
		list ($request, $response) = $this->createDummyRequestAndResponseForFluxController('Content');
	}

}
