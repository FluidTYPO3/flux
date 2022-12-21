<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\FormEngine\NormalizedDataStructureProvider;
use FluidTYPO3\Flux\Integration\NormalizedData\Converter\InlineRecordDataConverter;
use FluidTYPO3\Flux\Integration\NormalizedData\FlexFormImplementation;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class NormalizedDataStructureProviderTest extends AbstractTestCase
{
    public function testAddData(): void
    {
        $result = [
            'tableName' => 'foobar',
            'databaseRow' => [
                'uid' => 123,
            ],
            'processedTca' => [
                'columns' => [
                    'foo' => [],
                ],
            ],
        ];

        $converter = $this->getMockBuilder(InlineRecordDataConverter::class)
            ->setMethods(['convertStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $converter->method('convertStructure')->willReturn($result + ['foo' => 'bar']);

        $implementation1 = $this->getMockBuilder(FlexFormImplementation::class)
            ->setMethods(['appliesToTableField'])
            ->disableOriginalConstructor()
            ->getMock();
        $implementation1->method('appliesToTableField')->willReturn(false);
        $implementation2 = $this->getMockBuilder(FlexFormImplementation::class)
            ->setMethods(['appliesToTableField', 'getConverterForTableFieldAndRecord'])
            ->disableOriginalConstructor()
            ->getMock();
        $implementation2->method('appliesToTableField')->willReturn(true);
        $implementation2->method('getConverterForTableFieldAndRecord')->willReturn($converter);

        $implementations = [
            $implementation1,
            $implementation2,
        ];

        $subject = $this->getMockBuilder(NormalizedDataStructureProvider::class)
            ->setMethods(['resolveImplementationsForTableField'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('resolveImplementationsForTableField')->willReturn($implementations);

        $output = $subject->addData($result);
        self::assertSame($result + ['foo' => 'bar'], $output);
    }
}
