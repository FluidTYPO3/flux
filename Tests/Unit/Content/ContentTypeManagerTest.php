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
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyContentTypeManager;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class ContentTypeManagerTest extends AbstractTestCase
{
    private ContentTypeManager $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getMockBuilder(ContentTypeManager::class)
            ->onlyMethods(
                [
                    'fetchDropInContentTypes',
                    'fetchFileBasedContentTypes',
                    'fetchRecordBasedContentTypes',
                    'getCache',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testFetchContentTypes(): void
    {
        self::assertSame([], $this->subject->fetchContentTypes());
    }

    public function testFetchContentTypesSuppressesSpecificExceptionTypes(): void
    {
        if (class_exists(DBALException::class)) {
            $exception = new DBALException('some error');
        } else {
            $exception = new \Doctrine\DBAL\Driver\PDO\Exception('some error');
        }

        $this->subject->method('fetchDropInContentTypes')->willThrowException($exception);
        $this->subject->method('fetchFileBasedContentTypes')->willThrowException(new NoSuchCacheException('some error'));
        self::assertSame([], $this->subject->fetchContentTypes());
    }

    public function testFetchContentTypesDoesNotSuppressOtherExceptionTypes(): void
    {
        $cache = $this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass();
        $this->subject->method('getCache')->willReturn($cache);
        $this->subject->method('fetchDropInContentTypes')->willThrowException(new \RuntimeException('some error'));
        self::expectException(\RuntimeException::class);
        $this->subject->fetchContentTypes();
    }

    public function testRegisterContentTypeNameIncludesTypeName(): void
    {
        $this->subject->registerTypeName('test_foobar');
        self::assertContains('test_foobar', $this->subject->fetchContentTypeNames());
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
            ->onlyMethods(['determineContentTypeForTypeString'])
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
            ->onlyMethods(['fetchContentTypes', 'getCache'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getCache')->willReturn($cache);
        $subject->method('fetchContentTypes')->willReturn([]);
        $subject->regenerate();
    }
}
