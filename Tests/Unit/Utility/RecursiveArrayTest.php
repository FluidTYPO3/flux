<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Utility_RecursiveArrayTest extends Tx_Flux_Tests_AbstractFunctionalTest {

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
		$product = Tx_Flux_Utility_RecursiveArray::merge($array1, $array2);
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
		$product = Tx_Flux_Utility_RecursiveArray::diff($array1, $array2);
		$this->assertSame($expected, $product);
	}

	/**
	 * @test
	 */
	public function returnsSameValueOnWalkFlexFormNodeIfNodeIsNotAnArray() {
		$node = time();
		$converted = Tx_Flux_Utility_RecursiveArray::walkFlexFormNode($node);
		$this->assertSame($node, $converted);
	}

	/**
	 * @test
	 */
	public function returnsEarlyOnWalkFlexFormNodeIfNodeIsShallowArray() {
		$node = array(
			'vDEF' => 'test'
		);
		$converted = Tx_Flux_Utility_RecursiveArray::walkFlexFormNode($node);
		$this->assertSame($node['vDEF'], $converted);
	}

	/**
	 * @test
	 */
	public function returnsEarlyOnWalkFlexFormNodeIfNodeIsMalformedArray() {
		$node = array(
			'bad' => 'bad'
		);
		$converted = Tx_Flux_Utility_RecursiveArray::walkFlexFormNode($node);
		$this->assertSame($node, $converted);
	}

	/**
	 * @test
	 */
	public function canWalkFlexFormNode() {
		$node = array(
			'TCEforms' => array(
				'el' => array(
					'_ignored' => array(
						'vDEF' => 'foo',
					),
					'dotted.path' => array(
						'vDEF' => 'baz',
					),
					'nested' => array(
						'vDEF' => 'xyz'
					),
					'morenested' => array(
						'el' => array(
							'xyz' => array(
								'vDEF' => 'moo'
							)
						),
						'raw' => 'dummy'
					),
				),
			)
		);
		$expected = array(
			'TCEforms' => array(
				'dotted' => array(
					'path' => 'baz'
				),
				'nested' => 'xyz',
				'morenested' => array(
					'xyz' => 'moo'
				)
			),
		);
		$walked = Tx_Flux_Utility_RecursiveArray::walkFlexFormNode($node);
		$this->assertIsArray($walked);
		$this->assertSame($expected, $walked);
	}

}
