<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content\RecordBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentGridProvider;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class RecordBasedContentGridProviderTest extends AbstractTestCase
{
    public function testTriggerReturnsTrueOnMatchedTableAndField(): void
    {
        $subject = new RecordBasedContentGridProvider();
        self::assertTrue($subject->trigger([], 'content_types', 'grid'));
    }

    public function testTriggerReturnsFalseOnUnmatchedTable(): void
    {
        $subject = new RecordBasedContentGridProvider();
        self::assertFalse($subject->trigger([], 'not_matched', 'grid'));
    }

    public function testTriggerReturnsFalseOnUnmatchedField(): void
    {
        $subject = new RecordBasedContentGridProvider();
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
                        'sheetTitle' => 'grid',
                        'sheetDescription' => 'grid',
                        'sheetShortDescr' => 'grid',
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
                                        'title' => 'column',
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
        $subject = new RecordBasedContentGridProvider();
        $subject->postProcessDataStructure($row, $dataStructure, []);

        self::assertSame($expected, $dataStructure);
    }

    public function testGetGridReturnsGridFromContentTypeDefinition(): void
    {
        $grid = Form\Container\Grid::create();

        $contentTypeDefinition = $this->getMockBuilder(ContentTypeDefinitionInterface::class)
            ->getMockForAbstractClass();
        $contentTypeDefinition->method('getGrid')->willReturn($grid);

        $contentTypes = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['determineContentTypeForRecord'])
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypes->method('determineContentTypeForRecord')->willReturn($contentTypeDefinition);

        $subject = new RecordBasedContentGridProvider();
        $subject->injectContentTypes($contentTypes);

        self::assertSame($grid, $subject->getGrid([]));
    }

    public function testGetGridReturnsGridFromParentIfContentTypeDefinitionNotFound(): void
    {
        $grid = Form\Container\Grid::create();

        $contentTypes = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['determineContentTypeForRecord'])
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypes->method('determineContentTypeForRecord')->willReturn(null);

        $subject = new RecordBasedContentGridProvider();
        $subject->setGrid($grid);
        $subject->injectContentTypes($contentTypes);

        self::assertSame($grid, $subject->getGrid([]));
    }
}
