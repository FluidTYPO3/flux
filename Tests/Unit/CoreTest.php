<?php
namespace FluidTYPO3\Flux;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Flux
 */
class CoreTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function returnsEmptyArrayForUnknownExtensionKeysAndControllerObjects() {
		$fakeControllerName = 'Flux';
		$registered = Core::getRegisteredProviderExtensionKeys($fakeControllerName);
		$this->assertEmpty($registered);
	}

	/**
	 * @test
	 */
	public function canRegisterFormInstanceForModelClassName() {
		$class = 'Tx_Flux_Domain_Model_Fake';
		$form = Form::create();
		Core::registerFormForModelObjectClassName($class, $form);
		$registered = Core::getRegisteredFormForModelObjectClass($class);
		$this->assertEquals($form, $registered);
	}

	/**
	 * @test
	 */
	public function canRegisterAutoFormInstanceForModelClassName() {
		$class = 'Tx_Flux_Domain_Model_Fake';
		Core::registerAutoFormForModelObjectClassName($class);
		$registered = Core::getRegisteredFormForModelObjectClass($class);
		$this->assertEquals(NULL, $registered);
	}

	/**
	 * @test
	 */
	public function canRegisterFormInstanceForTable() {
		$table = 'this_table_does_not_exist';
		$form = Form::create();
		Core::registerFormForTable($table, $form);
		$forms = Core::getRegisteredFormsForTables();
		$this->assertArrayHasKey($table, $forms);
		$returnedForm = Core::getRegisteredFormForTable($table);
		$incorrectReturnedForm = Core::getRegisteredFormForTable($table . 'badname');
		$this->assertSame($form, $returnedForm);
		$this->assertNull($incorrectReturnedForm);
	}

	/**
	 * @test
	 */
	public function canRegisterProviderExtensionKey() {
		$fakeExtensionKey = 'flux';
		$fakeControllerName = 'Flux';
		Core::registerProviderExtensionKey($fakeExtensionKey, $fakeControllerName);
		$registered = Core::getRegisteredProviderExtensionKeys($fakeControllerName);
		$this->assertContains($fakeExtensionKey, $registered);
	}

	/**
	 * @test
	 */
	public function canRegisterProviderInstance() {
		/** @var \FluidTYPO3\Flux\Provider\ProviderInterface $provider */
		$provider = $this->objectManager->get('FluidTYPO3\Flux\Provider\Provider');
		Core::registerConfigurationProvider($provider);
		$registered = Core::getRegisteredFlexFormProviders();
		$this->assertContains($provider, $registered);
	}

	/**
	 * @test
	 */
	public function canRegisterAndUnregisterProviderClassName() {
		$providerClassName = 'FluidTYPO3\Flux\Provider\Provider';
		Core::registerConfigurationProvider($providerClassName);
		$registered = Core::getRegisteredFlexFormProviders();
		$this->assertContains($providerClassName, $registered);
		Core::unregisterConfigurationProvider($providerClassName);
		$registered = Core::getRegisteredFlexFormProviders();
		$this->assertNotContains($providerClassName, $registered);
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
		$providerClassName = 'FluidTYPO3\Flux\Provider\ProviderInterface';
		$relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['CType'] = $contentObjectType;
		$absoluteTemplatePathAndFilename = GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
		$configurationSectionName = 'Configuration';
		Core::registerFluidFlexFormContentObject($extensionKey, $contentObjectType, $relativeTemplatePathAndFilename,
			$variables, $configurationSectionName, $paths);
		$detectedProvider = $service->resolvePrimaryConfigurationProvider('tt_content', NULL, $record, $extensionKey);
		$this->assertInstanceOf($providerClassName, $detectedProvider);
		$this->assertSame($extensionKey, $detectedProvider->getExtensionKey($record));
		$this->assertSame($absoluteTemplatePathAndFilename, $detectedProvider->getTemplatePathAndFilename($record));
		$this->assertSame(PathUtility::translatePath($paths), $detectedProvider->getTemplatePaths($record));
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
		$providerClassName = 'FluidTYPO3\Flux\Provider\ProviderInterface';
		$relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['list_type'] = $pluginType;
		$absoluteTemplatePathAndFilename = GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
		$configurationSectionName = 'Configuration';
		Core::registerFluidFlexFormPlugin($extensionKey, $pluginType, $relativeTemplatePathAndFilename,
			$variables, $configurationSectionName, $paths);
		$detectedProvider = $service->resolvePrimaryConfigurationProvider('tt_content', $fieldName, $record, $extensionKey);
		$this->assertInstanceOf($providerClassName, $detectedProvider);
		$this->assertSame($extensionKey, $detectedProvider->getExtensionKey($record));
		$this->assertSame($absoluteTemplatePathAndFilename, $detectedProvider->getTemplatePathAndFilename($record));
		$this->assertSame(PathUtility::translatePath($paths), $detectedProvider->getTemplatePaths($record));
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
		$providerClassName = 'FluidTYPO3\Flux\Provider\ProviderInterface';
		$relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$absoluteTemplatePathAndFilename = GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
		$configurationSectionName = 'Configuration';
		Core::registerFluidFlexFormTable($table, $fieldName, $relativeTemplatePathAndFilename,
			$variables, $configurationSectionName, $paths);
		$detectedProvider = $service->resolvePrimaryConfigurationProvider($table, $fieldName, $record);
		$this->assertInstanceOf($providerClassName, $detectedProvider);
		$this->assertSame($absoluteTemplatePathAndFilename, $detectedProvider->getTemplatePathAndFilename($record));
		$this->assertSame(PathUtility::translatePath($paths), $detectedProvider->getTemplatePaths($record));
	}

	/**
	 * @test
	 */
	public function canUnregisterFormForModelClassName() {
		$fakeClass = 'MyFakeClass';
		$form = Form::create();
		Core::registerFormForModelObjectClassName($fakeClass, $form);
		$this->assertSame($form, Core::getRegisteredFormForModelObjectClass($fakeClass));
		Core::unregisterFormForModelObjectClassName($fakeClass);
		$this->assertNull(Core::getRegisteredFormForModelObjectClass($fakeClass));
	}

	/**
	 * @test
	 */
	public function canAddAndRetrieveGlobalTypoScript() {
		Core::addStaticTypoScript(self::FIXTURE_TYPOSCRIPT_DIR);
		$registered = Core::getStaticTypoScript();
		$this->assertContains(self::FIXTURE_TYPOSCRIPT_DIR, $registered);
	}

	/**
	 * @test
	 */
	public function canAddAndRetrieveGlobalTypoScriptCollections() {
		Core::addStaticTypoScript(array(self::FIXTURE_TYPOSCRIPT_DIR));
		$registered = Core::getStaticTypoScript();
		$this->assertContains(self::FIXTURE_TYPOSCRIPT_DIR, $registered);
	}

	/**
	 * @test
	 */
	public function canGetRegisteredFormsForModelClassNames() {
		$fakeClass = 'MyFakeClass';
		$form = Form::create();
		Core::registerFormForModelObjectClassName($fakeClass, $form);
		$this->assertSame($form, Core::getRegisteredFormForModelObjectClass($fakeClass));
		$this->assertContains($form, Core::getRegisteredFormsForModelObjectClasses());
		Core::unregisterFormForModelObjectClassName($fakeClass);

	}

	/**
	 * @test
	 */
	public function canAddAndRetrieveOutlets() {
		$fakeClass = 'MyFakeClass';
		Core::registerOutlet($fakeClass);
		$this->assertContains($fakeClass, Core::getOutlets());
		Core::unregisterOutlet($fakeClass);
		$this->assertNotContains($fakeClass, Core::getOutlets());
	}

	/**
	 * @test
	 */
	public function canAddAndRetrievePipes() {
		$fakeClass = 'MyFakeClass';
		Core::registerPipe($fakeClass);
		$this->assertContains($fakeClass, Core::getPipes());
		Core::unregisterPipe($fakeClass);
		$this->assertNotContains($fakeClass, Core::getPipes());
	}

	/**
	 * @test
	 */
	public function canUnregisterNotCurrentlyRegisteredProviders() {
		$fakeClass = 'MyFakeClass';
		Core::unregisterConfigurationProvider($fakeClass);
		core::registerConfigurationProvider($fakeClass);
		$this->assertNotContains($fakeClass, Core::getRegisteredFlexFormProviders());
	}

}
