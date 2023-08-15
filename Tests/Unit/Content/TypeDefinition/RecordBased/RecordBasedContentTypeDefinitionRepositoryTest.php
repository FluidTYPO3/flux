<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Tests\Unit\Content\TypeDefinition\RecordBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Statement;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinitionRepository;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Package\PackageManager;

class RecordBasedContentTypeDefinitionRepositoryTest extends AbstractTestCase
{
    protected QueryBuilder $queryBuilder;
    protected ConnectionPool $connectionPool;

    protected function setUp(): void
    {
        $packageManager = $this->getMockBuilder(PackageManager::class)
            ->setMethods(['isPackageActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $packageManager->method('isPackageActive')->willReturn(true);

        $this->queryBuilder = $this->createQueryBuilderMock();
        $this->connectionPool = $this->getMockBuilder(ConnectionPool::class)
            ->onlyMethods(['getQueryBuilderForTable'])
            ->getMock();
        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($this->queryBuilder);

        AccessibleExtensionManagementUtility::setPackageManager($packageManager);

        parent::setUp();
    }

    public function testReturnsEmptySetOfDefinitionsOnTableNotExists(): void
    {
        $this->queryBuilder->method('select')->willThrowException(
            $this->getMockBuilder(TableNotFoundException::class)->disableOriginalConstructor()->getMock()
        );
        $subject = new RecordBasedContentTypeDefinitionRepository($this->connectionPool);

        self::assertSame([], $subject->fetchContentTypeDefinitions());
    }

    public function testCreatesDefinitionInstancesFromResultSet(): void
    {
        $definitionRecords = [
            [
                'content_type' => 'flux_test',
                'extension_identity' => 'FluidTYPO3.Flux',
                'icon' => '',
            ],
            [
                'content_type' => 'flux_test2',
                'extension_identity' => '',
                'icon' => '',
            ],

        ];

        $subject = new RecordBasedContentTypeDefinitionRepository($this->connectionPool);

        $this->queryBuilder->execute()->method('fetchAll')->willReturn($definitionRecords);

        $definitions = $subject->fetchContentTypeDefinitions();
        self::assertCount(2, $definitions);
        self::assertInstanceOf(RecordBasedContentTypeDefinition::class, reset($definitions));
    }

    protected function createQueryBuilderMock(): MockObject
    {
        $statement = $this->getMockBuilder(Statement::class)
            ->setMethods(['fetchAll'])
            ->disableOriginalConstructor()
            ->getMock();

        $expressionBuilder = $this->getMockBuilder(ExpressionBuilder::class)
            ->setMethods(['eq'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $expressionBuilder->method('eq')->willReturn('');

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->setMethods(
                [
                    'select',
                    'from',
                    'where',
                    'expr',
                    'orderBy',
                    'execute',
                    'createNamedParameter',
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('expr')->willReturn($expressionBuilder);
        $queryBuilder->method('createNamedParameter')->willReturn('"foo"');
        $queryBuilder->method('execute')->willReturn($statement);

        return $queryBuilder;
    }
}
