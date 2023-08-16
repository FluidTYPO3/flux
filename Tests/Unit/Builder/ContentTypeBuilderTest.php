<?php
namespace FluidTYPO3\Flux\Tests\Unit\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ContentTypeBuilder;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

class ContentTypeBuilderTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = '';

        $package = $this->getMockBuilder(Package::class)
            ->onlyMethods(['getPackagePath'])
            ->disableOriginalConstructor()
            ->getMock();
        $package->method('getPackagePath')->willReturn('.');

        $packageManager = $this->getMockBuilder(PackageManager::class)
            ->onlyMethods(['getPackage', 'isPackageActive'])
            ->disableOriginalConstructor()
            ->getMock();
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
    }

    public function testAddBoilerplateTableConfiguration(): void
    {
        $subject = new ContentTypeBuilder();
        $subject->addBoilerplateTableConfiguration('foobar');
        $this->assertNotEmpty($GLOBALS['TCA']['tt_content']['types']['foobar']);
    }

    public function testRegisterContentType(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [];

        $subject = $this->getMockBuilder(ContentTypeBuilder::class)
            ->onlyMethods(['createIcon'])
            ->getMock();
        $subject->method('createIcon')->willReturn('icon');
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->expects($this->once())->method('getForm')->willReturn($form);

        $subject->registerContentType(
            'FluidTYPO3.Flux',
            'foobarextension',
            $provider
        );
        self::assertTrue(true);
    }

    public function testConfigureContentTypeFromTemplateFile(): void
    {
        $provider = $this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock();
        GeneralUtility::addInstance(Provider::class, $provider);
        $subject = $this->getMockBuilder(ContentTypeBuilder::class)
            ->addMethods(['dummy'])->getMock();
        $result = $subject->configureContentTypeFromTemplateFile(
            'FluidTYPO3.Flux',
            $this->getAbsoluteFixtureTemplatePathAndFilename(static::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL)
        );
        $this->assertInstanceOf(Provider::class, $result);
    }

    public function testThrowsExceptionOnInvalidProviderClass(): void
    {
        $this->expectExceptionCode(1690816678);
        (new ContentTypeBuilder())->configureContentTypeFromTemplateFile(
            'FluidTYPO3.Flux',
            '/dev/null',
            \DateTime::class,
        );
    }
}
