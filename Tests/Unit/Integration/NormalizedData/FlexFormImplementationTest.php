<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\NormalizedData;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\NormalizedData\Converter\ConverterInterface;
use FluidTYPO3\Flux\Integration\NormalizedData\FlexFormImplementation;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class FlexFormImplementationTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        FlexFormImplementation::registerForTableAndField('tt_content', 'pi_flexform');
    }

    /**
     * @dataProvider getAppliesToFieldTestValues
     */
    public function testAppliesToField(bool $expected, string $table, ?string $field): void
    {
        $subject = new FlexFormImplementation([]);
        self::assertSame($expected, $subject->appliesToTableField($table, $field));
    }

    public function getAppliesToFieldTestValues(): array
    {
        return [
            'unmatched field and table' => [false, 'foo', 'bar'],
            'unmatched field and matched table' => [false, 'tt_content', 'bar'],
            'matched field and table' => [true, 'tt_content', 'pi_flexform'],
            'null field and matched table' => [true, 'tt_content', null],
        ];
    }

    public function testGetConverterForTableFieldAndRecord(): void
    {
        $subject = new FlexFormImplementation([]);
        self::assertInstanceOf(
            ConverterInterface::class,
            $subject->getConverterForTableFieldAndRecord('tt_content', 'pi_flexform', ['uid' => 123])
        );
    }
}
