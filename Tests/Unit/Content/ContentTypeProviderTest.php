<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeForm;
use FluidTYPO3\Flux\Content\ContentTypeProvider;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class ContentTypeProviderTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        $this->singletonInstances[FluxService::class] = $this->getMockBuilder(FluxService::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function testTriggersOnMatchedTableAndFieldWithAnyRowAndAnyExtension(): void
    {
        $subject = new ContentTypeProvider();
        self::assertTrue($subject->trigger([], 'content_types', 'content_configuration', 'anything'));
    }

    public function testDoesNotTriggerOnUnmatchedTableWithAnyRowAndAnyExtension(): void
    {
        $subject = new ContentTypeProvider();
        self::assertFalse($subject->trigger([], 'not_matched', 'content_configuration', 'anything'));
    }

    public function testDoesNotTriggerOnUnmatchedFieldWithAnyRowAndAnyExtension(): void
    {
        $subject = new ContentTypeProvider();
        self::assertFalse($subject->trigger([], 'content_tyoes', 'not_matched', 'anything'));
    }

    public function testCreatesForm(): void
    {
        $record = [
            'content_type' => 'test_foobar',
        ];
        $contentTypeDefinition = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getSheetNamesAndLabels'])
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeDefinition->method('getSheetNamesAndLabels')->willReturn($this->sheetAndLabelNameGenerator());
        $subject = $this->getMockBuilder(ContentTypeProvider::class)
            ->setMethods(['resolveContentTypeDefinition'])
            ->getMock();
        $subject->method('resolveContentTypeDefinition')
            ->with($record)
            ->willReturn($contentTypeDefinition);
        /** @var Form $output */
        $output = $subject->getForm($record);
        self::assertInstanceOf(ContentTypeForm::class, $output);
        self::assertInstanceOf(Form\Container\Sheet::class, $output->get('sheet'));
    }

    protected function sheetAndLabelNameGenerator(): \Generator
    {
        yield "sheet" => "Label";
    }
}
