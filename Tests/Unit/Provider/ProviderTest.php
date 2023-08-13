<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class ProviderTest extends AbstractTestCase
{
    protected FormDataTransformer $formDataTransformer;
    protected WorkspacesAwareRecordService $recordService;
    protected ViewBuilder $viewBuilder;
    protected CacheService $cacheService;
    protected TypoScriptService $typoScriptService;
    protected array $definition = [
        'name' => 'test',
        'label' => 'Test provider',
        'tableName' => 'tt_content',
        'fieldName' => 'pi_flexform',
        'form' => [
            'sheets' => [
                'foo' => [
                    'fields' => [
                        'test' => [
                            'type' => Input::class,
                        ]
                    ]
                ],
                'bar' => [
                    'fields' => [
                        'test2' => [
                            'type' => Input::class,
                        ]
                    ]
                ],
            ],
            'fields' => [
                'test3' => [
                    'type' => Input::class,
                ]
            ],
        ],
        'grid' => [
            'rows' => [
                'foo' => [
                    'columns' => [
                        'bar' => [
                            'areas' => [

                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

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

    /**
     * @test
     */
    public function canGetName()
    {
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->loadSettings($this->definition);
        $this->assertSame($provider->getName(), $this->definition['name']);
    }

    /**
     * @test
     */
    public function canCreateInstanceWithListType()
    {
        $definition = $this->definition;
        $definition['listType'] = 'felogin_pi1';
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->loadSettings($definition);
        $this->assertSame($provider->getName(), $definition['name']);
        $this->assertSame($provider->getListType(), $definition['listType']);
    }

    /**
     * @test
     */
    public function canReturnExtensionKey()
    {
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->setExtensionKey('test');
        $resolver = $this->getMockBuilder(ProviderResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolvePrimaryConfigurationProvider'])
            ->getMock();
        $resolver->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $result = $resolver->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', [], 'flux');
        $this->assertSame($provider, $result);
        $extensionKey = $result->getExtensionKey($record);
        $this->assertNotEmpty($extensionKey);
        $this->assertMatchesRegularExpression('/[a-z_]+/', $extensionKey);
    }

    /**
     * @test
     */
    public function canCreateFormFromDefinitionWithAllSupportedNodes()
    {
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->loadSettings($this->definition);
        $form = $provider->getForm([]);
        $this->assertInstanceOf(Form::class, $form);
    }

    /**
     * @test
     */
    public function canCreateGridFromDefinitionWithAllSupportedNodes()
    {
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->loadSettings($this->definition);
        $grid = $provider->getGrid([]);
        $this->assertInstanceOf(Grid::class, $grid);
    }

    /**
     * @test
     */
    public function canSetTableName()
    {
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->setTableName('test');
        $this->assertSame('test', $provider->getTableName([]));
    }

    /**
     * @test
     */
    public function canSetFieldName()
    {
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->setFieldName('test');
        $this->assertSame('test', $provider->getFieldName([]));
    }

    /**
     * @test
     */
    public function canSetExtensionKey()
    {
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->setExtensionKey('test');
        $this->assertSame('test', $provider->getExtensionKey([]));
    }

    /**
     * @test
     */
    public function canSetPluginName()
    {
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->setPluginName('test');
        $this->assertSame('test', $provider->getPluginName());
    }

    /**
     * @test
     */
    public function canSetControllerAction()
    {
        $provider = new Provider(...$this->getConstructorArguments());
        $provider->setControllerAction('test');
        $this->assertSame('test', $provider->getControllerActionFromRecord([]));
    }

    public function testProcessTableConfigurationReturnsUntouchedConfiguration(): void
    {
        $configuration = ['recordTypeValue' => '1'];
        $instance = $this->getMockBuilder(AbstractProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getForm'])
            ->getMockForAbstractClass();
        $instance->method('getForm')->willReturn(Form::create());
        self::assertSame($configuration, $instance->processTableConfiguration(['uid' => 123], $configuration));
    }

    public function testProcessTableConfigurationAddsNativeFields(): void
    {
        $configuration = [
            'recordTypeValue' => 'foo',
            'processedTca' => [
                'types' => [
                    'foo' => [
                        'showitem' => '',
                    ]
                ],
                'columns' => [],
            ],
            'columnsToProcess' => [],
            'databaseRow' => [],
        ];
        $expected = $configuration;
        $expected['processedTca']['columns']['native'] = [
            'label' => 'My Native Field',
            'exclude' => 0,
            'config' => [
                'type' => 'input',
                'size' => 32,
                'eval' => 'trim',
            ],
        ];
        $expected['columnsToProcess'] = ['native'];
        $expected['processedTca']['types']['foo']['showitem'] = '--div--;My Special Sheet, native';
        $expected['databaseRow'] = ['native' => null];

        $form = Form::create();
        $field = $form->createField('input', 'native', 'My Native Field');
        $field->setNative(true);
        $field->setPosition('before:header My Special Sheet');

        $instance = $this->getMockBuilder(AbstractProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getForm', 'getTableName'])
            ->getMockForAbstractClass();
        $instance->method('getForm')->willReturn($form);
        $instance->method('getTableName')->willReturn('table');

        $GLOBALS['TCA']['table']['types']['foo']['showitem'] = '';

        self::assertSame($expected, $instance->processTableConfiguration(['uid' => 123], $configuration));
    }

    public function testProcessTableConfigurationRemovesNativeFields(): void
    {
        $configuration = [
            'recordTypeValue' => '1',
            'processedTca' => [
                'columns' => [
                    'native' => [
                        'label' => 'My Native Field',
                        'exclude' => 0,
                        'config' => [
                            'type' => 'input',
                            'size' => 32,
                            'eval' => 'trim',
                        ],
                    ],
                ],
            ],
        ];
        $expected = [
            'recordTypeValue' => '1',
            'processedTca' => [
                'columns' => [],
            ],
        ];

        $form = Form::create();
        $form->setOption(Form::OPTION_HIDE_NATIVE_FIELDS, 'native');

        $instance = $this->getMockBuilder(AbstractProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getForm', 'getTableName'])
            ->getMockForAbstractClass();
        $instance->method('getForm')->willReturn($form);
        $instance->method('getTableName')->willReturn('table');

        self::assertSame($expected, $instance->processTableConfiguration(['uid' => 123], $configuration));
    }
}
