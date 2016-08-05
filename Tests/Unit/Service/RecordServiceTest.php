<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * RecordServiceTest
 */
class RecordServiceTest extends AbstractTestCase
{

    /**
     * @var DatabaseConnection
     */
    private static $connectionMock;

    /**
     * @param array $methods
     * @return DatabaseConnection
     */
    protected function getMockDatabaseConnection(array $methods)
    {
        self::$connectionMock = $this->getMockBuilder($this->createInstanceClassName())->setMethods($methods)->getMock();
        return self::$connectionMock;
    }

    /**
     * @param array $methods
     * @param array $connectionMethods
     * @return RecordService
     */
    protected function getMockServiceInstance(array $methods, array $connectionMethods)
    {
        $methods[] = 'getDatabaseConnection';
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods($methods)->getMock();
        $connectionMock = $this->getMockDatabaseConnection($connectionMethods);
        $mock->expects($this->atLeastOnce())->method('getDatabaseConnection')->will($this->returnValue($connectionMock));
        return $mock;
    }

    /**
     * @test
     */
    public function getMethodCallsExpectedDatabaseMethod()
    {
        $table = 'test';
        $fields = 'a,b';
        $clause = '1=2';
        $groupBy = 'foo';
        $orderBy = 'bar';
        $limit = 60;
        $mock = $this->getMockServiceInstance(array(), array('exec_SELECTgetRows'));
        self::$connectionMock->expects($this->once())->method('exec_SELECTgetRows')->with($fields, $table, $clause, $groupBy, $orderBy, $limit);
        $mock->get($table, $fields, $clause, $groupBy, $orderBy, $limit);
    }

    /**
     * @test
     */
    public function getSingleMethodCallsExpectedDatabaseMethod()
    {
        $table = 'test';
        $fields = 'a,b';
        $uid = 123;
        $mock = $this->getMockServiceInstance(array(), array('exec_SELECTgetSingleRow'));
        self::$connectionMock->expects($this->once())->method('exec_SELECTgetSingleRow')->with($fields, $table, "uid = '" . $uid . "'");
        $mock->getSingle($table, $fields, $uid);
    }

    /**
     * @test
     */
    public function updateMethodCallsExpectedDatabaseMethod()
    {
        $table = 'test';
        $uid = 123;
        $fields = array('foo' => 'bar', 'uid' => $uid);
        $mock = $this->getMockServiceInstance(array(), array('exec_UPDATEquery'));
        self::$connectionMock->expects($this->once())->method('exec_UPDATEquery')->with($table, "uid = '" . $uid . "'", $fields);
        $mock->update($table, $fields);
    }

    /**
     * @test
     */
    public function deleteMethodCallsExpectedDatabaseMethodWithUid()
    {
        $table = 'test';
        $uid = 123;
        $mock = $this->getMockServiceInstance(array(), array('exec_DELETEquery'));
        self::$connectionMock->expects($this->once())->method('exec_DELETEquery')->with($table, "uid = '" . $uid . "'");
        $mock->delete($table, $uid);
    }

    /**
     * @test
     */
    public function deleteMethodCallsExpectedDatabaseMethodWithRecord()
    {
        $table = 'test';
        $uid = 123;
        $record = array('uid' => 123);
        $mock = $this->getMockServiceInstance(array(), array('exec_DELETEquery'));
        self::$connectionMock->expects($this->once())->method('exec_DELETEquery')->with($table, "uid = '" . $uid . "'");
        $mock->delete($table, $record);
    }

    /**
     * @test
     */
    public function returnsDatabaseConnection()
    {
        $instance = $this->createInstance();
        $this->assertSame($GLOBALS['TYPO3_DB'], $this->callInaccessibleMethod($instance, 'getDatabaseConnection'));
    }

    /**
     * @test
     */
    public function preparedGetCallsExpectedMethodSequence()
    {
        $query = $this->getMockBuilder(
            'TYPO3\\CMS\\Core\\Database\\PreparedStatement'
        )->setMethods(
            array('execute', 'fetchAll', 'free')
        )->disableOriginalConstructor()->getMock();
        $connection = $this->getMockDatabaseConnection(array('prepare_SELECTquery'));
        $connection->expects($this->once())->method('prepare_SELECTquery')->will($this->returnValue($query));
        $query->expects($this->once())->method('execute');
        $query->expects($this->once())->method('fetchAll')->will($this->returnValue(array()));
        $query->expects($this->once())->method('free');
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getDatabaseConnection'))->getMock();
        $mock->expects($this->once())->method('getDatabaseConnection')->will($this->returnValue($connection));
        $mock->preparedGet('table', '', '', array());
    }
}
