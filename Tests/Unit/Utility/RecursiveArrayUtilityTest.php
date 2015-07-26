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
 * @package Flux
 */
class RecursiveArrayUtilityTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canOperateArrayMergeFunction() {
		$array1 = [
			'foo' => [
				'bar' => TRUE
			]
		];
		$array2 = [
			'foo' => [
				'foo' => TRUE
			]
		];
		$expected = [
			'foo' => [
				'bar' => TRUE,
				'foo' => TRUE
			]
		];
		$product = RecursiveArrayUtility::merge($array1, $array2);
		$this->assertSame($expected, $product);
	}

	/**
	 * @test
	 */
	public function canOperateArrayDiffFunction() {
		$array1 = [
			'bar' => TRUE,
			'baz' => TRUE,
			'same' => [
				'foo' => TRUE
			],
			'foo' => [
				'bar' => TRUE,
				'foo' => TRUE
			]
		];
		$array2 = [
			'bar' => TRUE,
			'baz' => FALSE,
			'new' => TRUE,
			'same' => [
				'foo' => TRUE
			],
			'foo' => [
				'bar' => TRUE
			]
		];
		$expected = [
			'baz' => TRUE,
			'foo' => [
				'foo' => TRUE
			],
			'new' => TRUE,
		];
		$product = RecursiveArrayUtility::diff($array1, $array2);
		$this->assertSame($expected, $product);
	}

	/**
	 * @test
	 */
	public function canOperateMergeRecursiveOverruleFunction() {
		$array1 = [
			'foo' => [
				'bar' => TRUE
			]
		];
		$array2 = [
			'foo' => [
				'foo' => TRUE,
				'bar' => FALSE
			]
		];
		$expected = [
			'foo' => [
				'bar' => FALSE,
				'foo' => TRUE
			]
		];
		$product = RecursiveArrayUtility::mergeRecursiveOverrule($array1, $array2);
		$this->assertSame($expected, $product);
	}

}
