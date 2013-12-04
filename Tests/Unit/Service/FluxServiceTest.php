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

require_once t3lib_extMgm::extPath('flux', 'Tests/Fixtures/Class/DummyModel.php');
require_once t3lib_extMgm::extPath('flux', 'Tests/Fixtures/Class/DummyRepository.php');

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Service_FluxServiceTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canInstantiateFluxService() {
		$service = $this->createFluxServiceInstance();
		$this->assertInstanceOf('Tx_Flux_Service_FluxService', $service);
	}

	/**
	 * @test
	 */
	public function canFlushCache() {
		$service = $this->createFluxServiceInstance();
		$service->flushCache();
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithoutExtensionNameAndControllerName() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView();
		$this->assertInstanceOf('Tx_Flux_View_ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithExtensionNameWithoutControllerName() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux');
		$this->assertInstanceOf('Tx_Flux_View_ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithExtensionNameAndControllerName() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux', 'API');
		$this->assertInstanceOf('Tx_Flux_View_ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithoutExtensionNameWithControllerName() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView(NULL, 'API');
		$this->assertInstanceOf('Tx_Flux_View_ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canResolvePrimaryConfigurationProviderWithEmptyArray() {
		$service = $this->createFluxServiceInstance();
		$result = $service->resolvePrimaryConfigurationProvider('tt_content', NULL);
		$this->assertNotEmpty($result);
	}

	/**
	 * @test
	 */
	public function canResolveConfigurationProvidersWithEmptyArrayAndTriggerCache() {
		$service = $this->createFluxServiceInstance();
		$result = $service->resolvePrimaryConfigurationProvider('tt_content', NULL);
		$this->assertNotEmpty($result);
		$result = $service->resolvePrimaryConfigurationProvider('tt_content', NULL);
		$this->assertNotEmpty($result);
	}

	/**
	 * @test
	 */
	public function canGetTypoScriptSubConfigurationWithNonexistingExtensionNameAndReturnEmptyArray() {
		$service = $this->createFluxServiceInstance();
		$config = $service->getTypoScriptSubConfiguration('doesnotexist', 'view', 'flux');
		$this->assertIsArray($config);
	}

	/**
	 * @test
	 */
	public function canGetFormWithPaths() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$service = $this->createFluxServiceInstance();
		$paths = array(
			'templateRootPath' => 'EXT:flux/Resources/Private/Templates',
			'partialRootPath' => 'EXT:flux/Resources/Private/Partials',
			'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts'
		);
		$form1 = $service->getFormFromTemplateFile($templatePathAndFilename, 'Configuration', 'form', $paths, 'flux');
		$form2 = $service->getFormFromTemplateFile($templatePathAndFilename, 'Configuration', 'form', $paths, 'flux');
		$this->assertInstanceOf('Tx_Flux_Form', $form1);
		$this->assertInstanceOf('Tx_Flux_Form', $form2);
	}

	/**
	 * @test
	 */
	public function getFormReturnsNullOnInvalidFile() {
		$templatePathAndFilename = '/void/nothing';
		$service = $this->createFluxServiceInstance();
		$form = $service->getFormFromTemplateFile($templatePathAndFilename);
		$this->assertNull($form);
	}

	/**
	 * @test
	 */
	public function canGetFormWithPathsAndTriggerCache() {
		$templatePathAndFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(self::FIXTURE_TEMPLATE_BASICGRID);
		$service = $this->createFluxServiceInstance();
		$paths = array(
			'templateRootPath' => 'EXT:flux/Resources/Private/Templates',
			'partialRootPath' => 'EXT:flux/Resources/Private/Partials',
			'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts'
		);
		$form = $service->getFormFromTemplateFile($templatePathAndFilename, 'Configuration', 'form', $paths, 'flux');
		$this->assertInstanceOf('Tx_Flux_Form', $form);
		$readAgain = $service->getFormFromTemplateFile($templatePathAndFilename, 'Configuration', 'form', $paths, 'flux');
		$this->assertInstanceOf('Tx_Flux_Form', $readAgain);
	}

	/**
	 * @test
	 */
	public function canReadGridFromTemplateWithoutConvertingToDataStructure() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$form = $this->performBasicTemplateReadTest($templatePathAndFilename);
		$this->assertInstanceOf('Tx_Flux_Form', $form);
	}

	/**
	 * @test
	 */
	public function canRenderTemplateWithCompactingSwitchedOn() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = '1';
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_COMPACTED);
		$service = $this->createFluxServiceInstance();
		$form = $service->getFormFromTemplateFile($templatePathAndFilename);
		$this->assertInstanceOf('Tx_Flux_Form', $form);
		$stored = $form->build();
		$this->assertIsArray($stored);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = $backup;
	}

	/**
	 * @test
	 */
	public function canRenderTemplateWithCompactingSwitchedOff() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = '0';
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_SHEETS);
		$this->performBasicTemplateReadTest($templatePathAndFilename);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = $backup;
	}

	/**
	 * @test
	 */
	public function canGetBackendViewConfigurationForExtensionName() {
		$service = $this->createFluxServiceInstance();
		$config = $service->getBackendViewConfigurationForExtensionName('noname');
		$this->assertNull($config);
	}

	/**
	 * @test
	 */
	public function canGetViewConfigurationForExtensionNameWhichDoesNotExistAndConstructDefaults() {
		$expected = array(
			'templateRootPath' => 'EXT:void/Resources/Private/Templates',
			'partialRootPath' => 'EXT:void/Resources/Private/Partials',
			'layoutRootPath' => 'EXT:void/Resources/Private/Layouts',
		);
		$service = $this->createFluxServiceInstance();
		$config = $service->getViewConfigurationForExtensionName('void');
		$this->assertSame($expected, $config);
	}

	/**
	 * @test
	 */
	public function canSendDebugMessages() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = 1;
		$service = $this->createFluxServiceInstance();
		$message = uniqid('message_');
		$service->message($message);
		$service->message($message);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = $backup;
	}

	/**
	 * @test
	 */
	public function canSendDebugMessagesInProductionContext() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = 2;
		$service = $this->createFluxServiceInstance();
		$message = uniqid('message_');
		$service->message($message);
		$service->message($message);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = $backup;
	}

	/**
	 * @test
	 */
	public function canDebugProvider() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = 1;
		$service = $this->createFluxServiceInstance();
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$provider = $service->resolvePrimaryConfigurationProvider('tt_content', NULL, $record, 'flux');
		$service->debugProvider($provider);
		$service->debugProvider($provider);
		$service->debug($provider);
		$service->debug($provider);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = $backup;
	}

	/**
	 * @test
	 */
	public function canDebugView() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = 1;
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('flux', 'Content');
		$service->debugView($view);
		$service->debugView($view);
		$service->debug($view);
		$service->debug($view);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = $backup;
	}

	/**
	 * @test
	 */
	public function canDebugRandomObject() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = 1;
		$service = $this->createFluxServiceInstance();
		$object = Tx_Flux_Form::create(array('name' => 'test'));
		$service->debug($object);
		$service->debug($object);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = $backup;
	}

	/**
	 * @test
	 */
	public function canDebugException() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = 1;
		$service = $this->createFluxServiceInstance();
		$exception = new RuntimeException('Hello world', 1);
		$service->debug($exception);
		$service->debug($exception);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = $backup;
	}

	/**
	 * @test
	 */
	public function canDebugRandomString() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = 1;
		$service = $this->createFluxServiceInstance();
		$string = 'Hello world';
		$service->debug($string);
		$service->debug($string);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = $backup;
	}

	/**
	 * @test
	 */
	public function loadTypoScriptProvidersReturnsEmptyArrayEarlyIfSetupNotFound() {
		$instance = $this->createFluxServiceInstance();
		$configurationManager = $this->getMock('Tx_Extbase_Configuration_ConfigurationManager', array('getConfiguration'));
		$configurationManager->expects($this->once())->method('getConfiguration')->will($this->returnValue(array()));
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationManager', $configurationManager, TRUE);
		$instance->flushCache();
		$providers = $this->callInaccessibleMethod($instance, 'loadTypoScriptConfigurationProviderInstances');
		$this->assertIsArray($providers);
		$this->assertEmpty($providers);
	}

	/**
	 * @test
	 */
	public function loadTypoScriptProvidersSupportsCustomClassName() {
		$instance = $this->createFluxServiceInstance();
		$configurationManager = $this->getMock('Tx_Extbase_Configuration_ConfigurationManager', array('getConfiguration'));
		$mockedTypoScript = array(
			'plugin.' => array(
				'tx_flux.' => array(
					'providers.' => array(
						'dummy.' => array(
							'className' => 'Tx_Flux_Tests_Fixtures_Class_DummyConfigurationProvider'
						)
					)
				)
			)
		);
		$configurationManager->expects($this->once())->method('getConfiguration')->will($this->returnValue($mockedTypoScript));
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationManager', $configurationManager, TRUE);
		$instance->flushCache();
		$providers = $this->callInaccessibleMethod($instance, 'loadTypoScriptConfigurationProviderInstances');
		$this->assertIsArray($providers);
		$this->assertNotEmpty($providers);
		$this->assertInstanceOf('Tx_Flux_Tests_Fixtures_Class_DummyConfigurationProvider', reset($providers));
	}

	/**
	 * @test
	 */
	public function templateWithErrorReturnsFormWithErrorReporter() {
		$badSource = '<f:layout invalid="TRUE" />';
		$temp = t3lib_div::tempnam('badtemplate') . '.html';
		t3lib_div::writeFileToTypo3tempDir($temp, $badSource);
		$form = $this->createFluxServiceInstance()->getFormFromTemplateFile($temp);
		$this->assertInstanceOf('Tx_Flux_Form', $form);
		$this->assertInstanceOf('Tx_Flux_Form_Field_UserFunction', reset($form->getFields()));
		$this->assertEquals('Tx_Flux_UserFunction_ErrorReporter->renderField', reset($form->getFields())->getFunction());
	}

	/**
	 * @test
	 */
	public function loadObjectsFromRepositorySupportsFindByIdentifiersMethod() {
		$class = substr(get_class($this), 0, -4);
		$instance = $this->getMock($class);
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'objectManager', $this->objectManager, TRUE);
		$result = $this->callInaccessibleMethod($instance, 'transformValueToType', '1', 'Tx_Extbase_Persistence_ObjectStorage<Tx_Flux_Domain_Model_Dummy>');
		$this->assertEquals($result, array(1));
	}

}
