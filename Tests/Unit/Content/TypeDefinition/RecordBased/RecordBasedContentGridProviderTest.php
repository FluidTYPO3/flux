<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content\TypeDefinition\RecordBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentGridProvider;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class RecordBasedContentGridProviderTest extends AbstractTestCase
{
    protected FluxService $fluxService;
    protected WorkspacesAwareRecordService $recordService;
    protected ViewBuilder $viewBuilder;
    protected CacheService $cacheService;
    protected ContentTypeManager $contentTypeManager;
    protected TypoScriptService $typoScriptService;

    protected function setUp(): void
    {
        $this->contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->onlyMethods(['determineContentTypeForRecord'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fluxService = $this->getMockBuilder(FluxService::class)
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

        parent::setUp();
    }

    protected function getConstructorArguments(): array
    {
        return [
            $this->fluxService,
            $this->recordService,
            $this->getMockBuilder(ViewBuilder::class)->disableOriginalConstructor()->getMock(),
            $this->cacheService,
            $this->typoScriptService,
            $this->contentTypeManager,
        ];
    }

    public function testTriggerReturnsTrueOnMatchedTableAndField(): void
    {
        $subject = new RecordBasedContentGridProvider(...$this->getConstructorArguments());
        self::assertTrue($subject->trigger([], 'content_types', 'grid'));
    }

    public function testTriggerReturnsFalseOnUnmatchedTable(): void
    {
        $subject = new RecordBasedContentGridProvider(...$this->getConstructorArguments());
        self::assertFalse($subject->trigger([], 'not_matched', 'grid'));
    }

    public function testTriggerReturnsFalseOnUnmatchedField(): void
    {
        $subject = new RecordBasedContentGridProvider(...$this->getConstructorArguments());
        self::assertFalse($subject->trigger([], 'content_types', 'not_matched'));
    }

    public function testPostProcessDataStructureReturnsStructureFromContentGridForm(): void
    {
        $expected = [
            'meta' => [
                'langDisable' => 1,
                'langChildren' => 0,
            ],
            'sheets' => [
                'grid' => [
                    'ROOT' => [
                        'sheetTitle' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux..sheets.grid',
                        'sheetDescription' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux..sheets.grid.description',
                        'sheetShortDescr' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux..sheets.grid.shortDescription',
                        'type' => 'array',
                        'el' => [
                            'gridMode' => [
                                'label' => 'Grid mode',
                                'exclude' => 0,
                                'config' => [
                                    'type' => 'select',
                                    'default' => 'rows',
                                    'size' => 1,
                                    'minitems' => 0,
                                    'multiple' => false,
                                    'renderType' => 'selectSingle',
                                    'items' => [
                                        ['rows' ,'rows'],
                                        ['columns', 'columns'],
                                    ],
                                ],
                            ],
                            'autoColumns' => [
                                'label' => 'Automatic content columns (adds automatic columns AFTER those defined below, until this number of total columns is reached)',
                                'exclude' => 0,
                                'config' => [
                                    'type' => 'input',
                                    'size' => 3,
                                    'eval' => 'trim,int',
                                ],
                            ],
                            'columns' => [
                                'type' => 'array',
                                'title' => 'Manual content columns',
                                'section' => '1',
                                'el' => [
                                    'column' => [
                                        'title' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux..objects.column',
                                        'type' => 'array',
                                        'el' => [
                                            'colPos' => [
                                                'label' => 'Column position value',
                                                'exclude' => 0,
                                                'config' => [
                                                    'type' => 'user',
                                                    'renderType' => 'fluxColumnPosition',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $dataStructure = [];
        $row = [];
        $subject = new RecordBasedContentGridProvider(...$this->getConstructorArguments());
        $subject->postProcessDataStructure($row, $dataStructure, []);

        self::assertSame($expected, $dataStructure);
    }

    public function testGetGridReturnsGridFromContentTypeDefinition(): void
    {
        $grid = Form\Container\Grid::create();

        $contentTypeDefinition = $this->getMockBuilder(ContentTypeDefinitionInterface::class)
            ->getMockForAbstractClass();
        $contentTypeDefinition->method('getGrid')->willReturn($grid);

        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn($contentTypeDefinition);

        $subject = new RecordBasedContentGridProvider(...$this->getConstructorArguments());

        self::assertSame($grid, $subject->getGrid([]));
    }

    public function testGetGridReturnsGridFromParentIfContentTypeDefinitionNotFound(): void
    {
        $grid = Form\Container\Grid::create();

        $this->contentTypeManager->method('determineContentTypeForRecord')->willReturn(null);

        $subject = new RecordBasedContentGridProvider(...$this->getConstructorArguments());
        $subject->setGrid($grid);

        self::assertSame($grid, $subject->getGrid([]));
    }
}
