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
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinitionRepository;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Mock\QueryBuilder;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Package\PackageManager;

class RecordBasedContentTypeDefinitionRepositoryTest extends AbstractTestCase
{
    protected ConnectionPool $connectionPool;

    protected function setUp(): void
    {
        $packageManager = $this->getMockBuilder(PackageManager::class)
            ->setMethods(['isPackageActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $packageManager->method('isPackageActive')->willReturn(true);

        $this->connectionPool = $this->getMockBuilder(ConnectionPool::class)
            ->onlyMethods(['getQueryBuilderForTable'])
            ->getMock();

        AccessibleExtensionManagementUtility::setPackageManager($packageManager);

        parent::setUp();
    }

    public function testReturnsEmptySetOfDefinitionsOnTableNotExists(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->onlyMethods(['select'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder->method('select')->willThrowException(
            $this->getMockBuilder(TableNotFoundException::class)->disableOriginalConstructor()->getMock()
        );
        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($queryBuilder);
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

        $queryBuilder = new QueryBuilder($definitionRecords);
        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($queryBuilder);

        $subject = new RecordBasedContentTypeDefinitionRepository($this->connectionPool);

        $definitions = $subject->fetchContentTypeDefinitions();
        self::assertCount(2, $definitions);
        self::assertInstanceOf(RecordBasedContentTypeDefinition::class, reset($definitions));
    }
}
