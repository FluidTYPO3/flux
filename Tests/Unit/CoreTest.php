<?php
namespace FluidTYPO3\Flux\Tests\Unit;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleCore;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CoreTest extends AbstractTestCase
{
    protected $objectManager;

    protected function setUp(): void
    {
        parent::setUp();

        GeneralUtility::addInstance(
            Provider::class,
            $this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock()
        );
        AccessibleCore::resetQueuedRegistrations();
    }

    /**
     * @test
     */
    public function returnsEmptyArrayForUnknownExtensionKeysAndControllerObjects()
    {
        $fakeControllerName = 'Flux';
        $registered = AccessibleCore::getRegisteredProviderExtensionKeys($fakeControllerName);
        $this->assertEmpty($registered);
    }

    /**
     * @test
     */
    public function canRegisterFormInstanceForTable()
    {
        $table = 'this_table_does_not_exist';
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        AccessibleCore::registerFormForTable($table, $form);
        $forms = AccessibleCore::getRegisteredFormsForTables();
        $this->assertArrayHasKey($table, $forms);
        $returnedForm = AccessibleCore::getRegisteredFormForTable($table);
        $incorrectReturnedForm = AccessibleCore::getRegisteredFormForTable($table . 'badname');
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
        AccessibleCore::registerProviderExtensionKey($fakeExtensionKey, $fakeControllerName);
        $registered = AccessibleCore::getRegisteredProviderExtensionKeys($fakeControllerName);
        $this->assertContains($fakeExtensionKey, $registered);
    }

    /**
     * @test
     */
    public function canRegisterProviderExtensionKeyWithContentController()
    {
        $package = $this->getMockBuilder(Package::class)
            ->setMethods(['getPackagePath'])
            ->disableOriginalConstructor()
            ->getMock();
        $package->method('getPackagePath')->willReturn('');

        $packageManager = $this->getMockBuilder(PackageManager::class)
            ->setMethods(['isPackageActive', 'getPackage'])
            ->disableOriginalConstructor()
            ->getMock();
        $packageManager->method('getPackage')->willReturn($package);
        $packageManager->method('isPackageActive')->willReturnMap(
            [
                ['fluidcontent', false],
                ['flux', true],
            ]
        );

        AccessibleExtensionManagementUtility::setPackageManager($packageManager);

        $fakeExtensionKey = 'flux';
        $fakeControllerName = 'Content';
        AccessibleCore::registerProviderExtensionKey($fakeExtensionKey, $fakeControllerName);
        $registered = AccessibleCore::getRegisteredProviderExtensionKeys($fakeControllerName);
        $this->assertContains($fakeExtensionKey, $registered);
    }

    /**
     * @test
     */
    public function canRegisterProviderInstance()
    {
        $provider = $this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock();
        AccessibleCore::registerConfigurationProvider($provider);
        $registered = AccessibleCore::getRegisteredFlexFormProviders();
        $this->assertContains($provider, $registered);
    }

    /**
     * @test
     */
    public function canRegisterAndUnregisterProviderClassName()
    {
        $providerClassName = Provider::class;
        AccessibleCore::registerConfigurationProvider($providerClassName);
        $registered = AccessibleCore::getRegisteredFlexFormProviders();
        $this->assertContains($providerClassName, $registered);
        AccessibleCore::unregisterConfigurationProvider($providerClassName);
        $registered = AccessibleCore::getRegisteredFlexFormProviders();
        $this->assertNotContains($providerClassName, $registered);
    }

    /**
     * @test
     */
    public function canRegisterStandaloneTemplateForContentObject()
    {
        $variables = array('test' => 'test');
        $paths = array('templateRootPaths' => array('Resources/Private/Templates'));
        $extensionKey = 'fake';
        $contentObjectType = 'void';
        $relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
        $absoluteTemplatePathAndFilename = str_replace('EXT:flux/', './', $relativeTemplatePathAndFilename);
        $configurationSectionName = 'Configuration';
        $result = AccessibleCore::registerFluidFlexFormContentObject(
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
        $paths = array('templateRootPaths' => array('Resources/Private/Templates'));
        $extensionKey = 'more_fake';
        $pluginType = 'void';
        $fieldName = null;
        $relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $record['list_type'] = $pluginType;
        $absoluteTemplatePathAndFilename = $relativeTemplatePathAndFilename;
        $configurationSectionName = 'Configuration';
        $result = AccessibleCore::registerFluidFlexFormPlugin(
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
        $paths = array('templateRootPaths' => array('Resources/Private/Templates'));
        $table = 'fake';
        $fieldName = null;
        $relativeTemplatePathAndFilename = self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
        $absoluteTemplatePathAndFilename = $relativeTemplatePathAndFilename;
        $configurationSectionName = 'Configuration';
        $result = AccessibleCore::registerFluidFlexFormTable(
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
        $absoluteTemplatePathAndFilename = $relativeTemplatePathAndFilename;
        AccessibleCore::registerTemplateAsContentType(
            'FluidTYPO3.Flux',
            $absoluteTemplatePathAndFilename
        );
        $this->assertNotEmpty(AccessibleCore::getQueuedContentTypeRegistrations());
    }

    /**
     * @test
     */
    public function canAddAndRetrieveOutlets()
    {
        $fakeClass = 'MyFakeClass';
        AccessibleCore::registerOutlet($fakeClass);
        $this->assertContains($fakeClass, AccessibleCore::getOutlets());
        AccessibleCore::unregisterOutlet($fakeClass);
        $this->assertNotContains($fakeClass, AccessibleCore::getOutlets());
    }

    /**
     * @test
     */
    public function canAddAndRetrievePipes()
    {
        $fakeClass = 'MyFakeClass';
        AccessibleCore::registerPipe($fakeClass);
        $this->assertContains($fakeClass, AccessibleCore::getPipes());
        AccessibleCore::unregisterPipe($fakeClass);
        $this->assertNotContains($fakeClass, AccessibleCore::getPipes());
    }

    /**
     * @test
     */
    public function canUnregisterNotCurrentlyRegisteredProviders()
    {
        $fakeClass = 'MyFakeClass';
        AccessibleCore::unregisterConfigurationProvider($fakeClass);
        AccessibleCore::registerConfigurationProvider($fakeClass);
        $this->assertNotContains($fakeClass, AccessibleCore::getRegisteredFlexFormProviders());
    }

    /**
     * @test
     */
    public function registerFormForTableSetsExtensionNameFromExtensionKeyGlobal()
    {
        $GLOBALS['_EXTKEY'] = 'test';
        $form = $this->getMockBuilder(Form::class)->setMethods(array('setExtensionName', 'getExtensionName'))->getMock();
        $form->method('getExtensionName')->willReturn(null);
        $form->expects($this->once())->method('setExtensionName')->with('Test');
        AccessibleCore::registerFormForTable('foobar', $form);
        unset($GLOBALS['_EXTKEY']);
    }
}
