<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content\TypeDefinition\RecordBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\RuntimeDefinedContentProvider;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinitionRepository;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordBasedContentTypeDefinitionTest extends AbstractTestCase
{
    private array $dummyContentConfiguration = [
        [
            'sheets' => [
                'lDEF' => [
                    'sheets' => [
                        'el' => [
                            [
                                'sheet' => [
                                    'el' => [
                                        'name' => [
                                            'vDEF' => 'sheet',
                                        ],
                                        'label' => [
                                            'vDEF' => 'sheet label',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'sheet' => [
                'lDEF' => [
                    'fields' => [
                        'el' => [
                            [
                                'input' => [
                                    'el' => [
                                        'name' => [
                                            'vDEF' => 'field',
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

    private array $dummyGridConfiguration = [
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

    public function testReturnsContentTypeDefinitions(): void
    {
        $definitions = [
            $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
                ->disableOriginalConstructor()
                ->getMock()
        ];

        $repository = $this->getMockBuilder(RecordBasedContentTypeDefinitionRepository::class)
            ->setMethods(['fetchContentTypeDefinitions'])
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('fetchContentTypeDefinitions')->willReturn($definitions);

        $singletonInstances = GeneralUtility::getSingletonInstances();
        GeneralUtility::setSingletonInstance(RecordBasedContentTypeDefinitionRepository::class, $repository);

        self::assertSame($definitions, RecordBasedContentTypeDefinition::fetchContentTypes());

        GeneralUtility::resetSingletonInstances($singletonInstances);
    }

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
    }

    public function testGetGridReturnsInternalGridIfDefined(): void
    {
        $grid = Form\Container\Grid::create();

        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->setInaccessiblePropertyValue($subject, 'grid', $grid);

        self::assertSame($grid, $subject->getGrid());
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
        $gridConfiguration = $this->dummyGridConfiguration;

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
        $gridConfiguration = $this->dummyGridConfiguration;
        $gridConfiguration[0]['grid']['lDEF']['gridMode']['vDEF'] = Section::GRID_MODE_COLUMNS;

        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getGridConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getGridConfiguration')->willReturn($gridConfiguration);

        $output = $subject->getGrid([]);
        self::assertCount(1, $output->getRows());
        self::assertCount(2, $output->getRows()[0]->getColumns());
    }

    public function testGetTemplateSourceGeneratesSource(): void
    {
        $gridConfiguration = $this->dummyGridConfiguration;

        $record = [
            'content_type' => 'flux_test',
            'icon' => '',
            'extension_identity' => 'FluidTYPO3.Flux',
            'template_source' => '',
        ];

        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getGridConfiguration'])
            ->setConstructorArgs([$record])
            ->getMock();
        $subject->method('getGridConfiguration')->willReturn($gridConfiguration);
        $source = $subject->getTemplateSource();
        $expected = <<<SOURCE
<div class="flux-grid">
<div class="flux-grid-row">
<flux:content.render area="1" />
</div>
<div class="flux-grid-row">
<flux:content.render area="2" />
</div>
</div>

SOURCE;

        self::assertSame($expected, $source);
    }

    public function testGetTemplateSourceReturnsEmptyStringWithoutGrid(): void
    {
        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getGrid'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getGrid')->willReturn(null);
        $source = $subject->getTemplateSource();

        self::assertSame('', $source);
    }

    public function testGetTemplateSourceReturnsSourceFromRecord(): void
    {
        $record = [
            'content_type' => 'flux_test',
            'icon' => '',
            'extension_identity' => 'FluidTYPO3.Flux',
            'template_source' => 'foo',
        ];
        $subject = new RecordBasedContentTypeDefinition($record);
        $source = $subject->getTemplateSource();

        self::assertSame('foo', $source);
    }

    public function testGetTemplatePathAndFilenameReturnsFileFromRecord(): void
    {
        $record = [
            'content_type' => 'flux_test',
            'icon' => '',
            'extension_identity' => 'FluidTYPO3.Flux',
            'template_file' => realpath('Tests/Fixtures/Templates/Content/AbsolutelyMinimal.html'),
        ];
        $subject = new RecordBasedContentTypeDefinition($record);
        $file = $subject->getTemplatePathAndFilename();

        self::assertSame($record['template_file'], $file);
    }

    public function testGetTemplatePathAndFilenameReturnsProxyWithoutTemplateInRecord(): void
    {
        $package = $this->getMockBuilder(Package::class)
            ->setMethods(['getPackagePath'])
            ->disableOriginalConstructor()
            ->getMock();
        $package->method('getPackagePath')->willReturn('./');

        $packageManager = $this->getMockBuilder(PackageManager::class)
            ->setMethods(['getPackage', 'isPackageActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $packageManager->method('isPackageActive')->willReturn(true);
        $packageManager->method('getPackage')->willReturn($package);

        $record = [
            'content_type' => 'flux_test',
            'icon' => '',
            'extension_identity' => 'FluidTYPO3.Flux',
            'template_file' => '',
        ];
        $subject = new RecordBasedContentTypeDefinition($record);

        AccessibleExtensionManagementUtility::setPackageManager($packageManager);

        $file = $subject->getTemplatePathAndFilename();

        self::assertSame('./Resources/Private/Templates/Content/Proxy.html', $file);
    }

    public function testGetFormReturnsExpectedFormComposition(): void
    {
        $record = [
            'content_type' => 'flux_test',
            'icon' => '',
            'title' => 'Test',
            'description' => 'Test description',
            'extension_identity' => 'FluidTYPO3.Flux',
            'template_file' => '',
            'sorting' => 123,
        ];

        $contentConfiguration = $this->dummyContentConfiguration;

        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getContentConfiguration'])
            ->setConstructorArgs([$record])
            ->getMock();
        $subject->method('getContentConfiguration')->willReturn($contentConfiguration);

        $form = $subject->getForm();

        self::assertInstanceOf(Form::class, $form);
        self::assertInstanceOf(Form\Container\Sheet::class, $form->get('sheet'));
        self::assertCount(1, $form->get('sheet')->getFields());
    }

    public function testGetSheetNamesAndLabelsReturnsExpectedValue(): void
    {
        $contentConfiguration = $this->dummyContentConfiguration;

        $subject = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(['getContentConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getContentConfiguration')->willReturn($contentConfiguration);

        self::assertSame(
            [
                'sheet' => 'Sheet: sheet label',
            ],
            iterator_to_array($subject->getSheetNamesAndLabels())
        );
    }
}
