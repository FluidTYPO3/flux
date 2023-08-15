<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\RuntimeDefinedContentProvider;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinitionRepository;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class RuntimeDefinedContentProviderTest extends AbstractTestCase
{
    protected FormDataTransformer $formDataTransformer;
    protected WorkspacesAwareRecordService $recordService;
    protected ViewBuilder $viewBuilder;
    protected CacheService $cacheService;
    protected ContentTypeManager $contentTypeManager;
    protected ContentTypeDefinitionInterface $contentTypeDefinition;

    protected function setUp(): void
    {
        $this->contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->onlyMethods(['determineContentTypeForRecord'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentTypeDefinition = $this->getMockBuilder(FluidRenderingContentTypeDefinitionInterface::class)
            ->getMockForAbstractClass();
        $this->formDataTransformer = $this->getMockBuilder(FormDataTransformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->disableOriginalConstructor()
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
        $this->singletonInstances[RecordBasedContentTypeDefinitionRepository::class]
            = $this->getMockBuilder(RecordBasedContentTypeDefinitionRepository::class)
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
            $this->contentTypeManager,
        ];
    }

    public function testGetControllerActionFromRecordReturnsProxy(): void
    {
        $subject = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());
        self::assertSame('proxy', $subject->getControllerActionFromRecord([]));
    }

    public function testGetExtensionKey(): void
    {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getExtensionIdentity')->willReturn('test');

        $subject = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());
        self::assertSame('test', $subject->getExtensionKey(['CType' => 'test']));
    }

    public function testGetExtensionKeyThrowsExceptionOnMissingContentTypeDefinition(): void
    {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn(null);

        self::expectExceptionCode(1556109085);
        $subject = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());
        $subject->getExtensionKey(['CType' => 'test']);
    }

    public function testGetControllerExtensionKeyFromRecord(): void
    {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getExtensionIdentity')->willReturn('test');

        $subject = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());
        self::assertSame('test', $subject->getControllerExtensionKeyFromRecord(['CType' => 'test']));
    }

    public function testGetForm(): void
    {
        $form = Form::create();

        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getForm')->willReturn($form);

        $subject = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());
        self::assertSame($form, $subject->getForm(['CType' => 'test']));
    }

    public function testGetGrid(): void
    {
        $grid = Form\Container\Grid::create();

        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getGrid')->willReturn($grid);

        $subject = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());
        self::assertSame($grid, $subject->getGrid(['CType' => 'test']));
    }

    public function testGetTemplatePathOrFilename(): void
    {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getTemplatePathAndFilename')->willReturn('test');

        $subject = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());
        self::assertSame('test', $subject->getTemplatePathAndFilename(['CType' => 'test']));
    }

    public function testPostProcessDataStructure(): void
    {
        $form = Form::create();
        $dataStructure = ['foo' => 'bar'];
        $record = ['CType' => 'test'];

        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getForm')->willReturn($form);

        $subject = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());
        $subject->postProcessDataStructure($record, $dataStructure, []);

        $expected = [
            'meta' => [
                'langDisable' => 1,
                'langChildren' => 0,
            ],
            'ROOT' => [
                'type' => 'array',
                'el' => [],
            ],
        ];

        self::assertSame($expected, $dataStructure);
    }

    public function testGetTemplateVariables(): void
    {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);

        $subject = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());

        $record = ['CType' => 'test'];

        $expected = [
            'record' => $record,
            'settings' => [],
            'page' => [],
            'user' => [],
            'contentType' => $this->contentTypeDefinition,
            'provider' => $subject,
        ];
        self::assertSame($expected, $subject->getTemplateVariables($record));
    }

    /**
     * @dataProvider getTriggerTestValues
     */
    public function testTrigger(
        bool $expected,
        string $table,
        ?string $field,
        ?ContentTypeDefinitionInterface $contentTypeDefinition
    ): void {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($contentTypeDefinition);
        $instance = new RuntimeDefinedContentProvider(...$this->getConstructorArguments());
        self::assertSame($expected, $instance->trigger([], $table, $field));
    }

    public function getTriggerTestValues(): array
    {
        $badContentType = $this->getMockBuilder(ContentTypeDefinitionInterface::class)->getMockForAbstractClass();
        $goodContentType = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'false with mismatched table' => [false, 'sometable', 'pi_flexform', null],
            'false with mismatched field' => [false, 'tt_content', 'some_field', null],
            'false with null field' => [false, 'tt_content', null, null],
            'false without content type' => [false, 'tt_content', 'pi_flexform', null],
            'false with incorrect content type definition' => [false, 'tt_content', 'pi_flexform', $badContentType],
            'true with correct content type definition' => [false, 'tt_content', 'pi_flexform', $goodContentType],
        ];
    }
}
