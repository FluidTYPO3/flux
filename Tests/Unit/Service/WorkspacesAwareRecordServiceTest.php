<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;

/**
 * WorkspacesAwareRecordServiceTest
 */
class WorkspacesAwareRecordServiceTest extends RecordServiceTest
{

    /**
     * @test
     */
    public function overlayRecordsCallsExpectedMethodSequence()
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('hasWorkspacesSupport', 'overlayRecord'))->getMock();
        $mock->expects($this->once())->method('hasWorkspacesSupport')->will($this->returnValue(true));
        $mock->expects($this->exactly(2))->method('overlayRecord')->will($this->returnValue(array('foo')));
        $records = array(array(), array());
        $expected = array(array('foo'), array('foo'));
        $result = $this->callInaccessibleMethod($mock, 'overlayRecords', 'table', $records);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getWorkspaceVersionOfRecordOrRecordItselfReturnsSelf()
    {
        $GLOBALS['BE_USER'] = new \stdClass();
        $instance = new WorkspacesAwareRecordService();
        $result = $this->callInaccessibleMethod($instance, 'getWorkspaceVersionOfRecordOrRecordItself', 'void', array('uid' => 1));
        $this->assertEquals(array('uid' => 1), $result);
        unset($GLOBALS['BE_USER']);
    }
}
