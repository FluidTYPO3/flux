<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Content\ContentTypeForm;
use FluidTYPO3\Flux\Content\ContentTypeProvider;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class ContentTypeProviderTest extends AbstractTestCase
{
    protected FormDataTransformer $formDataTransformer;
    protected WorkspacesAwareRecordService $recordService;
    protected ViewBuilder $viewBuilder;
    protected CacheService $cacheService;
    protected TypoScriptService $typoScriptService;

    protected function setUp(): void
    {
        $this->formDataTransformer = $this->getMockBuilder(FormDataTransformer::class)
            ->onlyMethods(
                [
                    'convertFlexFormContentToArray',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->onlyMethods(['getSingle', 'update'])
            ->getMock();
        $this->viewBuilder = $this->getMockBuilder(ViewBuilder::class)
            ->onlyMethods(['buildTemplateView', 'buildPreviewView'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheService = $this->getMockBuilder(CacheService::class)
            ->onlyMethods(['setInCaches', 'getFromCaches', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->typoScriptService = $this->getMockBuilder(TypoScriptService::class)
            ->onlyMethods(['getSettingsForExtensionName', 'getTypoScriptByPath'])
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    protected function getConstructorArguments(): array
    {
        return [
            $this->formDataTransformer,
            $this->recordService,
            $this->getMockBuilder(ViewBuilder::class)->disableOriginalConstructor()->getMock(),
            $this->cacheService,
            $this->typoScriptService,
        ];
    }

    public function testTriggersOnMatchedTableAndFieldWithAnyRowAndAnyExtension(): void
    {
        $subject = new ContentTypeProvider(...$this->getConstructorArguments());
        self::assertTrue($subject->trigger([], 'content_types', 'content_configuration', 'anything'));
    }

    public function testDoesNotTriggerOnUnmatchedTableWithAnyRowAndAnyExtension(): void
    {
        $subject = new ContentTypeProvider(...$this->getConstructorArguments());
        self::assertFalse($subject->trigger([], 'not_matched', 'content_configuration', 'anything'));
    }

    public function testDoesNotTriggerOnUnmatchedFieldWithAnyRowAndAnyExtension(): void
    {
        $subject = new ContentTypeProvider(...$this->getConstructorArguments());
        self::assertFalse($subject->trigger([], 'content_tyoes', 'not_matched', 'anything'));
    }

    public function testCreatesForm(): void
    {
        $record = [
            'content_type' => 'test_foobar',
        ];
        $contentTypeDefinition = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->onlyMethods(['getSheetNamesAndLabels'])
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeDefinition->method('getSheetNamesAndLabels')->willReturn($this->sheetAndLabelNameGenerator());
        $subject = $this->getMockBuilder(ContentTypeProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['resolveContentTypeDefinition'])
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
