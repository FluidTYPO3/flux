<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\FlexFormBuilder;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class FlexFormBuilderTest extends AbstractTestCase
{
    protected ?FluxService $fluxService = null;

    protected function setUp(): void
    {
        $this->fluxService = $this->getMockBuilder(FluxService::class)
            ->onlyMethods(['getFromCaches', 'setInCaches', 'resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function testCreatesInstancesInConstructor(): void
    {
        $subject = new FlexFormBuilder($this->fluxService);
        self::assertInstanceOf(
            FluxService::class,
            $this->getInaccessiblePropertyValue($subject, 'configurationService')
        );
    }

    /**
     * @return void
     */
    public function testReturnsEmptyDataStructureIdentifierForNonMatchingTableAndField()
    {
        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn(null);

        $subject = new FlexFormBuilder($this->fluxService);

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
            ->setConstructorArgs([$this->fluxService])
            ->getMock();
        $result = $subject->parseDataStructureByIdentifier($identifier);
        $this->assertSame([], $result);
    }

    public function testDataStructureForIdentifierFromCache()
    {
        $structure = ['foo' => 'bar'];
        $subject = new FlexFormBuilder($this->fluxService);
        $this->fluxService->method('getFromCaches')->willReturn($structure);
        $result = $subject->parseDataStructureByIdentifier(['type' => 'flux', 'record' => ['uid' => 123]]);
        $this->assertSame($structure, $result);
    }

    public function testParseDataStructureForIdentifierThrowsExceptionIfUnableToLoadRecord()
    {
        $subject = $this->getMockBuilder(FlexFormBuilder::class)
            ->setConstructorArgs([$this->fluxService])
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
            ->setConstructorArgs([$this->fluxService])
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

        $subject = new FlexFormBuilder($this->fluxService);

        $this->fluxService->expects(self::once())->method('setInCaches');
        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

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

    public function testParseDataStructureForIdentifierReturnsDataSourceFromProvider()
    {
        $form = Form::create();

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $subject = new FlexFormBuilder($this->fluxService);

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

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
