<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Integration\ViewBuilder;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

abstract class AbstractProviderTest extends AbstractTestCase
{
    protected FluxService $fluxService;
    protected WorkspacesAwareRecordService $recordService;
    protected string $configurationProviderClassName = 'FluidTYPO3\Flux\Provider\Provider';
    private array $dummyGridConfiguration = [
        'columns' => [
            [
                'column' => [
                    'name' => 'column1',
                    'label' => 'Label 1',
                    'colPos' => 1,
                ],
            ],
            [
                'column' => [
                    'name' => 'column2',
                    'label' => 'Label 2',
                    'colPos' => 2,
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->fluxService = $this->getMockBuilder(FluxService::class)
            ->setMethods(
                [
                    'getFromCaches',
                    'setInCaches',
                    'getSettingsForExtensionName',
                    'convertFlexFormContentToArray',
                    'message'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle', 'update'])
            ->getMock();

        $this->singletonInstances[FluxService::class] = $this->fluxService;
        $this->singletonInstances[WorkspacesAwareRecordService::class] = $this->recordService;

        parent::setUp();
    }

    protected function getConfigurationProviderInstance(): ProviderInterface
    {
        $potentialClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));

        /** @var ProviderInterface $instance */
        if (true === class_exists($potentialClassName)) {
            $instance = new $potentialClassName();
        } else {
            $className = $this->configurationProviderClassName;
            $instance = new $className();
        }
        $instance = new $potentialClassName();
        $instance->setControllerName('Content');
        return $instance;
    }

    protected function getBasicRecord(): array
    {
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $record['pi_flexform'] = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
        return $record;
    }

    /**
     * @test
     */
    public function prunesEmptyFieldNodesOnRecordSave()
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $row['pi_flexform'] = Xml::EXPECTING_FLUX_PRUNING;

        $this->recordService->expects($this->once())->method('getSingle')->willReturn($row);
        $this->recordService->expects($this->once())->method('update');

        $provider = $this->getConfigurationProviderInstance();
        $provider->setFieldName('pi_flexform');
        $provider->setTableName('tt_content');

        $tceMain = $this->getMockBuilder(DataHandler::class)->disableOriginalConstructor()->getMock();
        $tceMain->datamap['tt_content'][$row['uid']]['pi_flexform']['data'] = array();
        $provider->postProcessRecord('update', $row['uid'], $row, $tceMain);
        $this->assertStringNotContainsString('<field index=""></field>', $row['pi_flexform']);
    }

    /**
     * @test
     */
    public function canGetAndSetListType()
    {
        $record = Records::$contentRecordIsParentAndHasChildren;
        /** @var ProviderInterface $instance */
        $instance = $this->getConfigurationProviderInstance();
        $instance->setExtensionKey('flux');
        $instance->setListType('test');
        $this->assertSame('test', $instance->getListType($record));
    }

    /**
     * @test
     */
    public function canGetAndSetContentObjectType()
    {
        /** @var ProviderInterface $instance */
        $instance = $this->getConfigurationProviderInstance();
        $instance->setContentObjectType('test');
        $this->assertSame('test', $instance->getContentObjectType());
    }

    /**
     * @test
     */
    public function canGetParentFieldName()
    {
        $record = Records::$contentRecordIsParentAndHasChildren;
        /** @var ProviderInterface $instance */
        $instance = $this->getConfigurationProviderInstance();
        $this->setInaccessiblePropertyValue($instance, 'parentFieldName', 'test');
        $this->assertSame('test', $instance->getParentFieldName($record));
    }

    /**
     * @test
     */
    public function canGetForm()
    {
        $provider = $this->getMockBuilder($this->createInstanceClassName())->setMethods(['extractConfiguration'])->getMock();
        $provider->expects($this->once())->method('extractConfiguration')->willReturn($this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock());
        $record = $this->getBasicRecord();
        $form = $provider->getForm($record);
        $this->assertInstanceOf(Form::class, $form);
    }

    /**
     * @test
     */
    public function canGetGrid()
    {
        $provider = $this->getMockBuilder($this->createInstanceClassName())->setMethods(['extractConfiguration', 'getForm'])->getMock();
        $provider->expects($this->once())->method('getForm')->willReturn(null);
        $provider->expects($this->once())->method('extractConfiguration')->willReturn(['grids' => ['grid' => Grid::create()]]);
        $record = $this->getBasicRecord();
        $grid = $provider->getGrid($record);
        $this->assertInstanceOf(Grid::class, $grid);
    }

    /**
     * @test
     */
    public function canGetForcedTemplateVariables()
    {
        $provider = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getPageValues'))->getMock();
        $record = $this->getBasicRecord();
        $variables = $provider->getTemplateVariables($record);
        $this->assertIsArray($variables);
    }

    /**
     * @test
     */
    public function canGetFlexformValues()
    {
        $record = $this->getBasicRecord();
        $provider = $this->getMockBuilder(str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4)))->setMethods(array('getForm'))->getMock();

        $this->fluxService->expects($this->once())->method('convertFlexFormContentToArray')->will($this->returnValue(array('test' => 'test')));

        $provider->expects($this->once())->method('getForm')->will($this->returnValue($this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock()));
        $provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
        $values = $provider->getFlexformValues($record);
        $this->assertIsArray($values);
        $this->assertEquals($values, array('test' => 'test'));
    }

