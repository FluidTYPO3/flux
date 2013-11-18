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
class Tx_Flux_CoreTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function returnsEmptyArrayForUnknownExtensionKeysAndControllerObjects() {
		$fakeControllerName = 'Flux';
		$registered = Tx_Flux_Core::getRegisteredProviderExtensionKeys($fakeControllerName);
		$this->assertEmpty($registered);
	}

	/**
	 * @test
	 */
	public function canRegisterFormInstanceForModelClassName() {
		$class = 'Tx_Flux_Domain_Model_Fake';
		$form = Tx_Flux_Form::create();
		Tx_Flux_Core::registerFormForModelObjectClassName($class, $form);
		$registered = Tx_Flux_Core::getRegisteredFormForModelObjectClass($class);
		$this->assertEquals($form, $registered);
	}

	/**
	 * @test
	 */
	public function canRegisterAutoFormInstanceForModelClassName() {
		$class = 'Tx_Flux_Domain_Model_Fake';
		Tx_Flux_Core::registerAutoFormForModelObjectClassName($class);
		$registered = Tx_Flux_Core::getRegisteredFormForModelObjectClass($class);
		$this->assertEquals(NULL, $registered);
	}

	/**
	 * @test
	 */
	public function canRegisterFormInstanceForTable() {
		$table = 'this_table_does_not_exist';
		$form = Tx_Flux_Form::create();
		Tx_Flux_Core::registerFormForTable($table, $form);
		$forms = Tx_Flux_Core::getRegisteredFormsForTables();
		$this->assertArrayHasKey($table, $forms);
		$returnedForm = Tx_Flux_Core::getRegisteredFormForTable($table);
		$incorrectReturnedForm = Tx_Flux_Core::getRegisteredFormForTable($table . 'badname');
		$this->assertSame($form, $returnedForm);
		$this->assertNull($incorrectReturnedForm);
	}

	/**
	 * @test
	 */
	public function canRegisterProviderExtensionKey() {
		$fakeExtensionKey = 'flux';
		$fakeControllerName = 'Flux';
		Tx_Flux_Core::registerProviderExtensionKey($fakeExtensionKey, $fakeControllerName);
		$registered = Tx_Flux_Core::getRegisteredProviderExtensionKeys($fakeControllerName);
		$this->assertContains($fakeExtensionKey, $registered);
	}

	/**
	 * @test
	 */
	public function canRegisterProviderInstance() {
		/** @var Tx_Flux_Provider_ProviderInterface $provider */
		$provider = $this->objectManager->get('Tx_Flux_Provider_Provider');
		Tx_Flux_Core::registerConfigurationProvider($provider);
		$registered = Tx_Flux_Core::getRegisteredFlexFormProviders();
		$this->assertContains($provider, $registered);
	}

	/**
	 * @test
	 */
	public function canRegisterAndUnregisterProviderClassName() {
		$providerClassName = 'Tx_Flux_Provider_Provider';
		Tx_Flux_Core::registerConfigurationProvider($providerClassName);
		$registered = Tx_Flux_Core::getRegisteredFlexFormProviders();
		$this->assertContains($providerClassName, $registered);
		Tx_Flux_Core::unregisterConfigurationProvider($providerClassName);
		$registered = Tx_Flux_Core::getRegisteredFlexFormProviders();
		$this->assertNotContains($providerClassName, $registered);
	}

	/**
	 * @test
	 */
	public function throwsExceptionOnInvalidClassName() {
		$providerClassName = 'Tx_Flux_Provider_DoesNotExistProvider';
		try {
			Tx_Flux_Core::registerConfigurationProvider($providerClassName);
		} catch (Exception $error) {
			$this->assertSame(1327173514, $error->getCode());
		}
	}

	/**
	 * @test
	 */
	public function throwsExceptionOnInvalidImplementation() {
		$providerClassName = 'Tx_Flux_Tests_Fixtures_Class_InvalidConfigurationProvider';
		try {
			Tx_Flux_Core::registerConfigurationProvider($providerClassName);
		} catch (Exception $error) {
			$this->assertSame(1327173536, $error->getCode());
		}
	}

	/**
	 * @test
	 */
	public function canRegisterStandaloneTemplateForContentObject() {
		$service = $this->createFluxServiceInstance();
		$variables = array('test' => 'test');
		$paths = array('templateRootPath' => 'EXT:flux/Resources/Private/Templates');
		$extensionKey = 'fake';
		$contentObjectType = 'void';
		$providerClassName = 'Tx_Flux_Provider_ProviderInterface';
		$relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['CType'] = $contentObjectType;
		$absoluteTemplatePathAndFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
		$configurationSectionName = 'Configuration';
		Tx_Flux_Core::registerFluidFlexFormContentObject($extensionKey, $contentObjectType, $relativeTemplatePathAndFilename,
			$variables, $configurationSectionName, $paths);
		$detectedProvider = $service->resolvePrimaryConfigurationProvider('tt_content', NULL, $record, $extensionKey);
		$this->assertInstanceOf($providerClassName, $detectedProvider);
		$this->assertSame($extensionKey, $detectedProvider->getExtensionKey($record));
		$this->assertSame($absoluteTemplatePathAndFilename, $detectedProvider->getTemplatePathAndFilename($record));
		$this->assertSame(Tx_Flux_Utility_Path::translatePath($paths), $detectedProvider->getTemplatePaths($record));
	}

	/**
	 * @test
	 */
	public function canRegisterStandaloneTemplateForPlugin() {
		$service = $this->createFluxServiceInstance();
		$variables = array('test' => 'test');
		$paths = array('templateRootPath' => 'EXT:flux/Resources/Private/Templates');
		$extensionKey = 'more_fake';
		$pluginType = 'void';
		$fieldName = NULL;
		$providerClassName = 'Tx_Flux_Provider_ProviderInterface';
		$relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['list_type'] = $pluginType;
		$absoluteTemplatePathAndFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
		$configurationSectionName = 'Configuration';
		Tx_Flux_Core::registerFluidFlexFormPlugin($extensionKey, $pluginType, $relativeTemplatePathAndFilename,
			$variables, $configurationSectionName, $paths);
		$detectedProvider = $service->resolvePrimaryConfigurationProvider('tt_content', $fieldName, $record, $extensionKey);
		$this->assertInstanceOf($providerClassName, $detectedProvider);
		$this->assertSame($extensionKey, $detectedProvider->getExtensionKey($record));
		$this->assertSame($absoluteTemplatePathAndFilename, $detectedProvider->getTemplatePathAndFilename($record));
		$this->assertSame(Tx_Flux_Utility_Path::translatePath($paths), $detectedProvider->getTemplatePaths($record));
	}

	/**
	 * @test
	 */
	public function canRegisterStandaloneTemplateForTable() {
		$service = $this->createFluxServiceInstance();
		$variables = array('test' => 'test');
		$paths = array('templateRootPath' => 'EXT:flux/Resources/Private/Templates');
		$table = 'fake';
		$fieldName = NULL;
		$providerClassName = 'Tx_Flux_Provider_ProviderInterface';
		$relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$absoluteTemplatePathAndFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
		$configurationSectionName = 'Configuration';
		Tx_Flux_Core::registerFluidFlexFormTable($table, $fieldName, $relativeTemplatePathAndFilename,
			$variables, $configurationSectionName, $paths);
		$detectedProvider = $service->resolvePrimaryConfigurationProvider($table, $fieldName, $record);
		$this->assertInstanceOf($providerClassName, $detectedProvider);
		$this->assertSame($absoluteTemplatePathAndFilename, $detectedProvider->getTemplatePathAndFilename($record));
		$this->assertSame(Tx_Flux_Utility_Path::translatePath($paths), $detectedProvider->getTemplatePaths($record));
	}

	/**
	 * @test
	 */
	public function canAddAndRetrieveGlobalTypoScript() {
		Tx_Flux_Core::addGlobalTypoScript(self::FIXTURE_TYPOSCRIPT_DIR);
		$registered = Tx_Flux_Core::getStaticTypoScriptLocations();
		$this->assertContains(self::FIXTURE_TYPOSCRIPT_DIR, $registered);
	}

	/**
	 * @test
	 */
	public function canAddAndRetrieveGlobalTypoScriptCollections() {
		Tx_Flux_Core::addGlobalTypoScript(array(self::FIXTURE_TYPOSCRIPT_DIR));
		$registered = Tx_Flux_Core::getStaticTypoScriptLocations();
		$this->assertContains(self::FIXTURE_TYPOSCRIPT_DIR, $registered);
	}

}
