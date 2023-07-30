<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;

/**
 * RecursiveArrayUtilityTest
 */
class RecursiveArrayUtilityTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function canOperateArrayMergeFunction()
    {
        $array1 = [
            'foo' => [
                'bar' => true
            ]
        ];
        $array2 = [
            'foo' => [
                'foo' => true
            ]
        ];
        $expected = [
            'foo' => [
                'bar' => true,
                'foo' => true
            ]
        ];
        $product = RecursiveArrayUtility::merge($array1, $array2);
        $this->assertSame($expected, $product);
    }

    /**
     * @test
     */
    public function canOperateArrayDiffFunction()
    {
        $array1 = [
            'bar' => true,
            'baz' => true,
            'same' => [
                'foo' => true
            ],
            'foo' => [
                'bar' => true,
                'foo' => true
            ]
        ];
        $array2 = [
            'bar' => true,
            'baz' => false,
            'new' => true,
            'same' => [
                'foo' => true
            ],
            'foo' => [
                'bar' => true
            ]
        ];
        $expected = [
            'baz' => true,
            'foo' => [
                'foo' => true
            ],
            'new' => true,
        ];
        $product = RecursiveArrayUtility::diff($array1, $array2);
        $this->assertSame($expected, $product);
    }

    /**
     * @test
     */
    public function canOperateMergeRecursiveOverruleFunction()
    {
        $array1 = [
            'foo' => [
                'bar' => true
            ]
        ];
        $array2 = [
            'foo' => [
                'foo' => true,
                'bar' => false
            ]
        ];
        $expected = [
            'foo' => [
                'bar' => false,
                'foo' => true
            ]
        ];
        $product = RecursiveArrayUtility::mergeRecursiveOverrule($array1, $array2);
        $this->assertSame($expected, $product);
    }

    public function testConvertPathToArray(): void
    {
        $result = RecursiveArrayUtility::convertPathToArray('foo.bar.baz');
        self::assertSame(['foo' => ['bar' => ['baz' => null]]], $result);
    }

    public function testConvertPathToArrayWithValue(): void
    {
        $result = RecursiveArrayUtility::convertPathToArray('foo.bar.baz', ['foo' => 'bar']);
        self::assertSame(['foo' => ['bar' => ['baz' => ['foo' => 'bar']]]], $result);
    }
}
