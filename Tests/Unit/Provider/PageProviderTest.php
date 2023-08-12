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
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyPageProvider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Fluid\View\TemplatePaths;

class PageProviderTest extends AbstractTestCase
{
    protected FluxService $fluxService;
    protected WorkspacesAwareRecordService $recordService;
    protected PageService $pageService;
    protected CacheService $cacheService;
    protected string $configurationProviderClassName = PageProvider::class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fluxService = $this->getMockBuilder(FluxService::class)
            ->onlyMethods(
                [
                    'getSettingsForExtensionName',
                    'convertFlexFormContentToArray',
                    'resolvePrimaryConfigurationProvider',
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
        $this->pageService = $this->getMockBuilder(PageService::class)
            ->onlyMethods(['getPageTemplateConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getConstructorArguments(): array
    {
        return [
            $this->fluxService,
            $this->recordService,
            $this->getMockBuilder(ViewBuilder::class)->disableOriginalConstructor()->getMock(),
            $this->cacheService,
            $this->pageService,
        ];
    }

    public function testGetExtensionKey(): void
    {
        /** @var PageProvider|MockObject $instance */
        $instance = $this->getMockBuilder(PageProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getControllerExtensionKeyFromRecord'])
            ->getMock();
        $instance->expects($this->once())->method('getControllerExtensionKeyFromRecord')->willReturn('flux');
        $result = $instance->getExtensionKey([]);
        $this->assertEquals('flux', $result);
    }

    public function testGetExtensionKeyWithoutSelection(): void
    {
        /** @var PageProvider|MockObject $instance */
        $instance = $this->getMockBuilder(PageProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getControllerExtensionKeyFromRecord'])
            ->getMock();
        $instance->expects($this->once())->method('getControllerExtensionKeyFromRecord')->willReturn('');
        $result = $instance->getExtensionKey([]);
        $this->assertEquals('FluidTYPO3.Flux', $result);
    }

    public function testGetTemplatePathAndFilename(): void
    {
        $expected = 'Tests/Fixtures/Templates/Page/Dummy.html';
        $fieldName = 'tx_fed_page_controller_action';
        $dataFieldName = 'tx_fed_page_flexform';

        $pathsConfiguration = ['templateRootPaths' => ['Tests/Fixtures/Templates/']];
        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->onlyMethods(['resolveTemplateFileForControllerAndActionAndFormat'])
            ->getMock();
        $templatePaths->method('resolveTemplateFileForControllerAndActionAndFormat')
            ->willReturn('Tests/Fixtures/Templates/Page/Dummy.html');
        $instance = $this->getMockBuilder(PageProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['createTemplatePaths'])
            ->getMock();
        $instance->method('createTemplatePaths')->willReturn($templatePaths);
        $record = [
            $fieldName => 'Flux->dummy',
        ];
        $this->pageService->expects($this->any())->method('getPageTemplateConfiguration')->willReturn($record);
        $instance->trigger($record, null, $dataFieldName);
        $result = $instance->getTemplatePathAndFilename($record);
        $this->assertStringEndsWith($expected, $result);
    }

    public function testGetControllerExtensionKeyFromRecordReturnsPresetKeyOnUnrecognisedAction(): void
    {
        /** @var PageProvider|MockObject $instance */
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getControllerActionReferenceFromRecord'])
            ->getMock();
        $instance->expects($this->once())->method('getControllerActionReferenceFromRecord')->willReturn('invalid');
        $instance->setExtensionKey('fallback');
        $result = $instance->getControllerExtensionKeyFromRecord([]);
        $this->assertEquals('fallback', $result);
    }

    /**
     * @dataProvider getInheritanceTreeTestValues
     */
    public function testGetInheritanceTree(array $input, array $expected): void
    {
        $record = ['uid' => 1];
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['loadRecordTreeFromDatabase'])
            ->getMock();
        $instance->method('loadRecordTreeFromDatabase')->with($record)->willReturn($input);
        $result = $this->callInaccessibleMethod($instance, 'getInheritanceTree', $record);
        $this->assertEquals($expected, $result);
    }

    public function getInheritanceTreeTestValues(): array
    {
        return [
            'empty tree returns empty' => [[], []],
            'no sub action returns full tree' => [
                [[PageProvider::FIELD_ACTION_MAIN => 'testmain']],
                [[PageProvider::FIELD_ACTION_MAIN => 'testmain']]
            ],
            'defined sub action halts reading' => [
                [[PageProvider::FIELD_ACTION_MAIN => ''], [PageProvider::FIELD_ACTION_SUB => 'testsub'], [PageProvider::FIELD_ACTION_SUB => 'notincluded']],
                [[PageProvider::FIELD_ACTION_MAIN => ''], [PageProvider::FIELD_ACTION_SUB => 'testsub']],
            ],
        ];
    }

    /**
     * @dataProvider getControllerActionFromRecordTestValues
     */
    public function testGetControllerActionFromRecord(array $record, string $fieldName, string $expected): void
    {
        $className = $this->createInstanceClassName();
        $instance = new $className(...$this->getConstructorArguments());

        $this->pageService->method('getPageTemplateConfiguration')->willReturn($record);

        $result = $instance->getControllerActionFromRecord($record, $fieldName);
        $this->assertEquals($expected, $result);
    }

    public function getControllerActionFromRecordTestValues(): array
    {
        return [
            'defaults to flux->default when no actions whatsoever are defined' => [
                ['tx_fed_page_controller_action' => ''],
                'tx_fed_page_flexform',
                'default'
            ],
            'When $forField is tx_fed_page_flexform, returns the main configured action if record defines it' => [
                ['tx_fed_page_controller_action' => 'flux->action'],
                'tx_fed_page_flexform',
                'action'
            ],
            'When $forField is tx_fed_page_flexform_sub, returns the sub configured action if record defines it' => [
                ['tx_fed_page_controller_action_sub' => 'flux->action'],
                'tx_fed_page_flexform_sub',
                'action'
            ],
        ];
    }

    public function testGetFlexFormValuesReturnsCollectedDataWhenEncounteringNullForm(): void
    {
        $tree = [
            $this->getBasicRecord(),
            $this->getBasicRecord()
        ];
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField('Input', 'foo');
        $record = $this->getBasicRecord();
        $dummyProvider1 = new DummyPageProvider(...$this->getConstructorArguments());
        $dummyProvider2 = new DummyPageProvider(...$this->getConstructorArguments());
        $dummyProvider1->setForm($form);
        $dummyProvider1->setFlexFormValues(['foo' => 'bar']);
        /** @var PageProvider|MockObject $provider */
        $provider = $this->getMockBuilder(PageProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getInheritanceTree', 'unsetInheritedValues', 'getForm'])
            ->getMock();

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturnOnConsecutiveCalls(
            $dummyProvider1,
            $dummyProvider2
        );

        $provider->method('getInheritanceTree')->will($this->returnValue($tree));
        $provider->method('unsetInheritedValues');
        $provider->method('getForm')->willReturn($this->getMockBuilder(Form::class)->getMock());
        $provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
        $values = $provider->getFlexformValues($record);
        $this->assertEquals($values, []);
    }

    /**
     * @test
     */
    public function canGetFlexformValuesUnderInheritanceConditions(): void
    {
        $tree = [
            $this->getBasicRecord(),
            $this->getBasicRecord()
        ];
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField('Input', 'foo');
        $record = $this->getBasicRecord();
        // use a new uid to prevent caching issues
        $record['uid'] = $record['uid'] + 1;
        $dummyProvider1 = new DummyPageProvider(...$this->getConstructorArguments());
        $dummyProvider2 = new DummyPageProvider(...$this->getConstructorArguments());
        $dummyProvider1->setForm($form);
        $dummyProvider1->setFlexFormValues(['foo' => 'bar']);
        $form2 = $this->getMockBuilder(Form::class)->getMock();
        $dummyProvider2->setForm($form2);
        /** @var PageProvider|MockObject $provider */
        $provider = $this->getMockBuilder(PageProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getInheritanceTree', 'unsetInheritedValues', 'getForm'])
            ->getMock();

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturnOnConsecutiveCalls(
            $dummyProvider1,
            $dummyProvider2
        );
        $this->fluxService->method('convertFlexFormContentToArray')->willReturn([]);

        $provider->method('getInheritanceTree')->will($this->returnValue($tree));
        $provider->method('unsetInheritedValues');
        $provider->method('getForm')->willReturn($this->getMockBuilder(Form::class)->getMock());
        $provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
        $values = $provider->getFlexformValues($record);
        $this->assertEquals($values, []);
    }

    /**
     * @test
     * @dataProvider getRemoveInheritedTestValues
     * @param mixed $testValue
     */
    public function removesInheritedValuesFromFields($testValue, bool $inherit, bool $inheritEmpty, bool $expectsOverride): void
    {
        $instance = $this->createInstance();
        $field = Form\Field\Input::create(['type' => 'Input']);
        $field->setName('test');
        $field->setInherit($inherit);
        $field->setInheritEmpty($inheritEmpty);
        $values = ['foo' => 'bar', 'test' => $testValue];
        $result = $this->callInaccessibleMethod($instance, 'unsetInheritedValues', $field, $values);
        if ($expectsOverride) {
            $this->assertEquals($values, $result);
        } else {
            $this->assertEquals(['foo' => 'bar'], $result);
        }
    }

    public function getRemoveInheritedTestValues(): array
    {
        return [
            ['test', true, true, true],
            ['', true, false, true],
            ['', true, true, false],
        ];
    }

    /**
     * @test
     */
    public function getParentFieldValueLoadsRecordFromDatabaseIfRecordLacksParentFieldValue(): void
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $row['uid'] = 2;
        $rowWithPid = $row;
        $rowWithPid['pid'] = 1;
        $className = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));

        $this->recordService->expects($this->exactly(1))->method('getSingle')->will($this->returnValue($rowWithPid));

        $instance = $this->getMockBuilder($className)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getParentFieldName', 'getTableName'])
            ->getMock();
        $instance->expects($this->once())->method('getParentFieldName')->with($row)->will($this->returnValue('pid'));

        $result = $this->callInaccessibleMethod($instance, 'getParentFieldValue', $row);
        $this->assertEquals($rowWithPid['pid'], $result);
    }

    /**
     * @dataProvider getInheritedPropertyValueByDottedPathTestValues
     * @param mixed $expected
     */
    public function testGetInheritedPropertyValueByDottedPath(array $input, string $path, $expected): void
    {
        $provider = $this->getMockBuilder(PageProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getInheritedConfiguration'])
            ->getMock();
        $result = $this->callInaccessibleMethod($provider, 'getInheritedPropertyValueByDottedPath', $input, $path);
        $this->assertEquals($expected, $result);
    }

    public function getInheritedPropertyValueByDottedPathTestValues(): array
    {
        return [
            [[], '', null],
            [['foo' => 'bar'], 'foo', 'bar'],
            [['foo' => 'bar'], 'bar', null],
            [['foo' => ['bar' => 'baz']], 'foo.bar', 'baz'],
            [['foo' => ['bar' => 'baz']], 'foo.foo', null],
        ];
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
    public function canPostProcessRecord(): void
    {
        /** @var PageProvider|MockObject $provider */
        $provider = $this->getMockBuilder(PageProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getForm', 'getInheritedPropertyValueByDottedPath', 'loadRecordTreeFromDatabase'])
            ->getMock();
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField('Input', 'settings.input')->setInherit(true);
        $record = $this->getBasicRecord();
        $fieldName = PageProvider::FIELD_NAME_MAIN;
        $tableName = $provider->getTableName($record);
        $record[PageProvider::FIELD_NAME_MAIN] = Xml::EXPECTING_FLUX_REMOVALS;
        $id = $record['uid'];
        /** @var DataHandler $parentInstance */
        $parentInstance = $this->getMockBuilder(DataHandler::class)->disableOriginalConstructor()->getMock();
        $parentInstance->datamap[$tableName][$id] = [
            'uid' => $record['uid'],
            PageProvider::FIELD_NAME_MAIN => [
                'data' => [
                    'options' => [
                        'lDEF' => [
                            'settings.input' => [
                                'vDEF' => 'test',
                            ],
                            'settings.input_clear' => [
                                'vDEF' => 1,
                            ],
                            'foobar' => [
                                'vDEF' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            PageProvider::FIELD_NAME_SUB => ['data' => []],
        ];
        $provider->method('getForm')->willReturn($form);
        $provider->method('getInheritedPropertyValueByDottedPath')->with([], 'settings.input')->willReturn('test');
        $provider->method('loadRecordTreeFromDatabase')->willReturn([]);

        $storedRecord = $parentInstance->datamap[$tableName][$id];
        $storedRecord[PageProvider::FIELD_NAME_SUB] = '';
        $storedRecord[PageProvider::FIELD_NAME_MAIN] = <<< DATA
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<T3FlexForms>
    <data>
        <sheet index="options">
            <language index="lDEF">
                <field index="settings.input">
                    <value index="vDEF">1</value>
                </field>
                <field index="foobar">
                    <value index="vDEF">12</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>

DATA;

        $expectedUpdateRecord = $storedRecord;
        $storedRecord[PageProvider::FIELD_NAME_SUB] = '';
        $expectedUpdateRecord[PageProvider::FIELD_NAME_MAIN] = <<< DATA
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<T3FlexForms>
    <data>
        <sheet index="options">
            <language index="lDEF">
                
                <field index="foobar">
                    <value index="vDEF">12</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>

DATA;

        $this->recordService->expects($this->atLeastOnce())->method('getSingle')->willReturn($storedRecord);
        $this->recordService->expects($this->once())->method('update')->with('pages', $expectedUpdateRecord);

        $provider->postProcessRecord('update', $id, $record, $parentInstance);
    }

    public function testLoadRecordTreeFromDatabaseReturnsEmptyArrayIfRecordIsEmpty(): void
    {
        $subject = new PageProvider(...$this->getConstructorArguments());
        self::assertSame([], $this->callInaccessibleMethod($subject, 'loadRecordTreeFromDatabase', []));
    }

    public function testLoadRecordTreeFromDatabaseUsesRootLineUtility(): void
    {
        $rootLineUtility = $this->getMockBuilder(RootlineUtility::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $rootLineUtility->method('get')->willReturn(
            [
                ['uid' => 123],
                ['uid' => 456],
            ]
        );
        $subject = new PageProvider(...$this->getConstructorArguments());

        GeneralUtility::addInstance(RootlineUtility::class, $rootLineUtility);

        self::assertSame([['uid' => 456]], $this->callInaccessibleMethod($subject, 'loadRecordTreeFromDatabase', ['uid' => 1]));
    }

    public function testGetFormReturnsNullIfRecordIsDeleted(): void
    {
        $subject = new PageProvider(...$this->getConstructorArguments());
        self::assertNull($subject->getForm(['deleted' => 1]));
    }

    public function testGetControllerActionFromRecordReturnsDefaultIfActionIsEmpty(): void
    {
        $subject = $this->getMockBuilder(PageProvider::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['getControllerActionReferenceFromRecord'])
            ->getMock();
        $subject->method('getControllerActionReferenceFromRecord')->willReturn('');
        self::assertSame('default', $subject->getControllerActionFromRecord(['uid' => 123]));
    }
}
