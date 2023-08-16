<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content\TypeDefinition\FluidFileBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\DropInContentTypeDefinition;
use FluidTYPO3\Flux\Enum\ExtensionOption;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;

class DropInContentTypeDefinitionTest extends AbstractTestCase
{
    private array $setup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = [];
        $this->setup = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'];

        $packageManager = $this->getMockBuilder(PackageManager::class)
            ->setMethods(['getPackage', 'isPackageActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $package = $this->getMockBuilder(Package::class)
            ->setMethods(['getPackagePath'])
            ->disableOriginalConstructor()
            ->getMock();
        $package->method('getPackagePath')->willReturn('./');

        $packageManager->method('getPackage')->willReturn($package);
        $packageManager->method('isPackageActive')->willReturn(true);

        AccessibleExtensionManagementUtility::setPackageManager($packageManager);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = [];
    }


    public function testFetchContentTypesReturnsEmptyArrayIfPlugAndPlayConfigurationIsDisabled(): void
    {
        $this->setup[ExtensionOption::OPTION_PLUG_AND_PLAY] = false;
        $result = DropInContentTypeDefinition::fetchContentTypes();
        self::assertSame([], $result);
    }

    public function testFetchContentTypesReturnsEmptyArrayIfPlugAndPlayDirectoryOptionValueIsNotScalar(): void
    {
        $this->setup[ExtensionOption::OPTION_PLUG_AND_PLAY] = true;
        $this->setup[ExtensionOption::OPTION_PLUG_AND_PLAY_DIRECTORY] = [];
        $result = DropInContentTypeDefinition::fetchContentTypes();
        self::assertSame([], $result);
    }

    public function testFetchContentTypesReturnsEmptyArrayIfConfiguredDirectoryContainsNoTemplateFiles(): void
    {
        vfsStream::setup('dropin');
        $vfsUrl = vfsStream::url('dropin');

        $this->setup[ExtensionOption::OPTION_PLUG_AND_PLAY] = true;
        $this->setup[ExtensionOption::OPTION_PLUG_AND_PLAY_DIRECTORY] = $vfsUrl;

        $result = DropInContentTypeDefinition::fetchContentTypes();
        self::assertSame([], $result);
    }

    public function testFetchContentTypesAutoCreatesBasicDirectoryStructure(): void
    {
        $root = vfsStream::setup('root', null, ['foo.txt' => 'bar']);
        $vfsUrl = vfsStream::url('root/templates');

        $this->setup[ExtensionOption::OPTION_PLUG_AND_PLAY] = true;
        $this->setup[ExtensionOption::OPTION_PLUG_AND_PLAY_DIRECTORY] = $vfsUrl;

        $result = DropInContentTypeDefinition::fetchContentTypes();

        self::assertInstanceOf(DropInContentTypeDefinition::class, $result['flux_standard']);
        self::assertSame('flux_standard', $result['flux_standard']->getContentTypeName());
    }

    public function testConstructingInstanceFillsExpectedProperties(): void
    {
        $relativePath = 'Tests/Fixtures/Templates/Content/AbsolutelyMinimal.html';
        $path = realpath('.');
        $subject = new DropInContentTypeDefinition(
            'FluidTYPO3.Flux',
            (string) $path,
            $relativePath,
            Provider::class
        );

        self::assertSame('FluidTYPO3.Flux', $subject->getExtensionIdentity(), 'Extension identity is unexpected value');
        self::assertSame(Provider::class, $subject->getProviderClassName(), 'Provider class name is unexpected value');
        self::assertSame('', $subject->getIconReference(), 'Icon reference is unexpected value');
    }
}
