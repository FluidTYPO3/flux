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
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionContainerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * RecordServiceTest
 */
class RecordServiceTest extends AbstractTestCase
{
    protected ?Statement $statement = null;

    /**
     * @param array $methods
     * @return RecordService|MockObject
     */
    protected function getMockServiceInstance(array $methods = [])
    {
        if (empty($methods)) {
            $methods[] = 'dummy';
        }
        $methods[] = 'isBackendOrPreviewContext';
        return $this->getMockBuilder($this->createInstanceClassName())->setMethods($methods)->getMock();
    }

    /**
     * @return QueryBuilder
     */
    protected function createAndRegisterMockForQueryBuilder()
    {
        $this->statement = $this->getMockBuilder(Statement::class)
            ->setMethods(['fetchAll'])
            ->disableOriginalConstructor()
            ->getMock();

        $expressionBuilder = $this->getMockBuilder(ExpressionBuilder::class)->disableOriginalConstructor()->getMock();

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->setMethods(
                [
                    'from',
                    'where',
                    'select',
                    'update',
                    'orderBy',
                    'groupBy',
                    'delete',
                    'setMaxResults',
                    'setFirstResult',
                    'setParameters',
                    'execute',
                    'set',
                    'getRestrictions',
                    'expr',
                    'createNamedParameter',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('update')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('groupBy')->willReturnSelf();
        $queryBuilder->method('delete')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();
        $queryBuilder->method('setFirstResult')->willReturnSelf();
        $queryBuilder->method('setParameters')->willReturnSelf();
        $queryBuilder->method('execute')->willReturn($this->statement);
        $queryBuilder->method('expr')->willReturn($expressionBuilder);
        $queryBuilder->method('createNamedParameter')->willReturn('param');
        $queryBuilder->method('getRestrictions')->willReturn(
            $this->getMockBuilder(QueryRestrictionContainerInterface::class)->getMockForAbstractClass()
        );

        $prophecy = $this->getMockBuilder(ConnectionPool::class)
            ->setMethods(['getQueryBuilderForTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $prophecy->method('getQueryBuilderForTable')->willReturn($queryBuilder);

        GeneralUtility::addInstance(ConnectionPool::class, $prophecy);

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
        $offset = 10;
        $mock = $this->getMockServiceInstance();

        $this->createAndRegisterMockForQueryBuilder();
        $this->statement->method('fetchAll')->willReturn([]);

        $this->assertSame(
            [],
            $mock->get($table, $fields, $clause, $groupBy, $orderBy, $limit, $offset)
        );
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

        $this->assertNull(
            $mock->getSingle($table, $fields, $uid)
        );
    }

    /**
     * @test
     */
    public function updateMethodCallsExpectedDatabaseMethod()
    {
        $table = 'test';
        $uid = 123;
        $fields = ['foo' => 'bar', 'uid' => $uid];
        $mock = $this->getMockServiceInstance();

        $this->createAndRegisterMockForQueryBuilder();

        $this->assertInstanceOf(
            Statement::class,
            $mock->update($table, $fields)
        );
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

        $this->assertTrue(
            $mock->delete($table, $uid)
        );
    }

    /**
     * @test
     */
    public function deleteMethodCallsExpectedDatabaseMethodWithRecord()
    {
        $table = 'test';
        $record = ['uid' => 123];
        $mock = $this->getMockServiceInstance();

        $this->createAndRegisterMockForQueryBuilder();

        $this->assertTrue(
            $mock->delete($table, $record)
        );
    }

    public function testPreparedGet(): void
    {
        $mock = $this->getMockServiceInstance();
        $this->createAndRegisterMockForQueryBuilder();
        $this->statement->method('fetchAll')->willReturn([]);
        self::assertSame(
            [],
            $mock->preparedGet('table', 'fields', 'test = 1')
        );
    }
}
