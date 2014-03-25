<?php
namespace FluidTYPO3\Flux\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
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
 ***************************************************************/

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * @author Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
 * @package Flux
 * @subpackage Utility
 */
class ExtensionTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function testHasVendorNameWithoutVendorName() {
		$result = ExtensionNamingUtility::hasVendorName('FooExt');
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function testHasVendorNameWithVendorName() {
		$result = ExtensionNamingUtility::hasVendorName('FT3.FooExt');
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function testGetVendorNameWithoutVendorName() {
		$result = ExtensionNamingUtility::getVendorName('FooExt');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function testGetVendorNameWithVendorName() {
		$result = ExtensionNamingUtility::getVendorName('FT3.FooExt');
		$this->assertSame('FT3', $result);
	}

	/**
	 * @test
	 */
	public function testGetExtensionKeyWithoutVendorName() {
		$result = ExtensionNamingUtility::getExtensionKey('FooExt');
		$this->assertSame('foo_ext', $result);
	}

	/**
	 * @test
	 */
	public function testGetExtensionKeyWithVendorName() {
		$result = ExtensionNamingUtility::getExtensionKey('FT3.FooExt');
		$this->assertSame('foo_ext', $result);
	}

	/**
	 * @test
	 */
	public function testGetExtensionNameWithoutVendorName() {
		$result = ExtensionNamingUtility::getExtensionName('FooExt');
		$this->assertSame('FooExt', $result);
	}

	/**
	 * @test
	 */
	public function testGetExtensionNameWithVendorName() {
		$result = ExtensionNamingUtility::getExtensionName('FT3.FooExt');
		$this->assertSame('FooExt', $result);
	}

	/**
	 * @test
	 */
	public function testGetVendorNameAndExtensionKeyWithoutVendorName() {
		list($vendorName, $extensionKey) = ExtensionNamingUtility::getVendorNameAndExtensionKey('FooExt');
		$this->assertNull($vendorName);
		$this->assertSame('foo_ext', $extensionKey);
	}

	/**
	 * @test
	 */
	public function testGetVendorNameAndExtensionKeyWithVendorName() {
		list($vendorName, $extensionKey) = ExtensionNamingUtility::getVendorNameAndExtensionKey('FT3.FooExt');
		$this->assertSame('FT3', $vendorName);
		$this->assertSame('foo_ext', $extensionKey);
	}

	/**
	 * @test
	 */
	public function testGetVendorNameAndExtensionNameWithoutVendorName() {
		list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName('FooExt');
		$this->assertNull($vendorName);
		$this->assertSame('FooExt', $extensionName);
	}

	/**
	 * @test
	 */
	public function testGetVendorNameAndExtensionNameWithVendorName() {
		list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName('FT3.FooExt');
		$this->assertSame('FT3', $vendorName);
		$this->assertSame('FooExt', $extensionName);
	}

	/**
	 * @test
	 */
	public function testGetVendorNameAndExtensionNameWithoutVendorNameUnderscore() {
		list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName('foo_ext');
		$this->assertNull($vendorName);
		$this->assertSame('FooExt', $extensionName);
	}

	/**
	 * @test
	 */
	public function testGetVendorNameAndExtensionNameWithVendorNameUnderscore() {
		list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName('FT3.foo_ext');
		$this->assertSame('FT3', $vendorName);
		$this->assertSame('FooExt', $extensionName);
	}

}
