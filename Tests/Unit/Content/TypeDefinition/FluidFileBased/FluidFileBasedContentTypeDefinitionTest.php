<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content\TypeDefinition\FluidFileBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\FluidFileBasedContentTypeDefinition;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class FluidFileBasedContentTypeDefinitionTest extends AbstractTestCase
{
    protected ?ProviderResolver $providerResolver = null;

    protected function setUp(): void
    {
        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->setMethods(['resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->singletonInstances[ProviderResolver::class] = $this->providerResolver;

        parent::setUp();
    }

    public function testConstructingInstanceFillsExpectedProperties(): void
    {
        $relativePath = 'AbsolutelyMinimal.html';
        $path = realpath('Tests/Fixtures/Templates/Content') . '/';
        $subject = new FluidFileBasedContentTypeDefinition(
            'FluidTYPO3.Flux',
            $path,
            $relativePath,
            Provider::class
        );

        self::assertSame('FluidTYPO3.Flux', $subject->getExtensionIdentity(), 'Extension identity is unexpected value');
        self::assertSame(Provider::class, $subject->getProviderClassName(), 'Provider class name is unexpected value');
        self::assertSame('', $subject->getIconReference(), 'Icon reference is unexpected value');
        self::assertTrue($subject->isUsingTemplateFile(), 'Return from isUsingTemplateFile() is unexpected value');
        self::assertFalse(
            $subject->isUsingGeneratedTemplateSource(),
            'Return from isUsingGeneratedTemplateSource() is unexpected value'
        );
        self::assertSame(
            $path . $relativePath,
            $subject->getTemplatePathAndFilename(),
            'Returned template path and filename is unexpected value'
        );
        self::assertSame(
            'flux_absolutelyminimal',
            $subject->getContentTypeName(),
            'Content type name is unexpected value'
        );
    }

    public function testFetchContentTypesReturnsEmptyArray(): void
    {
        self::assertSame([], FluidFileBasedContentTypeDefinition::fetchContentTypes());
    }

    public function testGetFormReturnsFormFromProvider(): void
    {
        $form = Form::create(['name' => 'test']);

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $subject = new FluidFileBasedContentTypeDefinition('', '', '');
        self::assertSame($form, $subject->getForm([]));
    }

    public function testGetFormReturnsDefaultFormWithoutProvider(): void
    {
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn(null);

        $subject = new FluidFileBasedContentTypeDefinition('', '', '');
        self::assertInstanceOf(Form::class, $subject->getForm([]));
    }

    public function testGetGridReturnsGridFromProvider(): void
    {
        $grid = Form\Container\Grid::create(['name' => 'grid']);

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getGrid')->willReturn($grid);

        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $subject = new FluidFileBasedContentTypeDefinition('', '', '');
        self::assertSame($grid, $subject->getGrid([]));
    }

    public function testGetGridReturnsDefaultGridWithoutProvider(): void
    {
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn(null);

        $subject = new FluidFileBasedContentTypeDefinition('', '', '');
        self::assertInstanceOf(Form\Container\Grid::class, $subject->getGrid([]));
    }
}
