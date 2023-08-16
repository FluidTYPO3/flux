<?php
namespace FluidTYPO3\Flux\Tests\Unit;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleCore;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyContentTypeManager;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CoreTest extends AbstractTestCase
{
    protected $objectManager;
    private array $singletons;

    protected function setUp(): void
    {
        parent::setUp();

        GeneralUtility::addInstance(
            Provider::class,
            $this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock()
        );
        AccessibleCore::resetQueuedRegistrations();

        $this->singletons = GeneralUtility::getSingletonInstances();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        GeneralUtility::resetSingletonInstances($this->singletons);
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
    public function canRegisterProviderExtensionKey()
    {
        $fakeExtensionKey = 'flux';
        $fakeControllerName = 'Flux';
        $contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        GeneralUtility::setSingletonInstance(ContentTypeManager::class, $contentTypeManager);
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

        $contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        GeneralUtility::setSingletonInstance(ContentTypeManager::class, $contentTypeManager);
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
        $variables = ['test' => 'test'];
        $paths = ['templateRootPaths' => ['Resources/Private/Templates']];
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
        $variables = ['test' => 'test'];
        $paths = ['templateRootPaths' => ['Resources/Private/Templates']];
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
        $variables = ['test' => 'test'];
        $paths = ['templateRootPaths' => ['Resources/Private/Templates']];
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
    public function canUnregisterNotCurrentlyRegisteredProviders()
    {
        $fakeClass = 'MyFakeClass';
        AccessibleCore::unregisterConfigurationProvider($fakeClass);
        AccessibleCore::registerConfigurationProvider($fakeClass);
        $this->assertNotContains($fakeClass, AccessibleCore::getRegisteredFlexFormProviders());
    }
}
