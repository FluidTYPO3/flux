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
class RecursiveArrayUtilityTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canOperateArrayMergeFunction() {
		$array1 = array(
			'foo' => array(
				'bar' => TRUE
			)
		);
		$array2 = array(
			'foo' => array(
				'foo' => TRUE
			)
		);
		$expected = array(
			'foo' => array(
				'bar' => TRUE,
				'foo' => TRUE
			)
		);
		$product = RecursiveArrayUtility::merge($array1, $array2);
		$this->assertSame($expected, $product);
	}

	/**
	 * @test
	 */
	public function canOperateArrayDiffFunction() {
		$array1 = array(
			'bar' => TRUE,
			'baz' => TRUE,
			'same' => array(
				'foo' => TRUE
			),
			'foo' => array(
				'bar' => TRUE,
				'foo' => TRUE
			)
		);
		$array2 = array(
			'bar' => TRUE,
			'baz' => FALSE,
			'new' => TRUE,
			'same' => array(
				'foo' => TRUE
			),
			'foo' => array(
				'bar' => TRUE
			)
		);
		$expected = array(
			'baz' => TRUE,
			'foo' => array(
				'foo' => TRUE
			),
			'new' => TRUE,
		);
		$product = RecursiveArrayUtility::diff($array1, $array2);
		$this->assertSame($expected, $product);
	}

	/**
	 * @test
	 */
	public function canOperateMergeRecursiveOverruleFunction() {
		$array1 = array(
			'foo' => array(
				'bar' => TRUE
			)
		);
		$array2 = array(
			'foo' => array(
				'foo' => TRUE,
				'bar' => FALSE
			)
		);
		$expected = array(
			'foo' => array(
				'bar' => FALSE,
				'foo' => TRUE
			)
		);
		$product = RecursiveArrayUtility::mergeRecursiveOverrule($array1, $array2);
		$this->assertSame($expected, $product);
	}

}
