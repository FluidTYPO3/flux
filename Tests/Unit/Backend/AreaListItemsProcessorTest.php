<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\AreaListItemsProcessor;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AreaListItemsProcessorTest
 */
class AreaListItemsProcessorTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function constructorSetsInternalAttributes()
    {
        $instance = new AreaListItemsProcessor();
        $this->assertAttributeInstanceOf('TYPO3\\CMS\\Extbase\\Object\\ObjectManager', 'objectManager', $instance);
        $this->assertAttributeInstanceOf('FluidTYPO3\\Flux\\Service\\FluxService', 'fluxService', $instance);
        $this->assertAttributeInstanceOf('FluidTYPO3\\Flux\\Service\\RecordService', 'recordService', $instance);
    }

    /**
     * @test
     * @dataProvider getItemsProcFuncTestValues
     * @param array $parameters
     * @param string $urlParent
     * @param string $urlArea
     * @param array $expectedReturnedItems
     * @param array $expectedItems
     */
    public function testItemsProcFunc($parameters, $urlParent, $urlArea, $expectedReturnedItems = null, $expectedItems)
    {
        $mock = $this->getMockBuilder(
            $this->createInstanceClassName()
        )->setMethods(
            array('readParentAndAreaNameFromUrl', 'getContentAreasDefinedInContentElement')
        )->disableOriginalConstructor()->getMock();
        $mock->expects($this->once())->method('readParentAndAreaNameFromUrl')
            ->will($this->returnValue(array($urlParent, $urlArea)));
        if (null !== $expectedReturnedItems) {
            $mock->expects($this->once())->method('getContentAreasDefinedInContentElement')
                ->will($this->returnValue($expectedReturnedItems));
        } else {
            $mock->expects($this->never())->method('getContentAreasDefinedInContentElement');
        }
        $mock->itemsProcFunc($parameters);
        $this->assertEquals($expectedItems, $parameters['items']);
    }

    /**
     * @return array
     */
    public function getItemsProcFuncTestValues()
    {
        return array(
            array(array(), null, null, null, array(array('', ''))),
            array(array(), 1, 'areaname', array(), array()),
            array(array(), 1, 'areaname', array(array('foobar', 'areaname')), array(1 => array('foobar', 'areaname'))),
        );
    }

    /**
     * @test
     */
    public function readParentAndAreaNameFromUrlReturnsArray()
    {
        $mock = new AreaListItemsProcessor();
        $result = $this->callInaccessibleMethod($mock, 'readParentAndAreaNameFromUrl');
        $this->assertCount(2, $result);
    }

    /**
     * @test
     */
    public function getContentAreasDefinedInContentElementCallsExpectedMethods()
    {
        $grid = $this->getMockBuilder('FluidTYPO3\\Flux\\Form\\Container\\Grid')->setMethods(array('build'))->getMock();
        $grid->expects($this->once())->method('build')->willReturn(array('rows' => array(array('columns' => array(array(
            'label' => 'column-label',
            'name' => 'column'
        ))))));
        $mock = $this->getMockBuilder('FluidTYPO3\\Flux\\Backend\\AreaListItemsProcessor')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $provider1 = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('getGrid'))->getMock();
        $provider1->expects($this->once())->method('getGrid')->willReturn(null);
        $provider2 = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('getGrid'))->getMock();
        $provider2->expects($this->once())->method('getGrid')->willReturn($grid);

        $providers = array($provider1, $provider2);
        $recordService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\RecordService')->setMethods(array('getSingle'))->getMock();
        $recordService->expects($this->once())->method('getSingle')->will($this->returnValue(array('foo' => 'bar')));
        $fluxService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('resolveConfigurationProviders'))->getMock();
        $fluxService->expects($this->once())->method('resolveConfigurationProviders')->willReturn($providers);
        ObjectAccess::setProperty($mock, 'fluxService', $fluxService, true);
        ObjectAccess::setProperty($mock, 'recordService', $recordService, true);
        $mock->getContentAreasDefinedInContentElement(1);

    }
}
