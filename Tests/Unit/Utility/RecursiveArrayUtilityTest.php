<?php
namespace FluidTYPO3\Flux\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * @package Flux
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

}
