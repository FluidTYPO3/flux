<?php
namespace FluidTYPO3\Flux\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * BackendConfigurationManagerTest
 */
class BackendConfigurationManagerTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function canCreateInstance()
    {
        $instance = $this->createInstance();
        $this->assertInstanceOf($this->createInstanceClassName(), $instance);
    }

    /**
     * @test
     */
    public function supportsInjectors()
    {
        $instance = new BackendConfigurationManager();
        $recordService = new RecordService();
        $instance->injectRecordService($recordService);
        $this->assertAttributeSame($recordService, 'recordService', $instance);
    }

    /**
     * @test
     */
    public function canSetCurrentPageId()
    {
        $instance = new BackendConfigurationManager();
        $instance->setCurrentPageId(123);
        $this->assertAttributeEquals(123, 'currentPageId', $instance);
    }

    /**
     * @test
     */
    public function testGetPrioritizedPageUidsCallsExpectedMethodSequence()
    {
        $instance = $this->getMockBuilder(
            'FluidTYPO3\\Flux\\Configuration\\BackendConfigurationManager'
        )->setMethods(
            array(
                'getPageIdFromGet', 'getPageIdFromPost',
                'getPageIdFromRecordIdentifiedInEditUrlArgument',
                'getPageIdFromContentObject'
            )
        )->getMock();
        $instance->setCurrentPageId(1);
        $instance->expects($this->at(0))->method('getPageIdFromGet')->willReturn(0);
        $instance->expects($this->at(1))->method('getPageIdFromPost')->willReturn(0);
        $instance->expects($this->at(2))->method('getPageIdFromRecordIdentifiedInEditUrlArgument')->willReturn(0);
        $instance->expects($this->at(3))->method('getPageIdFromContentObject')->willReturn(0);
        $result = $this->callInaccessibleMethod($instance, 'getPrioritizedPageUids');
        $this->assertEquals(array(0, 0, 0, 0, 1), $result);
    }

    /**
     * @test
     */
    public function getPageIdFromContentObjectUsesGetFromRecordIfFilled()
    {
        $record = Records::$contentRecordWithParentAndWithoutChildren;
        $mockContentObject = new \stdClass();
        $mockContentObject->data = $record;
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getPageIdFromRecord', 'getContentObject'))->getMock();
        $mock->expects($this->at(0))->method('getContentObject')->will($this->returnValue($mockContentObject));
        $mock->expects($this->at(1))->method('getPageIdFromRecord')->with($record);
        $this->callInaccessibleMethod($mock, 'getPageIdFromContentObject');
    }

    /**
     * @test
     */
    public function getPageIdFromRecordReturnsPidProperty()
    {
        $record = Records::$contentRecordWithParentAndWithoutChildren;
        $record['pid'] = 123;
        $mock = $this->getMockBuilder($this->createInstanceClassName())->getMock();
        $result = $this->callInaccessibleMethod($mock, 'getPageIdFromRecord', $record);
        $this->assertEquals(123, $result);
    }

    /**
     * @test
     */
    public function getPageIdFromRecordReturnsZeroIfPropertyEmpty()
    {
        $record = Records::$contentRecordWithParentAndWithoutChildren;
        $record['pid'] = '';
        $mock = $this->getMockBuilder($this->createInstanceClassName())->getMock();
        $result = $this->callInaccessibleMethod($mock, 'getPageIdFromRecord', $record);
        $this->assertEquals(0, $result);
    }

    /**
     * @test
     */
    public function getPageIdFromGetReturnsExpectedValue()
    {
        $_GET['id'] = 123;
        $mock = $this->getMockBuilder($this->createInstanceClassName())->getMock();
        $result = $this->callInaccessibleMethod($mock, 'getPageIdFromGet');
        $this->assertEquals(123, $result);
        unset($_GET['id']);
    }

    /**
     * @test
     */
    public function getPageIdFromPostReturnsExpectedValue()
    {
        $_POST['id'] = 123;
        $mock = $this->getMockBuilder($this->createInstanceClassName())->getMock();
        $result = $this->callInaccessibleMethod($mock, 'getPageIdFromPost');
        $this->assertEquals(123, $result);
        unset($_POST['id']);
    }

    /**
     * @test
     */
    public function getCurrentPageIdReturnsProtectedPropertyOnlyIfSet()
    {
        $pageUid = 54642;
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getPrioritizedPageUids'))->getMock();
        $mock->expects($this->never())->method('getPrioritizedPageUids');
        $mock->setCurrentPageId($pageUid);
        $result = $mock->getCurrentPageId();
        $this->assertEquals($pageUid, $result);
    }

    /**
     * @test
     */
    public function getCurrentPageIdCallsGetPrioritizedPageUids()
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getPrioritizedPageUids'))->getMock();
        $mock->expects($this->once())->method('getPrioritizedPageUids')->willReturn(array(0, 0, 0, 0, 123));
        $result = $mock->getCurrentPageId();
        $this->assertEquals(123, $result);
    }

    /**
     * @test
     */
    public function getPageIdFromRecordUidDelegatesToRecordService()
    {
        $recordService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\RecordService')->setMethods(array('getSingle'))->getMock();
        $recordService->expects($this->once())->method('getSingle')
            ->with('table', 'pid', 123)->will($this->returnValue(array('foo' => 'bar')));
        $mock = $this->objectManager->get($this->createInstanceClassName());
        $mock->injectRecordService($recordService);
        $this->callInaccessibleMethod($mock, 'getPageIdFromRecordUid', 'table', 123);
    }

    /**
     * @dataProvider getEditArgumentsTestValues
     * @param array $argument
     * @param array $expected
     */
    public function testGetEditArguments(array $argument, array $expected)
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getEditArgumentValuePair'))->getMock();
        $mock->expects($this->once())->method('getEditArgumentValuePair')->willReturn($argument);
        $result = $this->callInaccessibleMethod($mock, 'getEditArguments');
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getEditArgumentsTestValues()
    {
        return array(
            array(array(array()), array(0, 0, 0)),
            array(array('tt_content' => array(1 => 'update')), array('tt_content', 1, 'update')),
            array(array('pages' => array(2 => 'delete')), array('pages', 2, 'delete'))
        );
    }

    /**
     * @test
     */
    public function getEditArgumentValuePairReturnsEmptyArray()
    {
        $instance = new BackendConfigurationManager();
        $result = $this->callInaccessibleMethod($instance, 'getEditArgumentValuePair');
        $this->assertEquals(array(array()), $result);
    }

    /**
     * @dataProvider getPageIdFromRecordIdentifiedInEditUrlArgumentTestValues
     * @param array $arguments
     * @param boolean $expectsRecordFetch
     */
    public function testGetPageIdFromRecordIdentifiedInEditUrlArgument(array $arguments, $expectsRecordFetch)
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getEditArguments', 'getPageIdFromRecordUid'))->getMock();
        $mock->expects($this->once())->method('getEditArguments')->willReturn($arguments);
        $mock->expects($this->exactly((integer) $expectsRecordFetch))->method('getPageIdFromRecordUid')
            ->with($arguments[0], abs($arguments[1]))->willReturn($arguments[1]);
        $result = $this->callInaccessibleMethod($mock, 'getPageIdFromRecordIdentifiedInEditUrlArgument');
        $this->assertEquals($arguments[1], $result);
    }

    /**
     * @return array
     */
    public function getPageIdFromRecordIdentifiedInEditUrlArgumentTestValues()
    {
        return array(
            array(array('tt_content', 1, 'update'), true),
            array(array('tt_content', -1, 'new'), false),
            array(array('pages', 0, 'update'), false),
            array(array('pages', 1, 'update'), false),
            array(array('pages', 1, 'delete'), false),
            array(array('pages', 1, 'new'), false),
            array(array('sys_file', 1, 'new'), true),
            array(array('sys_file', 1, 'edit'), true),
        );
    }
}