    /**
     * @test
     */
    public function canGetTemplateVariables()
    {
        $provider = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getPageValues', 'getGrid', 'getForm'))->getMock();
        $provider->expects($this->once())->method('getPageValues')->willReturn([]);
        $provider->expects($this->once())->method('getForm')->willReturn(null);
        $provider->expects($this->once())->method('getGrid')->willReturn(Grid::create());
        $this->setInaccessiblePropertyValue($provider, 'templatePaths', array());
        $provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
        $record = $this->getBasicRecord();
        $values = $provider->getTemplateVariables($record);
        $this->assertIsArray($values);
    }

    /**
     * @test
     */
    public function canGetConfigurationSection()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $section = $provider->getConfigurationSectionName($record);
        $this->assertIsString($section);
    }

    /**
     * BASIC STUB: override this in your own test class if your
     * Provider is expected to return an extension key.
     *
     * @test
     */
    public function canGetExtensionKey()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $extensionKey = $provider->getExtensionKey($record);
        $this->assertSame('FluidTYPO3.Flux', $extensionKey);
    }

    /**
     * BASIC STUB: override this in your own test class if your
     * Provider is expected to return an extension key.
     *
     * @test
     */
    public function canGetTableName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $tableName = $provider->getTableName($record);
        $this->assertNull($tableName);
    }

    /**
     * @test
     */
    public function canGetControllerExtensionKey()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $result = $provider->getControllerExtensionKeyFromRecord($record);
        $this->assertSame('FluidTYPO3.Flux', $result);
    }

    /**
     * @test
     */
    public function canGetControllerActionName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $result = $provider->getControllerActionFromRecord($record);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function canGetControllerActionReferenceName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $result = $provider->getControllerActionReferenceFromRecord($record);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function canGetPriority()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $priority = $provider->getPriority($record);
        $this->assertIsInteger($priority);
    }

    /**
     * @test
     */
    public function canGetFieldName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $result = $provider->getFieldName($record);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canGetTemplateFilePathAndFilename()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $templatePathAndFilename = $provider->getTemplatePathAndFilename($record);
        $this->assertEmpty($templatePathAndFilename);
    }

    /**
     * @test
     */
    public function canPostProcessDataStructure()
    {
        $provider = $this->getMockBuilder($this->createInstanceClassName())->setMethods(['extractConfiguration'])->getMock();
        $provider->expects($this->once())->method('extractConfiguration')->willReturn($this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock());
        $record = $this->getBasicRecord();
        $dataStructure = array();
        $config = array();
        $result = $provider->postProcessDataStructure($record, $dataStructure, $config);
        $this->assertNull($result);
        $this->assertIsArray($config);
    }

    /**
     * @test
     */
    public function canPostProcessDataStructureWithManualFormInstance()
    {
        $provider = $this->getConfigurationProviderInstance();
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField(Form\Field\Input::class, 'dummy');
        $record = $this->getBasicRecord();
        $dataStructure = array();
        $config = array();
        $provider->setForm($form);
        $provider->postProcessDataStructure($record, $dataStructure, $config);
        $this->assertIsArray($dataStructure);
        $this->assertNotEquals(array(), $dataStructure);
        $this->assertNotEmpty($dataStructure);
    }

    /**
     * @test
     */
    public function canPostProcessRecord()
    {
        $record = $this->getBasicRecord();
        $record['test'] = 'test';
        $provider = $this->getConfigurationProviderInstance();

        $this->recordService->expects($this->once())->method('getSingle')->willReturn($record);
        $this->recordService->expects($this->once())->method('update');

        $parentInstance = $this->getMockBuilder(DataHandler::class)->disableOriginalConstructor()->getMock();
        $id = $record['uid'];
        $tableName = $provider->getTableName($record);
        if (true === empty($tableName)) {
            $tableName = 'tt_content';
            $provider->setTableName($tableName);
        }
        $fieldName = $provider->getFieldName($record);
        if (true === empty($fieldName)) {
            $fieldName = 'pi_flexform';
            $provider->setFieldName($fieldName);
        }
        $record[$fieldName] = array(
            'data' => array(
                'options' => array(
                    'lDEF' => array(
                        'settings.input' => array(
                            'vDEF' => 'test'
                        ),
                        'settings.input_clear' => array(
                            'vDEF' => 1
                        )
                    )
                )
            )
        );
        $parentInstance->datamap[$tableName][$id] = $record;
        $record[$fieldName] = Xml::EXPECTING_FLUX_REMOVALS;
        $provider->postProcessRecord('update', $id, $record, $parentInstance);
        $this->assertIsString($record[$fieldName]);
        $this->assertStringNotContainsString('settings.input', $record[$fieldName]);
    }

    /**
     * @test
     */
    public function canPostProcessRecordWithNullFieldName()
    {
        $provider = $this->getConfigurationProviderInstance();

        $this->recordService->expects($this->any())->method('getSingle')->willReturn(['uid' => 123]);

        $record = $this->getBasicRecord();
        $parentInstance = $this->getMockBuilder(DataHandler::class)->disableOriginalConstructor()->getMock();
        $record['test'] = 'test';
        $id = $record['uid'];
        $tableName = $provider->getTableName($record);
        if (true === empty($tableName)) {
            $tableName = 'tt_content';
            $provider->setTableName($tableName);
        }
        $fieldName = null;
        $provider->setFieldName(null);
        $parentInstance->datamap[$tableName][$id] = $record;
        $result = $provider->postProcessRecord('update', $id, $record, $parentInstance);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canPreProcessRecordAndTransferDataToRecordValues()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $parentInstance = $this->getMockBuilder(DataHandler::class)->disableOriginalConstructor()->getMock();
        $tableName = $provider->getTableName($record);
        if (empty($tableName)) {
            $tableName = 'tt_content';
            $provider->setTableName($tableName);
        }
        $fieldName = $provider->getFieldName($record);
        if (empty($fieldName)) {
            $fieldName = 'pi_flexform';
            $provider->setFieldName($fieldName);
        }
        $GLOBALS['TCA'][$tableName]['columns']['header'] = true;
        $record['header'] = 'old';
        $record[$fieldName] = array(
            'data' => array(
                'options' => array(
                    'lDEF' => array(
                        $tableName . '.header' => array(
                            'vDEF' => 'overridden-header'
                        )
                    )
                )
            )
        );
        $id = $record['uid'];
        $provider->preProcessRecord($record, $id, $parentInstance);
        $this->assertSame($record['header'], $record[$fieldName]['data']['options']['lDEF'][$tableName . '.header']['vDEF']);
    }

    /**
     * @test
     */
    public function canSetForm()
    {
        $form = Form::create(array('name' => 'test'));
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $provider = $this->getConfigurationProviderInstance();
        $provider->setForm($form);
        $this->assertSame($form, $provider->getForm($record));
    }
    /**
     * @test
     */
    public function canSetGrid()
    {
        $grid = Grid::create(array('name' => 'test'));
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $provider = $this->getConfigurationProviderInstance();
        $provider->setGrid($grid);
        $provider->setForm($this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock());
        $this->assertSame($grid, $provider->getGrid($record));
    }

    /**
     * @test
     */
    public function canSetTableName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $provider->setTableName('test');
        $this->assertSame('test', $provider->getTableName($record));
    }

    /**
     * @test
     */
    public function canSetFieldName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $provider->setFieldName('test');
        $this->assertSame('test', $provider->getFieldName($record));
    }

    /**
     * @test
     */
    public function canSetExtensionKey()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $provider->setExtensionKey('test');
        $this->assertSame('test', $provider->getExtensionKey($record));
    }

    /**
     * @test
     */
    public function canSetExtensionKeyAndPassToFormThroughLoadSettings()
    {
        $provider = $this->getConfigurationProviderInstance();
        $settings = array(
            'extensionKey' => 'my_ext',
            'form' => array(
                'name' => 'test'
            )
        );
        $provider->loadSettings($settings);
        $record = Records::$contentRecordIsParentAndHasChildren;
        $this->assertSame('my_ext', $provider->getExtensionKey($record));
        $this->assertSame('MyExt', $provider->getForm($record)->getExtensionName());
    }

    /**
     * @test
     */
    public function canSetTemplateVariables()
    {
        $provider = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getPageValues'))->getMock();
        $provider->expects($this->once())->method('getPageValues')->willReturn([]);
        $record = $this->getBasicRecord();
        $variables = array('test' => 'test');
        $provider->setTemplateVariables($variables);
        $this->assertArrayHasKey('test', $provider->getTemplateVariables($record));
    }

    /**
     * @test
     */
    public function canSetTemplatePathAndFilename()
    {
        $templatePaths = $this->getMockBuilder(TemplatePaths::class)->disableOriginalConstructor()->getMock();
        $provider = $this->getMockBuilder($this->createInstanceClassName())->setMethods(['createTemplatePaths', 'resolveAbsolutePathToFile'])->getMock();
        $provider->method('createTemplatePaths')->willReturn($templatePaths);
        $provider->method('resolveAbsolutePathToFile')->willReturnArgument(0);

        $record = $this->getBasicRecord();

        $template = 'test.html';
        $provider->setTemplatePathAndFilename($template);
        $this->assertStringContainsString($template, $provider->getTemplatePathAndFilename($record));

        $template = null;
        $provider->setTemplatePathAndFilename($template);
        $this->assertSame($template, $provider->getTemplatePathAndFilename($record));

        $template = 'test.html';
        $provider->setTemplatePathAndFilename($template);
        $this->assertStringEndsWith(
            'test.html',
            $provider->getTemplatePathAndFilename($record),
            'EXT relative paths are transformed'
        );

        $template = '/foo/Resources/Private/Foo/Bar.html';
        $provider->setTemplatePathAndFilename($template);
        $this->assertSame(
            $template,
            $provider->getTemplatePathAndFilename($record),
            'Absolute paths are not transformed'
        );
    }

    /**
     * @test
     */
    public function canUseAbsoluteTemplatePathDirectly()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $template = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
        $provider->setTemplatePathAndFilename($template);
        $this->assertSame($provider->getTemplatePathAndFilename($record), $template);
    }

    /**
     * @test
     */
    public function canSetConfigurationSectionName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $section = 'Custom';
        $provider->setConfigurationSectionName($section);
        $this->assertSame($section, $provider->getConfigurationSectionName($record));
    }

    public function testGetGridCanDetectContentContainerInParentWithModeRows(): void
    {
        $form = Form::create();
        $section = $form->createContainer(Form\Container\Section::class, 'columns');
        $section->setGridMode(Section::GRID_MODE_ROWS);
        $object = $section->createContainer(Form\Container\SectionObject::class, 'column');
        $object->setContentContainer(true);

        $subject = $this->getMockBuilder(AbstractProvider::class)
            ->setMethods(['getForm', 'getFlexFormValues'])
            ->getMock();
        $subject->method('getForm')->willReturn($form);
        $subject->method('getFlexFormValues')->willReturn($this->dummyGridConfiguration);

        $output = $subject->getGrid([]);
        self::assertInstanceOf(Grid::class, $output);
        self::assertCount(2, $output->getRows());
    }

    public function testGetGridCanDetectContentContainerInParentWithModeColumns(): void
    {
        $form = Form::create();
        $section = $form->createContainer(Form\Container\Section::class, 'columns');
        $section->setGridMode(Section::GRID_MODE_COLUMNS);
        $object = $section->createContainer(Form\Container\SectionObject::class, 'column');
        $object->setContentContainer(true);

        $subject = $this->getMockBuilder(AbstractProvider::class)
            ->setMethods(['getForm', 'getFlexFormValues'])
            ->getMock();
        $subject->method('getForm')->willReturn($form);
        $subject->method('getFlexFormValues')->willReturn($this->dummyGridConfiguration);

        $output = $subject->getGrid([]);
        self::assertInstanceOf(Grid::class, $output);
        self::assertCount(2, $output->getRows()[0]->getColumns());
    }

    public function testGetCacheKeyForStoredVariable(): void
    {
        $instance = $subject = $this->getMockBuilder(AbstractProvider::class)->getMockForAbstractClass();
        $output = $this->callInaccessibleMethod($instance, 'getCacheKeyForStoredVariable', ['uid' => 123], 'test');
        self::assertSame('flux-storedvariable---123-FluidTYPO3.Flux-default-test', $output);
    }

    public function testGetPreview(): void
    {
        $view = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['getPreview'])
            ->disableOriginalConstructor()
            ->getMock();
        $view->method('getPreview')->willReturn('preview');
        $viewBuilder = $this->getMockBuilder(ViewBuilder::class)
            ->setMethods(['buildPreviewView'])
            ->disableOriginalConstructor()
            ->getMock();
        $viewBuilder->method('buildPreviewView')->willReturn($view);

        GeneralUtility::addInstance(ViewBuilder::class, $viewBuilder);

        $instance = $subject = $this->getMockBuilder(AbstractProvider::class)->getMockForAbstractClass();
        $output = $instance->getPreview(['uid' => 123]);
        self::assertSame([null, 'preview', false], $output);
    }

    public function testProcessTableConfigurationReturnsUntouchedConfiguration(): void
    {
        $configuration = ['foo' => 'bar'];
        $instance = $subject = $this->getMockBuilder(AbstractProvider::class)
            ->setMethods(['dummy'])
            ->getMockForAbstractClass();
        self::assertSame($configuration, $instance->processTableConfiguration(['uid' => 123], $configuration));
    }

    public function testGetViewForRecord(): void
    {
        $this->prepareTemplateViewMock();

        $instance = $subject = $this->getMockBuilder(AbstractProvider::class)
            ->setMethods(['getEnvironmentVariable'])
            ->getMockForAbstractClass();
        $output = $instance->getViewForRecord(['uid' => 123]);
        self::assertInstanceOf(TemplateView::class, $output);
    }

    public function testExtractConfiguration(): void
    {
        $form = Form::create();

        $view = $this->prepareTemplateViewMock();
        $view->getRenderingContext()->getViewHelperVariableContainer()->add(
            FormViewHelper::class,
            FormViewHelper::SCOPE_VARIABLE_FORM,
            $form
        );

        $this->fluxService->method('getSettingsForExtensionName')->willReturn([]);

        $instance = $subject = $this->getMockBuilder(AbstractProvider::class)
            ->setMethods(['dispatchFlashMessageForException', 'getEnvironmentVariable'])
            ->getMockForAbstractClass();

        $output = $this->callInaccessibleMethod(
            $instance,
            'extractConfiguration',
            ['uid' => 123],
            FormViewHelper::SCOPE_VARIABLE_FORM
        );

        self::assertSame($form, $output);
    }

    public function testExtractConfigurationCachesStaticForms(): void
    {
        $form = Form::create();
        $form->setOption(Form::OPTION_STATIC, true);

        $view = $this->prepareTemplateViewMock();
        $view->getRenderingContext()->getViewHelperVariableContainer()->add(
            FormViewHelper::class,
            FormViewHelper::SCOPE_VARIABLE_FORM,
            $form
        );

        $this->fluxService->method('getSettingsForExtensionName')->willReturn([]);
        $this->fluxService->expects(self::once())->method('setInCaches');

        $instance = $subject = $this->getMockBuilder(AbstractProvider::class)
            ->setMethods(['dispatchFlashMessageForException', 'getEnvironmentVariable'])
            ->getMockForAbstractClass();

        $output = $this->callInaccessibleMethod(
            $instance,
            'extractConfiguration',
            ['uid' => 123],
            FormViewHelper::SCOPE_VARIABLE_FORM
        );

        self::assertSame($form, $output);
    }

    public function testExtractConfigurationWithoutConfigurationSectionName(): void
    {
        $view = $this->prepareTemplateViewMock();

        $this->fluxService->method('getSettingsForExtensionName')->willReturn([]);

        $instance = $subject = $this->getMockBuilder(AbstractProvider::class)
            ->setMethods(['getConfigurationSectionName', 'getEnvironmentVariable'])
            ->getMockForAbstractClass();
        $instance->method('getConfigurationSectionName')->willReturn(null);

        $output = $this->callInaccessibleMethod($instance, 'extractConfiguration', ['uid' => 123], 'test');

        self::assertSame(null, $output);
    }

    public function testExtractConfigurationReturnsValueFromCache(): void
    {
        $this->fluxService->method('getFromCaches')->willReturn(['test' => 'foo']);

        $instance = $subject = $this->getMockBuilder(AbstractProvider::class)
            ->setMethods(['dispatchFlashMessageForException'])
            ->getMockForAbstractClass();

        $output = $this->callInaccessibleMethod($instance, 'extractConfiguration', ['uid' => 123], 'test');

        self::assertSame('foo', $output);
    }

    public function testExtractConfigurationReturnsNullOnInvalidTemplateResource(): void
    {
        $view = $this->prepareTemplateViewMock();
        $view->method('renderSection')->willThrowException(new InvalidTemplateResourceException(''));

        $this->fluxService->method('getSettingsForExtensionName')->willReturn([]);

        $instance = $subject = $this->getMockBuilder(AbstractProvider::class)
            ->setMethods(['dispatchFlashMessageForException', 'getEnvironmentVariable'])
            ->getMockForAbstractClass();

        $output = $this->callInaccessibleMethod($instance, 'extractConfiguration', ['uid' => 123], 'test');

        self::assertSame(null, $output);
    }

    private function prepareTemplateViewMock(): TemplateView
    {
        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->setMethods(['fillDefaultsByPackageName'])
            ->disableOriginalConstructor()
            ->getMock();
        $controllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();
        $renderingContext = $this->getMockBuilder(RenderingContext::class)->disableOriginalConstructor()->getMock();
        $renderingContext->method('getTemplatePaths')->willReturn($templatePaths);
        $renderingContext->method('getViewHelperVariableContainer')->willReturn(new ViewHelperVariableContainer());

        $templateView = $this->getMockBuilder(TemplateView::class)
            ->setMethods(['render', 'renderSection', 'assignMultiple', 'getRenderingContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $templateView->method('getRenderingContext')->willReturn($renderingContext);

        $previewView = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['render', 'renderSection', 'assignMultiple', 'getRenderingContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $templateView->method('getRenderingContext')->willReturn($renderingContext);

        $viewBuilder = $this->getMockBuilder(ViewBuilder::class)
            ->setMethods(['buildTemplateView', 'buildPreviewView'])
            ->disableOriginalConstructor()
            ->getMock();
        $viewBuilder->method('buildTemplateView')->willReturn($templateView);
        $viewBuilder->method('buildPreviewView')->willReturn($previewView);

        GeneralUtility::addInstance(
            Request::class,
            $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock()
        );
        GeneralUtility::addInstance(
            UriBuilder::class,
            $this->getMockBuilder(UriBuilder::class)->disableOriginalConstructor()->getMock()
        );
        GeneralUtility::addInstance(RenderingContext::class, $renderingContext);
        GeneralUtility::addInstance(ControllerContext::class, $controllerContext);
        GeneralUtility::addInstance(ViewBuilder::class, $viewBuilder);

        return $templateView;
    }
}
