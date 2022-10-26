<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\ContentTypeBuilder;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Lang\LanguageService;

/**
 * ContentTypeBuilderTest
 */
class ContentTypeBuilderTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = '';

        $package = $this->getMockBuilder(Package::class)->setMethods(['getPackagePath'])->disableOriginalConstructor()->getMock();
        $package->method('getPackagePath')->willReturn('.');

        $packageManager = $this->getMockBuilder(PackageManager::class)->setMethods(['getPackage', 'isPackageActive'])->disableOriginalConstructor()->getMock();
        $packageManager->method('getPackage')->willReturn($package);
        $packageManager->method('isPackageActive')->willReturn(true);

        CompatibilityRegistry::register(ContentTypeBuilder::DEFAULT_SHOWITEM, ['8.7' => 'foo']);
        AccessibleExtensionManagementUtility::setPackageManager($packageManager);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig']);

        CompatibilityRegistry::register(ContentTypeBuilder::DEFAULT_SHOWITEM, []);
        AccessibleExtensionManagementUtility::setPackageManager(null);
    }

    /**
     * @return void
     */
    public function testAddBoilerplateTableConfiguration()
    {
        $subject = new ContentTypeBuilder();
        $subject->addBoilerplateTableConfiguration('foobar');
        $this->assertNotEmpty($GLOBALS['TCA']['tt_content']['types']['foobar']);
    }

    /**
     * @return void
     */
    public function testRegisterContentType()
    {
        $subject = $this->getMockBuilder(ContentTypeBuilder::class)->setMethods(['getCache', 'getRuntimeCache', 'createIcon'])->getMock();
        $subject->method('getCache')->willReturn($this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass());
        $subject->method('getRuntimeCache')->willReturn($this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass());
        $subject->method('createIcon')->willReturn('icon');
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->expects($this->once())->method('getForm')->willReturn($form);

        $GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] = [];
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [];
        $GLOBALS['LANG'] = $this->getMockBuilder(LanguageService::class)->disableOriginalConstructor()->getMock();

        $subject->registerContentType(
            'FluidTYPO3.Flux',
            'foobarextension',
            $provider,
            'FoobarPlugin'
        );
        $this->assertNotEmpty($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items']);
    }

    /**
     * @return void
     */
    public function testConfigureContentTypeFromTemplateFile()
    {
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMockForAbstractClass();
        $objectManager->method('get')->with(Provider::class)->willReturn(new Provider());
        $subject = $this->getMockBuilder(ContentTypeBuilder::class)->setMethods(['getObjectManager'])->getMock();
        $subject->method('getObjectManager')->willReturn($objectManager);
        $result = $subject->configureContentTypeFromTemplateFile(
            'FluidTYPO3.Flux',
            $this->getAbsoluteFixtureTemplatePathAndFilename(static::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL)
        );
        $this->assertInstanceOf(Provider::class, $result);
    }
}
