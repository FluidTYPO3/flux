<?php
namespace FluidTYPO3\Flux\Tests\Unit\Helpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Helper\ContentTypeBuilder;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * ContentTypeBuilderTest
 */
class ContentTypeBuilderTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testAddBoilerplateTableConfiguration()
    {
        $subject = new ContentTypeBuilder();
        $subject->addBoilerplateTableConfiguration('foobar');
        $this->assertNotEmpty($GLOBALS['TCA']['tt_content']['types']['foobar']);
        $this->assertNotNull('foobar', $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['ds']['*,foobar']);
    }

    /**
     * @return void
     */
    public function testRegisterContentTypeThrowsExceptionWhenMissingForm()
    {
        $subject = new ContentTypeBuilder();
        $provider = $this->getMockBuilder(Provider::class)->setMethods(['getForm'])->getMock();
        $provider->expects($this->once())->method('getForm')->willReturn(null);
        $this->expectException(\RuntimeException::class);
        $subject->registerContentType(
            'FluidTYPO3.Flux',
            'foobar',
            $provider,
            'Foobar'
        );
    }

    /**
     * @return void
     */
    public function testRegisterContentType()
    {
        $subject = new ContentTypeBuilder();
        $form = Form::create([]);
        $provider = $this->getMockBuilder(Provider::class)->getMock();
        $provider = $this->getMockBuilder(Provider::class)->setMethods(['getForm'])->getMock();
        $provider->expects($this->once())->method('getForm')->willReturn($form);

        $GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] = [];

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
        $subject = new ContentTypeBuilder();
        $result = $subject->configureContentTypeFromTemplateFile(
            'FluidTYPO3.Flux',
            $this->getAbsoluteFixtureTemplatePathAndFilename(static::FIXTURE_TEMPLATE_BASICGRID)
        );
        $this->assertInstanceOf(Provider::class, $result);
        $this->assertSame('test', $result->getForm([])->getId());
    }
}
