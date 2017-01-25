<?php
namespace FluidTYPO3\Flux\Tests\Unit;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CoreTest
 */
class CoreTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function returnsEmptyArrayForUnknownExtensionKeysAndControllerObjects()
    {
        $fakeControllerName = 'Flux';
        $registered = Core::getRegisteredProviderExtensionKeys($fakeControllerName);
        $this->assertEmpty($registered);
    }

    /**
     * @test
     */
    public function canRegisterFormInstanceForModelClassName()
    {
        $class = 'Tx_Flux_Domain_Model_Fake';
        $form = Form::create();
        Core::registerFormForModelObjectClassName($class, $form);
        $registered = Core::getRegisteredFormForModelObjectClass($class);
        $this->assertEquals($form, $registered);
    }

    /**
     * @test
     */
    public function canRegisterAutoFormInstanceForModelClassName()
    {
        $class = 'Tx_Flux_Domain_Model_Fake';
        Core::registerAutoFormForModelObjectClassName($class);
        $registered = Core::getRegisteredFormForModelObjectClass($class);
        $this->assertEquals(null, $registered);
    }

    /**
     * @test
     */
    public function canRegisterFormInstanceForTable()
    {
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
    public function canRegisterProviderExtensionKey()
    {
        $fakeExtensionKey = 'flux';
        $fakeControllerName = 'Flux';
        Core::registerProviderExtensionKey($fakeExtensionKey, $fakeControllerName);
        $registered = Core::getRegisteredProviderExtensionKeys($fakeControllerName);
        $this->assertContains($fakeExtensionKey, $registered);
    }

    /**
     * @test
     */
    public function canRegisterProviderInstance()
    {
        /** @var \FluidTYPO3\Flux\Provider\ProviderInterface $provider */
        $provider = $this->objectManager->get('FluidTYPO3\Flux\Provider\Provider');
        Core::registerConfigurationProvider($provider);
        $registered = Core::getRegisteredFlexFormProviders();
        $this->assertContains($provider, $registered);
    }

    /**
     * @test
     */
    public function canRegisterAndUnregisterProviderClassName()
    {
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
    public function canRegisterStandaloneTemplateForContentObject()
    {
        $variables = array('test' => 'test');
        $paths = array('templateRootPaths' => array('EXT:flux/Resources/Private/Templates'));
        $extensionKey = 'fake';
        $contentObjectType = 'void';
        $relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $record['CType'] = $contentObjectType;
        $absoluteTemplatePathAndFilename = GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
        $configurationSectionName = 'Configuration';
        $result = Core::registerFluidFlexFormContentObject(
            $extensionKey,
            $contentObjectType,
            $absoluteTemplatePathAndFilename,
            $variables,
            $configurationSectionName,
            $paths
        );
        $this->assertInstanceOf(ProviderInterface::class, $result);
    }

    /**
     * @test
     */
    public function canRegisterStandaloneTemplateForPlugin()
    {
        $variables = array('test' => 'test');
        $paths = array('templateRootPaths' => array('EXT:flux/Resources/Private/Templates'));
        $extensionKey = 'more_fake';
        $pluginType = 'void';
        $fieldName = null;
        $relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $record['list_type'] = $pluginType;
        $absoluteTemplatePathAndFilename = GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
        $configurationSectionName = 'Configuration';
        $result = Core::registerFluidFlexFormPlugin(
            $extensionKey,
            $pluginType,
            $absoluteTemplatePathAndFilename,
            $variables,
            $configurationSectionName,
            $paths
        );
        $this->assertInstanceOf(ProviderInterface::class, $result);
    }

    /**
     * @test
     */
    public function canRegisterStandaloneTemplateForTable()
    {
        $variables = array('test' => 'test');
        $paths = array('templateRootPaths' => array('EXT:flux/Resources/Private/Templates'));
        $table = 'fake';
        $fieldName = null;
        $relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
        $absoluteTemplatePathAndFilename = GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
        $configurationSectionName = 'Configuration';
        $result = Core::registerFluidFlexFormTable(
            $table,
            $fieldName,
            $absoluteTemplatePathAndFilename,
            $variables,
            $configurationSectionName,
            $paths
        );
        $this->assertInstanceOf(ProviderInterface::class, $result);
    }

    /**
     * @test
     */
    public function canRegisterTemplateAsContentType()
    {
        $fieldName = null;
        $relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
        $absoluteTemplatePathAndFilename = GeneralUtility::getFileAbsFileName($relativeTemplatePathAndFilename);
        $result = Core::registerTemplateAsContentType(
            'FluidTYPO3.Flux',
            $absoluteTemplatePathAndFilename
        );
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canUnregisterFormForModelClassName()
    {
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
    public function canAddAndRetrieveGlobalTypoScript()
    {
        Core::addStaticTypoScript(self::FIXTURE_TYPOSCRIPT_DIR);
        $registered = Core::getStaticTypoScript();
        $this->assertContains(self::FIXTURE_TYPOSCRIPT_DIR, $registered);
    }

    /**
     * @test
     */
    public function canAddAndRetrieveGlobalTypoScriptCollections()
    {
        Core::addStaticTypoScript(array(self::FIXTURE_TYPOSCRIPT_DIR));
        $registered = Core::getStaticTypoScript();
        $this->assertContains(self::FIXTURE_TYPOSCRIPT_DIR, $registered);
    }

    /**
     * @test
     */
    public function canGetRegisteredFormsForModelClassNames()
    {
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
    public function canAddAndRetrieveOutlets()
    {
        $fakeClass = 'MyFakeClass';
        Core::registerOutlet($fakeClass);
        $this->assertContains($fakeClass, Core::getOutlets());
        Core::unregisterOutlet($fakeClass);
        $this->assertNotContains($fakeClass, Core::getOutlets());
    }

    /**
     * @test
     */
    public function canAddAndRetrievePipes()
    {
        $fakeClass = 'MyFakeClass';
        Core::registerPipe($fakeClass);
        $this->assertContains($fakeClass, Core::getPipes());
        Core::unregisterPipe($fakeClass);
        $this->assertNotContains($fakeClass, Core::getPipes());
    }

    /**
     * @test
     */
    public function canUnregisterNotCurrentlyRegisteredProviders()
    {
        $fakeClass = 'MyFakeClass';
        Core::unregisterConfigurationProvider($fakeClass);
        core::registerConfigurationProvider($fakeClass);
        $this->assertNotContains($fakeClass, Core::getRegisteredFlexFormProviders());
    }

    /**
     * @test
     */
    public function canRegisterAndUnregisterPackagesForFormGeneration()
    {
        $fakePackage = 'MyFakeVendor.MyFakePackage';
        Core::registerFluxDomainFormPackage($fakePackage);
        $this->assertArrayHasKey($fakePackage, Core::getRegisteredPackagesForAutoForms());
        Core::unregisterFluxDomainFormPackage($fakePackage);
        $this->assertArrayNotHasKey($fakePackage, Core::getRegisteredPackagesForAutoForms());
    }

    /**
     * @test
     */
    public function registerFormForModelObjectClassNameSetsExtensionNameFromExtensionKeyGlobal()
    {
        $GLOBALS['_EXTKEY'] = 'test';
        $form = $this->getMockBuilder('FluidTYPO3\\Flux\\Form')->setMethods(array('setExtensionName'))->getMock();
        $form->expects($this->once())->method('setExtensionName')->with('Test');
        Core::registerFormForModelObjectClassName('FooBar', $form);
        unset($GLOBALS['_EXTKEY']);
    }

    /**
     * @test
     */
    public function registerFormForTableSetsExtensionNameFromExtensionKeyGlobal()
    {
        $GLOBALS['_EXTKEY'] = 'test';
        $form = $this->getMockBuilder('FluidTYPO3\\Flux\\Form')->setMethods(array('setExtensionName'))->getMock();
        $form->expects($this->once())->method('setExtensionName')->with('Test');
        Core::registerFormForTable('foobar', $form);
        unset($GLOBALS['_EXTKEY']);
    }
}
