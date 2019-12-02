<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\BackendLayoutDataProvider;
use FluidTYPO3\Flux\Service\ConfigurationService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayoutCollection;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class BackendLayoutDataProviderTest
 */
class BackendLayoutDataProviderTest extends AbstractTestCase
{

    /**
     * @return void
     */
    public function testPerformsInjections()
    {
        $instance = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(BackendLayoutDataProvider::class);
        $this->assertAttributeInstanceOf(ObjectManager::class, 'objectManager', $instance);
        $this->assertAttributeInstanceOf(ConfigurationService::class, 'configurationService', $instance);
        $this->assertAttributeInstanceOf(WorkspacesAwareRecordService::class, 'recordService', $instance);
    }

    /**
     * @dataProvider getBackendLayoutConfigurationTestValues
     * @param Provider $provider
     * @param mixed $record
     * @param array $expected
     */
    public function testGetBackendLayoutConfiguration(Provider $provider, $record, array $expected)
    {
        $instance = new BackendLayoutDataProvider();
        $pageUid = 1;
        /** @var ConfigurationService|\PHPUnit_Framework_MockObject_MockObject $configurationService */
        $configurationService = $this->getMockBuilder(
            ConfigurationService::class
        )->setMethods(
            array('resolvePageProvider', 'debug', 'message')
        )->getMock();
        if (null !== $record) {
            $configurationService->expects($this->once())->method('resolvePageProvider')
                ->with($record)->willReturn($provider);
        }
        /** @var WorkspacesAwareRecordService|\PHPUnit_Framework_MockObject_MockObject $recordService */
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)->setMethods(array('getSingle'))->getMock();
        $recordService->expects($this->once())->method('getSingle')->willReturn($record);
        $instance->injectConfigurationService($configurationService);
        $instance->injectWorkspacesAwareRecordService($recordService);
        $result = $instance->getBackendLayout('flux__grid', $pageUid);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getBackendLayoutConfigurationTestValues()
    {
        $form = Form::create(array('id' => 'formId'));
        /** @var Provider|\PHPUnit_Framework_MockObject_MockObject $standardProvider */
        $standardProvider = $this->getMockBuilder(
            Provider::class
        )->setMethods(
            array('getControllerActionFromRecord', 'getForm')
        )->getMock();
        $standardProvider->expects($this->any())->method('getForm')->willReturn($form);
        $standardProvider->setTemplatePaths(array());
        $actionLessProvider = clone $standardProvider;
        $exceptionProvider = clone $standardProvider;
        $emptyGridProvider = clone $standardProvider;
        $gridProvider = clone $standardProvider;
        $actionLessProvider->expects($this->any())->method('getControllerActionFromRecord')->willReturn(null);
        $exceptionProvider->expects($this->any())->method('getControllerActionFromRecord')->willThrowException(new \RuntimeException());
        /** @var Grid $grid */
        $grid = Grid::create(array());
        $emptyGridProvider->setGrid($grid);
        $emptyGridProvider->expects($this->any())->method('getControllerActionFromRecord')->willReturn('default');
        /** @var Grid $grid */
        $grid = Grid::create(array());
        $grid->setParent($form);
        $grid->createContainer('Row', 'row')->createContainer('Column', 'column')->setColSpan(3)->setRowSpan(3)->setColumnPosition(2);
        $gridProvider->setGrid($grid);
        $gridProvider->expects($this->any())->method('getControllerActionFromRecord')->willReturn('default');
        $gridArray = array(
            'colCount' => 3,
            'rowCount' => 1,
            'rows.' => array(
                '1.' => array(
                    'columns.' => array(
                        '1.' => array(
                            'name' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.formId.columns.column',
                            'colPos' => 2,
                            'colspan' => 3,
                            'rowspan' => 3
                        )
                    )
                )
            )
        );
        return array(
            array($standardProvider, null, array()),
            array($standardProvider, array(), array()),
            array($actionLessProvider, array(), array()),
            array($emptyGridProvider, array(), array()),
            array($exceptionProvider, array(), array()),
            array($gridProvider, array(), $gridArray),
        );
    }

    /**
     * @dataProvider getEnsureDottedKeysTestValues
     * @param array $input
     * @param array $expected
     */
    public function testEnsureDottedKeys(array $input, array $expected)
    {
        $instance = new BackendLayoutDataProvider();
        $result = $this->callInaccessibleMethod($instance, 'ensureDottedKeys', $input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getEnsureDottedKeysTestValues()
    {
        return array(
            array(
                array('foo' => array('bar' => 'bar')),
                array('foo.' => array('bar' => 'bar'))
            ),
            array(
                array('foo.' => array('bar' => 'bar')),
                array('foo.' => array('bar' => 'bar'))
            )
        );
    }

    /**
     * @dataProvider getEncodeTypoScriptArrayTestValues
     * @param array $input
     * @param $expected
     */
    public function testEncodeTypoScriptArray(array $input, $expected)
    {
        $instance = new BackendLayoutDataProvider();
        $result = $this->callInaccessibleMethod($instance, 'encodeTypoScriptArray', $input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getEncodeTypoScriptArrayTestValues()
    {
        return array(
            array(
                array('foo' => array('bar' => 'bar')),
                'backend_layout.foo.bar = bar' . PHP_EOL
            ),
            array(
                array('foo.' => array('bar' => 'bar')),
                'backend_layout.foo.bar = bar' . PHP_EOL
            )
        );
    }

    /**
     * @return void
     */
    public function testGetBackendLayout()
    {
        /** @var BackendLayoutDataProvider|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this->getMockBuilder(
            BackendLayoutDataProvider::class
        )->setMethods(
            array('getBackendLayoutConfiguration', 'ensureDottedKeys', 'encodeTypoScriptArray')
        )->getMock();
        $instance->expects($this->at(0))->method('getBackendLayoutConfiguration')->with(1)->willReturn(array('conf'));
        $instance->expects($this->at(1))->method('ensureDottedKeys')->with(array('conf'))->willReturn(array('conf-converted'));
        $instance->expects($this->at(2))->method('encodeTypoScriptArray')->with(array('conf-converted'))->willReturn('config');
        $result = $instance->getBackendLayout('grid', 1);
        $this->assertInstanceOf(BackendLayout::class, $result);
        $this->assertEquals('grid', $result->getIdentifier());
        $this->assertEquals('config', $result->getConfiguration());
    }

    /**
     * @return void
     */
    public function testAddBackendLayouts()
    {
        /** @var BackendLayoutDataProvider|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this->getMockBuilder(
            BackendLayoutDataProvider::class
        )->setMethods(
            array('getBackendLayoutConfiguration', 'encodeTypoScriptArray')
        )->getMock();
        $instance->expects($this->once())->method('getBackendLayoutConfiguration')->with(1)->willReturn(array('conf'));
        $instance->expects($this->once())->method('encodeTypoScriptArray')->with(array('conf'))->willReturn('conf');
        $collection = new BackendLayoutCollection('collection');
        $context = new DataProviderContext();
        $context->setPageId(1);
        $instance->addBackendLayouts($context, $collection);
        $this->assertEquals('conf', reset($collection->getAll())->getConfiguration());
    }
}
