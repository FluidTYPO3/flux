<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\RuntimeDefinedContentProvider;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinitionRepository;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class RuntimeDefinedContentProviderTest extends AbstractTestCase
{
    protected ?ContentTypeManager $contentTypeManager;
    protected ?ContentTypeDefinitionInterface $contentTypeDefinition;

    protected function setUp(): void
    {
        $this->contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['determineContentTypeForRecord'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentTypeDefinition = $this->getMockBuilder(FluidRenderingContentTypeDefinitionInterface::class)
            ->getMockForAbstractClass();

        $this->singletonInstances[ContentTypeManager::class] = $this->contentTypeManager;
        $this->singletonInstances[FluxService::class] = $this->getMockBuilder(FluxService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->singletonInstances[WorkspacesAwareRecordService::class] = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->singletonInstances[RecordBasedContentTypeDefinitionRepository::class] = $this->getMockBuilder(RecordBasedContentTypeDefinitionRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function testGetControllerActionFromRecordReturnsProxy(): void
    {
        $subject = new RuntimeDefinedContentProvider();
        self::assertSame('proxy', $subject->getControllerActionFromRecord([]));
    }

    public function testGetExtensionKey(): void
    {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getExtensionIdentity')->willReturn('test');

        $subject = new RuntimeDefinedContentProvider();
        self::assertSame('test', $subject->getExtensionKey(['CType' => 'test']));
    }

    public function testGetExtensionKeyThrowsExceptionOnMissingContentTypeDefinition(): void
    {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn(null);

        self::expectExceptionCode(1556109085);
        $subject = new RuntimeDefinedContentProvider();
        $subject->getExtensionKey(['CType' => 'test']);
    }

    public function testGetControllerExtensionKeyFromRecord(): void
    {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getExtensionIdentity')->willReturn('test');

        $subject = new RuntimeDefinedContentProvider();
        self::assertSame('test', $subject->getControllerExtensionKeyFromRecord(['CType' => 'test']));
    }

    public function testGetForm(): void
    {
        $form = Form::create();

        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getForm')->willReturn($form);

        $subject = new RuntimeDefinedContentProvider();
        self::assertSame($form, $subject->getForm(['CType' => 'test']));
    }

    public function testGetGrid(): void
    {
        $grid = Form\Container\Grid::create();

        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getGrid')->willReturn($grid);

        $subject = new RuntimeDefinedContentProvider();
        self::assertSame($grid, $subject->getGrid(['CType' => 'test']));
    }

    public function testGetTemplatePathOrFilename(): void
    {
        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getTemplatePathAndFilename')->willReturn('test');

        $subject = new RuntimeDefinedContentProvider();
        self::assertSame('test', $subject->getTemplatePathAndFilename(['CType' => 'test']));
    }

    public function testPostProcessDataStructure(): void
    {
        $form = Form::create();
        $dataStructure = ['foo' => 'bar'];
        $record = ['CType' => 'test'];

        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($this->contentTypeDefinition);
        $this->contentTypeDefinition->method('getForm')->willReturn($form);

        $subject = new RuntimeDefinedContentProvider();
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

        $subject = new RuntimeDefinedContentProvider();

        $record = ['CType' => 'test'];

        $expected = [
            'record' => $record,
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
        $instance = new RuntimeDefinedContentProvider();
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
