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

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Class/DummyModel.php');

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Utility_AnnotationTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canParseAnnotationsFromModelClassNameWithoutPropertyName() {
		$class = 'Tx_Flux_Domain_Model_Dummy';
		$annotation = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Control\\Hide');
		$this->assertTrue($annotation);
	}

	/**
	 * @test
	 */
	public function canParseAnnotationsFromModelClassNameWithPropertyNameAndTriggerCache() {
		$class = 'Tx_Flux_Domain_Model_Dummy';
		Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Form\\Field', 'crdate');
		$annotation = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Form\\Field', 'crdate');
		$this->assertIsArray($annotation);
	}

	/**
	 * @test
	 */
	public function canParseShortAnnotationWithoutArguments() {
		$annotation = 'input';
		$expected = array(
			'type' => 'input',
			'config' => array()
		);
		$result = Tx_Flux_Utility_Annotation::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canParseShortAnnotationWithArguments() {
		$annotation = 'input(size: 10)';
		$expected = array(
			'type' => 'input',
			'config' => array(
				'size' => 10
			)
		);
		$result = Tx_Flux_Utility_Annotation::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canParseLongAnnotationWithArguments() {
		$annotation = '{flux:input(default: \'test\', float: 0.5)}';
		$expected = array(
			'type' => 'input',
			'config' => array(
				'default' => 'test',
				'float' => 0.5
			)
		);
		$result = Tx_Flux_Utility_Annotation::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canParseLongAnnotationWithSubArrayInArguments() {
		$annotation = '{flux:input(dummy: {foo: 1, bar: 2})}';
		$expected = array(
			'type' => 'input',
			'config' => array(
				'dummy' => array('foo' => 1, 'bar' => 2)
			)
		);
		$result = Tx_Flux_Utility_Annotation::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function returnsTrueForEmptyAnnotations() {
		$annotation = '';
		$expected = TRUE;
		$result = Tx_Flux_Utility_Annotation::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canHandleArraysOfAnnotations() {
		$annotations = array(
			'foo' => '',
			'bar' => ''
		);
		$expected = array(
			'foo' => TRUE,
			'bar' => TRUE
		);
		$result = Tx_Flux_Utility_Annotation::parseAnnotation($annotations);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canHandleSingleItemArraysOfAnnotations() {
		$annotations = array('');
		$expected = TRUE;
		$result = Tx_Flux_Utility_Annotation::parseAnnotation($annotations);
		$this->assertEquals($expected, $result);
	}

}
