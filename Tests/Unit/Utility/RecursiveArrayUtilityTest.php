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
        $array1 = array(
            'foo' => array(
                'bar' => true
            )
        );
        $array2 = array(
            'foo' => array(
                'foo' => true
            )
        );
        $expected = array(
            'foo' => array(
                'bar' => true,
                'foo' => true
            )
        );
        $product = RecursiveArrayUtility::merge($array1, $array2);
        $this->assertSame($expected, $product);
    }

    /**
     * @test
     */
    public function canOperateArrayDiffFunction()
    {
        $array1 = array(
            'bar' => true,
            'baz' => true,
            'same' => array(
                'foo' => true
            ),
            'foo' => array(
                'bar' => true,
                'foo' => true
            )
        );
        $array2 = array(
            'bar' => true,
            'baz' => false,
            'new' => true,
            'same' => array(
                'foo' => true
            ),
            'foo' => array(
                'bar' => true
            )
        );
        $expected = array(
            'baz' => true,
            'foo' => array(
                'foo' => true
            ),
            'new' => true,
        );
        $product = RecursiveArrayUtility::diff($array1, $array2);
        $this->assertSame($expected, $product);
    }

    /**
     * @test
     */
    public function canOperateMergeRecursiveOverruleFunction()
    {
        $array1 = array(
            'foo' => array(
                'bar' => true
            )
        );
        $array2 = array(
            'foo' => array(
                'foo' => true,
                'bar' => false
            )
        );
        $expected = array(
            'foo' => array(
                'bar' => false,
                'foo' => true
            )
        );
        $product = RecursiveArrayUtility::mergeRecursiveOverrule($array1, $array2);
        $this->assertSame($expected, $product);
    }
}
