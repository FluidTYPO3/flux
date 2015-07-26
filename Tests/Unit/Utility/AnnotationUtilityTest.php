<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\AnnotationUtility;

/**
 * @package Flux
 */
class AnnotationUtilityTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canParseAnnotationsFromModelClassNameWithoutPropertyName() {
		$class = 'FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\Domain\\Model\\Dummy';
		$annotation = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\Hide');
		$this->assertTrue($annotation);
	}

	/**
	 * @test
	 */
	public function canParseAnnotationsFromModelClassNameWithPropertyNameAndTriggerCache() {
		$class = 'FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\Domain\\Model\\Dummy';
		AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Form\Field', 'crdate');
		$annotation = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Form\Field', 'crdate');
		$this->assertIsArray($annotation);
	}

	/**
	 * @test
	 */
	public function canParseShortAnnotationWithoutArguments() {
		$annotation = 'input';
		$expected = [
			'type' => 'input',
			'config' => []
		];
		$result = AnnotationUtility::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canParseShortAnnotationWithArguments() {
		$annotation = 'input(size: 10)';
		$expected = [
			'type' => 'input',
			'config' => [
				'size' => 10
			]
		];
		$result = AnnotationUtility::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canParseLongAnnotationWithArguments() {
		$annotation = '{flux:input(default: \'test\', float: 0.5)}';
		$expected = [
			'type' => 'input',
			'config' => [
				'default' => 'test',
				'float' => 0.5
			]
		];
		$result = AnnotationUtility::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canParseLongAnnotationWithSubArrayInArguments() {
		$annotation = '{flux:input(dummy: {foo: 1, bar: 2})}';
		$expected = [
			'type' => 'input',
			'config' => [
				'dummy' => ['foo' => 1, 'bar' => 2]
			]
		];
		$result = AnnotationUtility::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function returnsTrueForEmptyAnnotations() {
		$annotation = '';
		$expected = TRUE;
		$result = AnnotationUtility::parseAnnotation($annotation);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canHandleArraysOfAnnotations() {
		$annotations = [
			'foo' => '',
			'bar' => ''
		];
		$expected = [
			'foo' => TRUE,
			'bar' => TRUE
		];
		$result = AnnotationUtility::parseAnnotation($annotations);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function canHandleSingleItemArraysOfAnnotations() {
		$annotations = [''];
		$expected = TRUE;
		$result = AnnotationUtility::parseAnnotation($annotations);
		$this->assertEquals($expected, $result);
	}

}
