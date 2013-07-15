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

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Tests_Functional_Service_FluxServiceTest extends Tx_Flux_Tests_AbstractFunctionalTest {

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
	public function canCreateExposedViewWithoutExtensionNameAndControllerName() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView();
		$this->assertInstanceOf('Tx_Flux_MVC_View_ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithExtensionNameWithoutControllerName() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux');
		$this->assertInstanceOf('Tx_Flux_MVC_View_ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithExtensionNameAndControllerName() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux', 'API');
		$this->assertInstanceOf('Tx_Flux_MVC_View_ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithoutExtensionNameWithControllerName() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView(NULL, 'API');
		$this->assertInstanceOf('Tx_Flux_MVC_View_ExposedTemplateView', $view);
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
	public function canGetFormWithPaths() {
		$templatePathAndFilename = t3lib_div::getFileAbsFileName(self::FIXTURE_TEMPLATE_BASICGRID);
		$service = $this->createFluxServiceInstance();
		$paths = array(
			'templateRootPath' => 'EXT:flux/Resources/Private/Templates',
			'partialRootPath' => 'EXT:flux/Resources/Private/Partials',
			'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts'
		);
		$form = $service->getFormFromTemplateFile($templatePathAndFilename, 'Configuration', 'form', $paths, 'flux');
		$this->assertInstanceOf('Tx_Flux_Form', $form);
	}

	/**
	 * @test
	 */
	public function getFormThrowsExceptionOnInvalidFile() {
		$templatePathAndFilename = '/void/nothing';
		$service = $this->createFluxServiceInstance();
		try {
			$service->getFormFromTemplateFile($templatePathAndFilename);
			$this->fail('Did not throw Exception on invalid file');
		} catch (Exception $error) {
			$this->assertSame(1366824347, $error->getCode());
		}
	}

	/**
	 * @test
	 */
	public function getGridFromTemplateFilePassesThroughExceptionIfDebugModeEnabledAtAnyLevel() {
		$currentMode = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = 2;
		$templatePathAndFilename = '/void/nothing';
		$service = $this->createFluxServiceInstance();
		try {
			$service->getGridFromTemplateFile($templatePathAndFilename, 'Configuration', 'grid', array(), 'flux');
			$this->fail('Did not throw Exception on invalid file');
		} catch (Exception $error) {
			$this->assertSame(1366824347, $error->getCode());
		}
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] = $currentMode;
	}

	/**
	 * @test
	 */
	public function canGetFormWithPathsAndTriggerCache() {
		$templatePathAndFilename = t3lib_div::getFileAbsFileName(self::FIXTURE_TEMPLATE_BASICGRID);
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
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = '1';
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_COMPACTED);
		$service = $this->createFluxServiceInstance();
		$form = $service->getFormFromTemplateFile($templatePathAndFilename);
		$this->assertInstanceOf('Tx_Flux_Form', $form);
		$stored = $form->build();
		$this->assertIsArray($stored);
	}

	/**
	 * @test
	 */
	public function canRenderTemplateWithCompactingSwitchedOff() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = '0';
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_SHEETS);
		$this->performBasicTemplateReadTest($templatePathAndFilename);
	}

	/**
	 * @test
	 */
	public function canDetectControllerClassPresenceFromExtensionKeyAndControllerType() {
		$service = $this->createFluxServiceInstance();
		$result = $service->detectControllerClassPresenceFromExtensionKeyAndControllerType('noname', 'Void');
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassExists() {
		$service = $this->createFluxServiceInstance();
		class_alias('Tx_Flux_Controller_AbstractFluxController', 'Void\\NoName\\Controller\\FakeController');
		$result = $service->detectControllerClassPresenceFromExtensionKeyAndControllerType('Void.NoName', 'Fake');
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassDoesNotExist() {
		$service = $this->createFluxServiceInstance();
		$result = $service->detectControllerClassPresenceFromExtensionKeyAndControllerType('Void.NoName', 'Void');
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function canGetBackendViewConfigurationForExtensionName() {
		$service = $this->createFluxServiceInstance();
		$config = $service->getBackendViewConfigurationForExtensionName('noname');
		$this->assertNull($config);
	}

}
