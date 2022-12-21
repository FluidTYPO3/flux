<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\SubPageProvider;
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

    protected function setUp(): void
    {
        $this->fluxService = $this->getMockBuilder(FluxService::class)
            ->setMethods(
                [
                    'getFromCaches',
                    'setInCaches',
                    'getSettingsForExtensionName',
                    'convertFlexFormContentToArray',
                    'message',
                    'resolvePrimaryConfigurationProvider',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle', 'update'])
            ->getMock();

        $this->pageService = $this->getMockBuilder(PageService::class)
            ->setMethods(['getPageTemplateConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->singletonInstances[FluxService::class] = $this->fluxService;
        $this->singletonInstances[WorkspacesAwareRecordService::class] = $this->recordService;
        $this->singletonInstances[PageService::class] = $this->pageService;

        parent::setUp();
    }

    public function testGetExtensionKey()
    {
        /** @var PageProvider|MockObject $instance */
        $instance = $this->getMockBuilder(PageProvider::class)->setMethods(array('getControllerExtensionKeyFromRecord'))->getMock();
        $instance->expects($this->once())->method('getControllerExtensionKeyFromRecord')->willReturn('flux');
        $result = $instance->getExtensionKey(array());
        $this->assertEquals('flux', $result);
    }

    public function testGetExtensionKeyWithoutSelection()
    {
        /** @var PageProvider|MockObject $instance */
        $instance = $this->getMockBuilder(PageProvider::class)->setMethods(array('getControllerExtensionKeyFromRecord'))->getMock();
        $instance->expects($this->once())->method('getControllerExtensionKeyFromRecord')->willReturn('');
        $result = $instance->getExtensionKey(array());
        $this->assertEquals('FluidTYPO3.Flux', $result);
    }

    public function testGetTemplatePathAndFilename()
    {
        $expected = 'Tests/Fixtures/Templates/Page/Dummy.html';
        $fieldName = 'tx_fed_page_controller_action';
        $dataFieldName = 'tx_fed_page_flexform';

        $pathsConfiguration = ['templateRootPaths' => ['Tests/Fixtures/Templates/']];
        $templatePaths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['resolveTemplateFileForControllerAndActionAndFormat'])->getMock();
        $templatePaths->method('resolveTemplateFileForControllerAndActionAndFormat')->willReturn('Tests/Fixtures/Templates/Page/Dummy.html');
        $instance = $this->getMockBuilder(SubPageProvider::class)->setMethods(['createTemplatePaths'])->getMock();
        $instance->method('createTemplatePaths')->willReturn($templatePaths);
        $record = array(
            $fieldName => 'Flux->dummy',
        );
        $this->pageService->expects($this->any())->method('getPageTemplateConfiguration')->willReturn($record);
        $instance->trigger($record, null, $dataFieldName);
        $result = $instance->getTemplatePathAndFilename($record);
        $this->assertStringEndsWith($expected, $result);
    }

    public function testGetFormCallsSetDefaultValuesInFieldsWithInheritedValues()
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        /** @var PageProvider|MockObject $instance */
        $instance = $this->getMockBuilder(PageProvider::class)->setMethods(array('setDefaultValuesInFieldsWithInheritedValues'))->getMock();

        $instance->expects($this->once())->method('setDefaultValuesInFieldsWithInheritedValues')->willReturn($form);
        $instance->setForm($form);
        $instance->getForm(array());
    }

    public function testGetControllerExtensionKeyFromRecordReturnsPresetKeyOnUnrecognisedAction()
    {
        /** @var PageProvider|MockObject $instance */
        $instance = $this->getMockBuilder(PageProvider::class)->setMethods(array('getControllerActionReferenceFromRecord'))->getMock();
        $instance->expects($this->once())->method('getControllerActionReferenceFromRecord')->willReturn('invalid');
        $instance->setExtensionKey('fallback');
        $result = $instance->getControllerExtensionKeyFromRecord(array());
        $this->assertEquals('fallback', $result);
    }

    /**
     * @dataProvider getInheritanceTreeTestValues
     * @param array $input
     * @param array $expected
     */
    public function testGetInheritanceTree(array $input, array $expected)
    {
        $record = array('uid' => 1);
        $instance = $this->getMockBuilder(PageProvider::class)->setMethods(array('loadRecordTreeFromDatabase'))->getMock();
        $instance->expects($this->once())->method('loadRecordTreeFromDatabase')->with($record)->willReturn($input);
        $result = $this->callInaccessibleMethod($instance, 'getInheritanceTree', $record);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getInheritanceTreeTestValues()
    {
        return array(
            array(array(), array()),
            array(
                array(array(PageProvider::FIELD_ACTION_SUB => 'testsub'), array(PageProvider::FIELD_ACTION_MAIN => 'testmain')),
                array(array(PageProvider::FIELD_ACTION_MAIN => 'testmain'))
            ),
            array(
                array(array(PageProvider::FIELD_ACTION_SUB => 'testsub'), array(PageProvider::FIELD_ACTION_MAIN => '')),
                array(array(PageProvider::FIELD_ACTION_SUB => 'testsub'), array(PageProvider::FIELD_ACTION_MAIN => ''))
            ),
        );
    }

    /**
     * @dataProvider getControllerActionFromRecordTestValues
     * @param array $record
     * @param string $fieldName
     * @param string $expected
     */
    public function testGetControllerActionFromRecord(array $record, $fieldName, $expected)
    {
        $instance = new PageProvider();

        // make sure PageProvider is now using the right field name
        $instance->trigger($record, null, $fieldName);
        $result = $instance->getControllerActionFromRecord($record);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getControllerActionFromRecordTestValues()
    {
        return array(
            array(array('tx_fed_page_controller_action' => ''), 'tx_fed_page_flexform', 'default'),
            array(array('tx_fed_page_controller_action' => 'flux->action'), 'tx_fed_page_flexform', 'action'),
        );
    }

    public function testGetFlexFormValuesReturnsCollectedDataWhenEncounteringNullForm()
    {
        $tree = array(
            $this->getBasicRecord(),
            $this->getBasicRecord()
        );
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField('Input', 'foo');
        $record = $this->getBasicRecord();
        $dummyProvider1 = new DummyPageProvider();
        $dummyProvider2 = new DummyPageProvider();
        $dummyProvider1->setForm($form);
        $dummyProvider1->setFlexFormValues(array('foo' => 'bar'));
        /** @var PageProvider|MockObject $provider */
        $provider = $this->getMockBuilder(PageProvider::class)->setMethods(array('getInheritanceTree', 'unsetInheritedValues', 'getForm'))->getMock();

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturnOnConsecutiveCalls(
            $dummyProvider1,
            $dummyProvider2
        );

        $provider->expects($this->once())->method('getInheritanceTree')->will($this->returnValue($tree));
        $provider->expects($this->any())->method('unsetInheritedValues');
        $provider->expects($this->any())->method('getForm')->willReturn($this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock());
        $provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
        $values = $provider->getFlexformValues($record);
        $this->assertEquals($values, array());
    }

    /**
     * @test
     */
    public function canGetFlexformValuesUnderInheritanceConditions()
    {
        $tree = array(
            $this->getBasicRecord(),
            $this->getBasicRecord()
        );
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField('Input', 'foo');
        $record = $this->getBasicRecord();
        // use a new uid to prevent caching issues
        $record['uid'] = $record['uid'] + 1;
        $dummyProvider1 = new DummyPageProvider();
        $dummyProvider2 = new DummyPageProvider();
        $dummyProvider1->setForm($form);
        $dummyProvider1->setFlexFormValues(array('foo' => 'bar'));
        $form2 = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $dummyProvider2->setForm($form2);
        /** @var PageProvider|MockObject $provider */
        $provider = $this->getMockBuilder(PageProvider::class)->setMethods(array('getInheritanceTree', 'unsetInheritedValues', 'getForm'))->getMock();

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturnOnConsecutiveCalls(
            $dummyProvider1,
            $dummyProvider2
        );
        $this->fluxService->method('convertFlexFormContentToArray')->willReturn([]);

        $provider->expects($this->once())->method('getInheritanceTree')->will($this->returnValue($tree));
        $provider->expects($this->any())->method('unsetInheritedValues');
        $provider->expects($this->any())->method('getForm')->willReturn($this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock());
        $provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
        $values = $provider->getFlexformValues($record);
        $this->assertEquals($values, array());
    }

    /**
     * @test
     */
    public function setsDefaultValueInFieldsBasedOnInheritedValue()
    {
        $row = array();
        $className = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($className)->setMethods(array('getInheritedPropertyValueByDottedPath', 'getInheritedConfiguration'))->getMock();
        $instance->expects($this->once())->method('getInheritedPropertyValueByDottedPath')
            ->with(array(), 'input')->will($this->returnValue('default'));
        $instance->expects($this->once())->method('getInheritedConfiguration')
            ->with($row)->will($this->returnValue(array()));
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $field = $form->createField('Input', 'input');
        $returnedForm = $this->callInaccessibleMethod($instance, 'setDefaultValuesInFieldsWithInheritedValues', $form, $row);
        $this->assertSame($form, $returnedForm);
        $this->assertEquals('default', $field->getDefault());
    }

    /**
     * @test
     * @dataProvider getRemoveInheritedTestValues
     * @param mixed $testValue
     * @param boolean $inherit
     * @param boolean $inheritEmpty
     * @param boolean $expectsOverride
     */
    public function removesInheritedValuesFromFields($testValue, $inherit, $inheritEmpty, $expectsOverride)
    {
        $instance = $this->createInstance();
        $field = Form\Field\Input::create(array('type' => 'Input'));
        $field->setName('test');
        $field->setInherit($inherit);
        $field->setInheritEmpty($inheritEmpty);
        $values = array('foo' => 'bar', 'test' => $testValue);
        $result = $this->callInaccessibleMethod($instance, 'unsetInheritedValues', $field, $values);
        if (true === $expectsOverride) {
            $this->assertEquals($values, $result);
        } else {
            $this->assertEquals(array('foo' => 'bar'), $result);
        }
    }

    /**
     * @return array
     */
    public function getRemoveInheritedTestValues()
    {
        return array(
            array('test', true, true, true),
            array('', true, false, true),
            array('', true, true, false),
        );
    }

    /**
     * @test
     */
    public function getParentFieldValueLoadsRecordFromDatabaseIfRecordLacksParentFieldValue()
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $row['uid'] = 2;
        $rowWithPid = $row;
        $rowWithPid['pid'] = 1;
        $className = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));

        $this->recordService->expects($this->exactly(1))->method('getSingle')->will($this->returnValue($rowWithPid));

        $instance = $this->getMockBuilder($className)->setMethods(array('getParentFieldName', 'getTableName'))->getMock();
        $instance->expects($this->once())->method('getParentFieldName')->with($row)->will($this->returnValue('pid'));

        $result = $this->callInaccessibleMethod($instance, 'getParentFieldValue', $row);
        $this->assertEquals($rowWithPid['pid'], $result);
    }

    /**
     * @dataProvider getInheritedPropertyValueByDottedPathTestValues
     * @param array $input
     * @param string $path
     * @param mixed $expected
     */
    public function testGetInheritedPropertyValueByDottedPath(array $input, $path, $expected)
    {
        $provider = $this->getMockBuilder(PageProvider::class)->setMethods(array('getInheritedConfiguration'))->getMock();
        $result = $this->callInaccessibleMethod($provider, 'getInheritedPropertyValueByDottedPath', $input, $path);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getInheritedPropertyValueByDottedPathTestValues()
    {
        return array(
            array(array(), '', null),
            array(array('foo' => 'bar'), 'foo', 'bar'),
            array(array('foo' => 'bar'), 'bar', null),
            array(array('foo' => array('bar' => 'baz')), 'foo.bar', 'baz'),
            array(array('foo' => array('bar' => 'baz')), 'foo.foo', null),
        );
    }

    /**
     * @return array
     */
    protected function getBasicRecord()
    {
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $record['pi_flexform'] = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
        return $record;
    }

    /**
     * @test
     */
    public function canPostProcessRecord()
    {
        /** @var PageProvider|MockObject $provider */
        $provider = $this->getMockBuilder(PageProvider::class)->setMethods(array('getForm', 'getInheritedPropertyValueByDottedPath', 'loadRecordTreeFromDatabase'))->getMock();
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->createField('Input', 'settings.input')->setInherit(true);
        $record = $this->getBasicRecord();
        $fieldName = $provider->getFieldName($record);
        $tableName = $provider->getTableName($record);
        $record[$fieldName] = Xml::EXPECTING_FLUX_REMOVALS;
        $id = $record['uid'];
        /** @var DataHandler $parentInstance */
        $parentInstance = $this->getMockBuilder(DataHandler::class)->disableOriginalConstructor()->getMock();
        $parentInstance->datamap[$tableName][$id] = array(
            'uid' => $record['uid'],
            $fieldName => array(
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
            )
        );
        $provider->expects($this->any())->method('getForm')->willReturn($form);
        $provider->expects($this->once())->method('getInheritedPropertyValueByDottedPath')
            ->with([], 'settings.input')->willReturn('test');
        $provider->method('loadRecordTreeFromDatabase')->willReturn([]);

        $this->recordService->expects($this->atLeastOnce())->method('getSingle')->willReturn($parentInstance->datamap[$tableName][$id]);
        $this->recordService->expects($this->once())->method('update');

        $this->fluxService->expects($this->any())->method('message');

        $provider->postProcessRecord('update', $id, $record, $parentInstance);
        $this->assertIsString($record[$fieldName]);
        $this->assertStringNotContainsString('settings.input', $record[$fieldName]);
    }

    public function testLoadRecordTreeFromDatabaseReturnsEmptyArrayIfRecordIsEmpty(): void
    {
        $subject = new PageProvider();
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
        $subject = new PageProvider();

        GeneralUtility::addInstance(RootlineUtility::class, $rootLineUtility);

        self::assertSame([['uid' => 456]], $this->callInaccessibleMethod($subject, 'loadRecordTreeFromDatabase', ['uid' => 1]));
    }

    public function testGetFormReturnsNullIfRecordIsDeleted(): void
    {
        $subject = new PageProvider();
        self::assertNull($subject->getForm(['deleted' => 1]));
    }

    public function testGetControllerActionFromRecordReturnsDefaultIfActionIsEmpty(): void
    {
        $subject = $this->getMockBuilder(PageProvider::class)
            ->setMethods(['getControllerActionReferenceFromRecord'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getControllerActionReferenceFromRecord')->willReturn('');
        self::assertSame('default', $subject->getControllerActionFromRecord(['uid' => 123]));
    }
}
