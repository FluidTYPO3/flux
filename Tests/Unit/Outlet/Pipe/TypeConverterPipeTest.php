<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;
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

use FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe\AbstractPipeTestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\IntegerConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectStorageConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter;

/**
 * @package Flux
 */
class TypeConverterPipeTest extends AbstractPipeTestCase {

	/**
	 * @test
	 */
	public function canConductData() {
		$instance = $this->createInstance();
		$converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter';
		$converter = $this->objectManager->get($converterClass);
		$instance->setTypeConverter($converter);
		$instance->setTargetType('string');
		$output = $instance->conduct('test');
		$this->assertEquals('test', $output);
	}

	/**
	 * @test
	 */
	public function conductingDataThrowsExceptionWhenTypeConverterCannotConvertToTargetType() {
		$instance = $this->createInstance();
		$converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter';
		$converter = $this->objectManager->get($converterClass);
		$instance->setTypeConverter($converter);
		$instance->setTargetType('TYPO3\CMS\Domain\Model\FrontendUser');
		$this->setExpectedException('FluidTYPO3\Flux\Outlet\Pipe\Exception', NULL, 1386292424);
		$instance->conduct($this->defaultData);
	}

	/**
	 * @test
	 */
	public function conductingDataThrowsPipeExceptionWhenTypeConverterReturnsError() {
		$instance = $this->createInstance();
		$converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter';
		$converter = $this->objectManager->get($converterClass);
		$instance->setTypeConverter($converter);
		$instance->setTargetType('float');
		if ('6.0' !== substr(TYPO3_version, 0, 3)) {
			$this->setExpectedException('FluidTYPO3\Flux\Outlet\Pipe\Exception');
		}
		$instance->conduct('test');
	}

	/**
	 * @test
	 */
	public function conductingDataPassesThroughExceptionWhenTypeConverterFails() {
		$instance = $this->createInstance();
		$converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter';
		$converter = $this->objectManager->get($converterClass);
		$instance->setTypeConverter($converter);
		$instance->setTargetType('DateTime');
		$this->setExpectedException('TYPO3\CMS\Extbase\Property\Exception\TypeConverterException', NULL, 1308003914);
		$instance->conduct(array());
	}

	/**
	 * @test
	 */
	public function canGetAndSetTargetType() {
		$this->assertGetterAndSetterWorks('targetType', 'string', 'string', TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetTypeConverter() {
		$converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter';
		$converter = $this->objectManager->get($converterClass);
		$this->assertGetterAndSetterWorks('typeConverter', $converter, $converter, TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetTypeConverterAndCreatesInstanceIfClassName() {
		$converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter';
		$converter = $this->objectManager->get($converterClass);
		$this->assertGetterAndSetterWorks('typeConverter', $converterClass, $converter, TRUE);
	}

}
