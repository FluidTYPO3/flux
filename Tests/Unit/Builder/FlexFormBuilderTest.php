<?php
namespace FluidTYPO3\Flux\Tests\Unit\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\FlexFormBuilder;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class FlexFormBuilderTest extends AbstractTestCase
{
    protected ProviderResolver $providerResolver;
    protected CacheService $cacheService;

    protected function setUp(): void
    {
        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheService = $this->getMockBuilder(CacheService::class)
            ->onlyMethods(['setInCaches', 'getFromCaches', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $GLOBALS['TCA']['table']['ctrl'] = [
            'type' => 'typefield',
            'typefield' => [
                'subtype_value_field' => 'field2',
            ],
            'useColumnsForDefaultValues' => 'field1',
        ];

        parent::setUp();
    }

    private function getConstructorArguments(): array
    {
        return [
            $this->cacheService,
            $this->providerResolver,
        ];
    }

    /**
     * @return void
     */
    public function testReturnsEmptyDataStructureIdentifierForNonMatchingTableAndField()
    {
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn(null);

        $subject = new FlexFormBuilder(...$this->getConstructorArguments());

        $result = $subject->resolveDataStructureIdentifier(
            'sometable',
            'somefield',
            ['uid' => 123]
        );
        $this->assertSame([], $result);
    }

    /**
     * @param array $identifier
     * @dataProvider getEmptyDataStructureIdentifierTestValues
     */
    public function testReturnsEmptyDataStructureForIdentifier(array $identifier)
    {
        $subject = $this->getMockBuilder(FlexFormBuilder::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $result = $subject->parseDataStructureByIdentifier($identifier);
        $this->assertSame([], $result);
    }

    public function testDataStructureForIdentifierFromCache()
    {
        $structure = ['foo' => 'bar'];
        $subject = new FlexFormBuilder(...$this->getConstructorArguments());
        $this->cacheService->method('getFromCaches')->willReturn($structure);
        $result = $subject->parseDataStructureByIdentifier(['type' => 'flux', 'record' => ['uid' => 123]]);
        $this->assertSame($structure, $result);
    }

    public function testParseDataStructureForIdentifierThrowsExceptionIfUnableToLoadRecord()
    {
        $subject = $this->getMockBuilder(FlexFormBuilder::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->onlyMethods(['loadRecordWithoutRestriction'])
            ->getMock();
        $subject->method('loadRecordWithoutRestriction')->willReturn(null);

        self::expectExceptionCode(1668011937);
        $subject->parseDataStructureByIdentifier(
            ['type' => 'flux', 'tableName' => 'table', 'record' => ['uid' => 123]]
        );
    }

    public function testReturnsEmptyDataStructureForIdentifierReturnsEmptyArrayWithoutProvider()
    {
        $subject = $this->getMockBuilder(FlexFormBuilder::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();

        $result = $subject->parseDataStructureByIdentifier(
            [
                'type' => 'flux',
                'tableName' => 'table',
                'fieldName' => 'field',
                'record' => ['uid' => 123, 'foo' => 'bar']
            ]
        );
        self::assertSame([], $result);
    }

    public function testParseDataStructureForIdentifierCachesStaticDataSourceFromProvider()
    {
        $form = Form::create();
        $form->setOption(Form::OPTION_STATIC, true);

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $this->cacheService->expects(self::once())->method('setInCaches');
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $subject = new FlexFormBuilder(...$this->getConstructorArguments());

        $result = $subject->parseDataStructureByIdentifier(
            [
                'type' => 'flux',
                'tableName' => 'table',
                'fieldName' => 'field',
                'record' => ['uid' => 123, 'foo' => 'bar']
            ]
        );
        self::assertSame(['ROOT' => ['el' => []]], $result);
    }

    public function testParseDataStructureForIdentifierReturnsDataSourceFromProvider()
    {
        $form = Form::create();

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $subject = new FlexFormBuilder(...$this->getConstructorArguments());

        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $result = $subject->parseDataStructureByIdentifier(
            [
                'type' => 'flux',
                'tableName' => 'table',
                'fieldName' => 'field',
                'record' => ['uid' => 123, 'foo' => 'bar']
            ]
        );
        self::assertSame(['ROOT' => ['el' => []]], $result);
    }

    /**
     * @return array
     */
    public function getEmptyDataStructureIdentifierTestValues()
    {
        return [
            [
                ['type' => 'unsupported']
            ],
            [
                ['type' => 'flux', 'record' => null]
            ],
        ];
    }
}
