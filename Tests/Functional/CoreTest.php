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
class Tx_Flux_Tests_Functional_CoreTest extends Tx_Flux_Tests_AbstractFunctionalTest {

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
		/** @var Tx_Flux_Provider_Configuration_Fallback_ConfigurationProvider $provider */
		$provider = $this->objectManager->get('Tx_Flux_Provider_Configuration_Fallback_ConfigurationProvider');
		Tx_Flux_Core::registerConfigurationProvider($provider);
		$registered = Tx_Flux_Core::getRegisteredFlexFormProviders();
		$this->assertContains($provider, $registered);
	}

	/**
	 * @test
	 */
	public function canRegisterAndUnregisterProviderClassName() {
		$providerClassName = 'Tx_Flux_Provider_Configuration_Fallback_ConfigurationProvider';
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
		$providerClassName = 'Tx_Flux_Provider_Configuration_DoesNotExistConfigurationProvider';
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
		$providerClassName = 'Tx_Flux_Provider_Configuration_Fallback_ContentObjectConfigurationProvider';
		$relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['CType'] = $contentObjectType;
		$absoluteTemplatePathAndFilename = t3lib_div::getFileAbsFileName($relativeTemplatePathAndFilename);
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
		$providerClassName = 'Tx_Flux_Provider_Configuration_Fallback_PluginConfigurationProvider';
		$relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['list_type'] = $pluginType;
		$absoluteTemplatePathAndFilename = t3lib_div::getFileAbsFileName($relativeTemplatePathAndFilename);
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
		$providerClassName = 'Tx_Flux_Provider_Configuration_Fallback_ConfigurationProvider';
		$relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$absoluteTemplatePathAndFilename = t3lib_div::getFileAbsFileName($relativeTemplatePathAndFilename);
		$configurationSectionName = 'Configuration';
		Tx_Flux_Core::registerFluidFlexFormTable($table, $fieldName, $relativeTemplatePathAndFilename,
			$variables, $configurationSectionName, $paths);
		$detectedProvider = $service->resolvePrimaryConfigurationProvider($table, $fieldName, $record);
		$this->assertInstanceOf($providerClassName, $detectedProvider);
		$this->assertSame($absoluteTemplatePathAndFilename, $detectedProvider->getTemplatePathAndFilename($record));
		$this->assertSame(Tx_Flux_Utility_Path::translatePath($paths), $detectedProvider->getTemplatePaths($record));
	}

}
