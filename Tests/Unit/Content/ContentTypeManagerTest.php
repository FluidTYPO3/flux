<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\DBALException;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class ContentTypeManagerTest extends AbstractTestCase
{
    public function testFetchContentTypes(): void
    {
        $subject = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(
                [
                    'fetchDropInContentTypes',
                    'fetchFileBasedContentTypes',
                    'fetchRecordBasedContentTypes',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        self::assertSame([], $subject->fetchContentTypes());
    }

    public function testFetchContentTypesSuppressesSpecificExceptionTypes(): void
    {
        if (class_exists(DBALException::class)) {
            $exception = new DBALException('some error');
        } else {
            $exception = new \Doctrine\DBAL\Driver\PDO\Exception('some error');
        }

        $subject = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(
                [
                    'fetchDropInContentTypes',
                    'fetchFileBasedContentTypes',
                    'fetchRecordBasedContentTypes',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('fetchDropInContentTypes')->willThrowException($exception);
        $subject->method('fetchFileBasedContentTypes')->willThrowException(new NoSuchCacheException('some error'));
        self::assertSame([], $subject->fetchContentTypes());
    }

    public function testFetchContentTypesDoesNotSuppressOtherExceptionTypes(): void
    {
        $subject = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(
                [
                    'fetchDropInContentTypes',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('fetchDropInContentTypes')->willThrowException(new \RuntimeException('some error'));
        self::expectException(\RuntimeException::class);
        $subject->fetchContentTypes();
    }

    public function testRegisterContentTypeNameIncludesTypeName(): void
    {
        $subject = new ContentTypeManager();
        $subject->registerTypeName('test_foobar');
        self::assertContains('test_foobar', $subject->fetchContentTypeNames());
    }

    public function testRegisterContentTypeDefinitionIncludesDefinition(): void
    {
        $definition = $this->getMockBuilder(ContentTypeDefinitionInterface::class)->getMockForAbstractClass();
        $definition->method('getContentTypeName')->willReturn('test_foobar');

        $subject = new ContentTypeManager();
        $subject->registerTypeDefinition($definition);

        self::assertSame($definition, $subject->determineContentTypeForTypeString('test_foobar'));
    }

    /**
     * @dataProvider getDetermineContentTypeForRecordTestValues
     * @param string $field
     * @param string $expectedValue
     */
    public function testDetermineContentTypeForRecord(string $field, string $expectedValue): void
    {
        $record = [
            $field => $expectedValue,
        ];
        $subject = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['determineContentTypeForTypeString'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::once())
            ->method('determineContentTypeForTypeString')
            ->with($record[$field])
            ->willReturn(null);
        $subject->determineContentTypeForRecord($record);
    }

    public function getDetermineContentTypeForRecordTestValues(): array
    {
        return [
            'with CType field' => ['CType', 'some-ctype'],
            'with content_type field' => ['content_type', 'some-contenttype'],
            'without recognised field' => ['anyotherfield', ''],
        ];
    }

    public function testRegenerateSetsCacheValue(): void
    {
        $cache = $this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass();
        $cache->expects(self::once())->method('set')->with(ContentTypeManager::CACHE_IDENTIFIER, []);

        $subject = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['fetchContentTypes', 'getCache'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('fetchContentTypes')->willReturn([]);
        $subject->method('getCache')->willReturn($cache);
        $subject->regenerate();
    }

    public function testDetermineContentTypeLoadsTypeFromCache(): void
    {
        $definition = $this->getMockBuilder(ContentTypeDefinitionInterface::class)->getMockForAbstractClass();
        $definition->method('getContentTypeName')->willReturn('test_foobar');

        $cache = $this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass();
        $cache->method('get')->willReturn($definition);

        $subject = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['getCache'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getCache')->willReturn($cache);

        self::assertSame($definition, $subject->determineContentTypeForTypeString('test_foobar'));
    }

    public function testDetermineContentTypeReturnsNullOnCacheError(): void
    {
        $subject = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['getCache'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getCache')->willThrowException(new NoSuchCacheException('some error'));

        self::assertSame(null, $subject->determineContentTypeForTypeString('test_foobar'));
    }
}
