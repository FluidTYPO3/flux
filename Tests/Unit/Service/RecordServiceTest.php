<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\Statement;
use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use Prophecy\Argument;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * RecordServiceTest
 */
class RecordServiceTest extends AbstractTestCase
{
    /**
     * @param array $methods
     * @return RecordService
     */
    protected function getMockServiceInstance(array $methods = [])
    {
        return $this->getMockBuilder($this->createInstanceClassName())->setMethods($methods)->getMock();
    }

    /**
     * @return QueryBuilder
     */
    protected function createAndRegisterMockForQueryBuilder()
    {
        $statement = $this->prophesize(Statement::class);
        $statement->fetchAll()->willReturn([]);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $queryBuilder->from(Argument::type('string'))->will(function ($arguments) use ($queryBuilder) { return $queryBuilder->reveal(); });
        $queryBuilder->where(Argument::type('string'))->will(function ($arguments) use ($queryBuilder) { return $queryBuilder->reveal(); });
        $queryBuilder->select(Argument::type('string'))->will(function ($arguments) use ($queryBuilder) { return $queryBuilder->reveal(); });
        $queryBuilder->orderBy('sorting', '');
        $queryBuilder->delete(Argument::type('string'));
        $queryBuilder->setMaxResults(Argument::type('int'));
        $queryBuilder->execute()->willReturn($statement->reveal());

        $prophecy = $this->prophesize(ConnectionPool::class);
        $prophecy->getQueryBuilderForTable(Argument::type('string'))->willReturn($queryBuilder->reveal());

        GeneralUtility::addInstance(ConnectionPool::class, $prophecy->reveal());

        return $queryBuilder;
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
        $mock = $this->getMockServiceInstance();

        $this->createAndRegisterMockForQueryBuilder();

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
        $mock = $this->getMockServiceInstance();

        $this->createAndRegisterMockForQueryBuilder();

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
        $mock = $this->getMockServiceInstance();

        $this->createAndRegisterMockForQueryBuilder();

        $mock->update($table, $fields);
    }

    /**
     * @test
     */
    public function deleteMethodCallsExpectedDatabaseMethodWithUid()
    {
        $table = 'test';
        $uid = 123;
        $mock = $this->getMockServiceInstance();

        $this->createAndRegisterMockForQueryBuilder();

        $mock->delete($table, $uid);
    }

    /**
     * @test
     */
    public function deleteMethodCallsExpectedDatabaseMethodWithRecord()
    {
        $table = 'test';
        $record = array('uid' => 123);
        $mock = $this->getMockServiceInstance();

        $this->createAndRegisterMockForQueryBuilder();

        $mock->delete($table, $record);
    }
}
