<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content\RecordBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\RuntimeDefinedContentProvider;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\DropInContentTypeDefinition;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class RecordBasedContentTypeDefinitionTest extends AbstractTestCase
{
    public function testConstructingInstanceFillsExpectedProperties(): void
    {
        $record = [
            'content_type' => 'test',
            'extension_identity' => 'flux',
            'icon' => 'icon-reference',
            'template_file' => '',
        ];
        $subject = new RecordBasedContentTypeDefinition($record);

        self::assertSame('flux', $subject->getExtensionIdentity(), 'Extension identity is unexpected value');
        self::assertSame($record['icon'], $subject->getIconReference(), 'Icon reference is unexpected value');
        self::assertFalse($subject->isUsingTemplateFile(), 'Definition incorrectly says it uses template file');
        self::assertTrue(
            $subject->isUsingGeneratedTemplateSource(),
            'Definition incorrectly says it does not use generated template source'
        );
        self::assertSame(
            RuntimeDefinedContentProvider::class,
            $subject->getProviderClassName(),
            'Provider class name is unexpected value'
        );
        //self::assertSame('', $subject->getTemplateSource(), 'Generated template source is unexpected value');
    }

    public function testGetGridGeneratesGridWithAutoColumnsEnabledAndGridModeRows(): void
    {
        $gridConfiguration = [
            [
                'grid' => [
                    'lDEF' => [
                        'gridMode' => [
                            'vDEF' => Section::GRID_MODE_ROWS,
                        ],
                        'autoColumns' => [
                            'vDEF' => 2,
                        ],
                    ],
                ],
            ],
        ];

        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getGridConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getGridConfiguration')->willReturn($gridConfiguration);

        $output = $subject->getGrid([]);
        self::assertCount(2, $output->getRows());
    }

    public function testGetGridGeneratesGridWithAutoColumnsEnabledAndGridModeColumns(): void
    {
        $gridConfiguration = [
            [
                'grid' => [
                    'lDEF' => [
                        'gridMode' => [
                            'vDEF' => Section::GRID_MODE_COLUMNS,
                        ],
                        'autoColumns' => [
                            'vDEF' => 2,
                        ],
                    ],
                ],
            ],
        ];

        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getGridConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getGridConfiguration')->willReturn($gridConfiguration);

        $output = $subject->getGrid([]);
        self::assertCount(1, $output->getRows());
        self::assertCount(2, $output->getRows()[0]->getColumns());
    }

    public function testGetGridGeneratesGridWithManualColumnsEnabledAndGridModeRows(): void
    {
        $gridConfiguration = [
            [
                'grid' => [
                    'lDEF' => [
                        'gridMode' => [
                            'vDEF' => Section::GRID_MODE_ROWS,
                        ],
                        'autoColumns' => [
                            'vDEF' => 0,
                        ],
                        'columns' => [
                            [
                                [
                                    'column' => [
                                        'el' => [
                                            'name' => [
                                                'vDEF' => 'column1',
                                            ],
                                            'label' => [
                                                'vDEF' => 'Label 1',
                                            ],
                                            'colPos' => [
                                                'vDEF' => 1,
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'column' => [
                                        'el' => [
                                            'name' => [
                                                'vDEF' => 'column2',
                                            ],
                                            'label' => [
                                                'vDEF' => 'Label 2',
                                            ],
                                            'colPos' => [
                                                'vDEF' => 2,
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

        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getGridConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getGridConfiguration')->willReturn($gridConfiguration);

        $output = $subject->getGrid([]);
        self::assertCount(2, $output->getRows());
    }

    public function testGetGridGeneratesGridWithManualColumnsEnabledAndGridModeColumns(): void
    {
        $gridConfiguration = [
            [
                'grid' => [
                    'lDEF' => [
                        'gridMode' => [
                            'vDEF' => Section::GRID_MODE_COLUMNS,
                        ],
                        'autoColumns' => [
                            'vDEF' => 0,
                        ],
                        'columns' => [
                            [
                                [
                                    'column' => [
                                        'el' => [
                                            'name' => [
                                                'vDEF' => 'column1',
                                            ],
                                            'label' => [
                                                'vDEF' => 'Label 1',
                                            ],
                                            'colPos' => [
                                                'vDEF' => 1,
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'column' => [
                                        'el' => [
                                            'name' => [
                                                'vDEF' => 'column2',
                                            ],
                                            'label' => [
                                                'vDEF' => 'Label 2',
                                            ],
                                            'colPos' => [
                                                'vDEF' => 2,
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

        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getGridConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getGridConfiguration')->willReturn($gridConfiguration);

        $output = $subject->getGrid([]);
        self::assertCount(1, $output->getRows());
        self::assertCount(2, $output->getRows()[0]->getColumns());
    }
}
