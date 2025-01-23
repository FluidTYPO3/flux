<?php
namespace FluidTYPO3\Flux\Tests\Unit\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\FlexFormBuilder;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyPageProvider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class FlexFormBuilderTest extends AbstractTestCase
{
    protected ProviderResolver $providerResolver;
    protected CacheService $cacheService;
    protected PageService $pageService;

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
        $this->pageService = $this->getMockBuilder(PageService::class)
            ->onlyMethods(['getPageTemplateConfiguration'])
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

    protected function tearDown(): void
    {
        unset($GLOBALS['TCA']);
    }

    private function getConstructorArguments(): array
    {
        return [
            $this->cacheService,
            $this->providerResolver,
            $this->pageService,
        ];
    }

    /**
     * @return void
     */
    public function testReturnsEmptyDataStructureIdentifierForNonMatchingTableAndField(): void
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
     * @return void
     */
    public function testReturnsDataStructureIdentifierBasedOnDefaultValuesForPagesWithoutUid(): void
    {
        $GLOBALS['TCA']['pages']['ctrl']['type'] = 'typefield';
        $GLOBALS['TCA']['pages']['ctrl']['typefield']['subtype_value_field'] = 'subtypefield';

        $provider = $this->getMockBuilder(DummyPageProvider::class)->disableOriginalConstructor()->getMock();
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $this->pageService->method('getPageTemplateConfiguration')->willReturn(
            [
                PageProvider::FIELD_ACTION_MAIN => 'mainAction',
                PageProvider::FIELD_ACTION_SUB => 'subAction',
            ]
        );

        $subject = new FlexFormBuilder(...$this->getConstructorArguments());
        $subject = $this->getMockBuilder(FlexFormBuilder::class)
            ->onlyMethods(['loadRecordWithoutRestriction'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();

        $result = $subject->resolveDataStructureIdentifier(
            'pages',
            'somefield',
            ['somefield' => 'bar', 'pid' => -1]
        );
        $this->assertSame(
            [
                'type' => 'flux',
                'tableName' => 'pages',
                'fieldName' => 'somefield',
                'record' => [
                    'somefield' => 'bar'
                ],
                'originalIdentifier' => [],
            ],
            $result
        );
    }

    /**
     * @param array $identifier
     * @dataProvider getEmptyDataStructureIdentifierTestValues
     */
    public function testReturnsEmptyDataStructureForIdentifier(array $identifier): void
    {
        $subject = $this->getMockBuilder(FlexFormBuilder::class)
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $result = $subject->parseDataStructureByIdentifier($identifier);
        $this->assertSame([], $result);
    }

    public function testDataStructureForIdentifierFromCache(): void
    {
        $structure = ['foo' => 'bar'];
        $subject = new FlexFormBuilder(...$this->getConstructorArguments());
        $this->cacheService->method('getFromCaches')->willReturn($structure);
        $result = $subject->parseDataStructureByIdentifier(['type' => 'flux', 'record' => ['uid' => 123]]);
        $this->assertSame($structure, $result);
    }

    public function testParseDataStructureForIdentifierThrowsExceptionIfUnableToLoadRecord(): void
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

    public function testReturnsEmptyDataStructureForIdentifierReturnsEmptyArrayWithoutProvider(): void
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

    public function testParseDataStructureForIdentifierCachesStaticDataSourceFromProvider(): void
    {
        $form = Form::create();
        $form->setOption(FormOption::STATIC, true);

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

    public function testParseDataStructureForIdentifierReturnsDataSourceFromProvider(): void
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

    public function getEmptyDataStructureIdentifierTestValues(): array
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

    public function testResolveDataStructureIdentifierReturnsEmptyWithNonDefaultDataStructureKey(): void
    {
        $subject = new FlexFormBuilder(...$this->getConstructorArguments());
        $output = $subject->resolveDataStructureIdentifier('table', 'field', [], ['dataStructureKey' => 'non-default']);
        self::assertSame([], $output);
    }

    public function testParseDataStructureIdentifierReturnsEmptyWithNonFluxType(): void
    {
        $subject = new FlexFormBuilder(...$this->getConstructorArguments());
        $output = $subject->parseDataStructureByIdentifier(['type' => 'not-flux']);
        self::assertSame([], $output);
    }

    public function testParseDataStructureIdentifierReturnsEmptyWithMissingRecord(): void
    {
        $subject = new FlexFormBuilder(...$this->getConstructorArguments());
        $output = $subject->parseDataStructureByIdentifier(['type' => 'flux']);
        self::assertSame([], $output);
    }

    public function testPatchTceformsWrapper(): void
    {
        $ds = [
            'something' => 'test',
            'someArray' => [
                'someSub' => 'sub',
                'config' => [
                    'type' => 'a-type',
                ],
            ],
        ];

        $expectedDs = $ds;
        $expectedDs['someArray'] = ['TCEforms' => $expectedDs['someArray']];

        $subject = new FlexFormBuilder(...$this->getConstructorArguments());
        $output = $this->callInaccessibleMethod($subject, 'patchTceformsWrapper', $ds);
        self::assertSame($expectedDs, $output);
    }
}
