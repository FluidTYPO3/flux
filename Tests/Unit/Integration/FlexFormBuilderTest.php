<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FlexFormBuilder;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class FlexFormBuilderTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        $this->singletonInstances[FluxService::class] = $this->getMockBuilder(FluxService::class)
            ->setMethods(['resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->singletonInstances[FluxService::class]->method('resolvePrimaryConfigurationProvider')
            ->willReturn($this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass());

        $GLOBALS['TCA']['table']['ctrl'] = [
            'type' => 'typefield',
            'typefield' => [
                'subtype_value_field' => 'field2',
            ],
            'useColumnsForDefaultValues' => 'field1',
        ];

        parent::setUp();
    }

    public function testResolveDataStructureIdentifier(): void
    {
        $expected = [
            'type' => 'flux',
            'tableName' => 'table',
            'fieldName' => 'field',
            'record' => [
                'field' => 'test',
            ],
            'originalIdentifier' => [
                'original' => true,
            ],
        ];

        $subject = new FlexFormBuilder();
        $output = $subject->resolveDataStructureIdentifier('table', 'field', $expected['record'], ['original' => true]);
        self::assertSame($expected, $output);
    }

    public function testParseDataStructureByIdentifierReturnsEmptyOnMismatchedType(): void
    {
        $subject = new FlexFormBuilder();
        self::assertSame([], $subject->parseDataStructureByIdentifier(['type' => 'notmatched']));
    }

    public function testParseDataStructureByIdentifierReturnsEmptyWithoutRecord(): void
    {
        $subject = new FlexFormBuilder();
        self::assertSame([], $subject->parseDataStructureByIdentifier(['type' => 'flux', 'record' => null]));
    }
}
