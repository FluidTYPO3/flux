<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;

/**
 * ExtensionNamingUtilityTest
 */
class ExtensionNamingUtilityTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function testHasVendorNameWithoutVendorName()
    {
        $result = ExtensionNamingUtility::hasVendorName('FooExt');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testHasVendorNameWithVendorName()
    {
        $result = ExtensionNamingUtility::hasVendorName('FT3.FooExt');
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function testGetVendorNameWithoutVendorName()
    {
        $result = ExtensionNamingUtility::getVendorName('FooExt');
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function testGetVendorNameWithVendorName()
    {
        $result = ExtensionNamingUtility::getVendorName('FT3.FooExt');
        $this->assertSame('FT3', $result);
    }

    /**
     * @test
     */
    public function testGetExtensionKeyWithoutVendorName()
    {
        $result = ExtensionNamingUtility::getExtensionKey('FooExt');
        $this->assertSame('foo_ext', $result);
    }

    /**
     * @test
     */
    public function testGetExtensionKeyWithVendorName()
    {
        $result = ExtensionNamingUtility::getExtensionKey('FT3.FooExt');
        $this->assertSame('foo_ext', $result);
    }

    /**
     * @test
     */
    public function testGetExtensionNameWithoutVendorName()
    {
        $result = ExtensionNamingUtility::getExtensionName('FooExt');
        $this->assertSame('FooExt', $result);
    }

    /**
     * @test
     */
    public function testGetExtensionNameWithVendorName()
    {
        $result = ExtensionNamingUtility::getExtensionName('FT3.FooExt');
        $this->assertSame('FooExt', $result);
    }

    /**
     * @test
     */
    public function testGetVendorNameAndExtensionKeyWithoutVendorName()
    {
        list($vendorName, $extensionKey) = ExtensionNamingUtility::getVendorNameAndExtensionKey('FooExt');
        $this->assertNull($vendorName);
        $this->assertSame('foo_ext', $extensionKey);
    }

    /**
     * @test
     */
    public function testGetVendorNameAndExtensionKeyWithVendorName()
    {
        list($vendorName, $extensionKey) = ExtensionNamingUtility::getVendorNameAndExtensionKey('FT3.FooExt');
        $this->assertSame('FT3', $vendorName);
        $this->assertSame('foo_ext', $extensionKey);
    }

    /**
     * @test
     */
    public function testGetVendorNameAndExtensionNameWithoutVendorName()
    {
        list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName('FooExt');
        $this->assertNull($vendorName);
        $this->assertSame('FooExt', $extensionName);
    }

    /**
     * @test
     */
    public function testGetVendorNameAndExtensionNameWithVendorName()
    {
        list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName('FT3.FooExt');
        $this->assertSame('FT3', $vendorName);
        $this->assertSame('FooExt', $extensionName);
    }

    /**
     * @test
     */
    public function testGetVendorNameAndExtensionNameWithoutVendorNameUnderscore()
    {
        list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName('foo_ext');
        $this->assertNull($vendorName);
        $this->assertSame('FooExt', $extensionName);
    }

    /**
     * @test
     */
    public function testGetVendorNameAndExtensionNameWithVendorNameUnderscore()
    {
        list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName('FT3.foo_ext');
        $this->assertSame('FT3', $vendorName);
        $this->assertSame('FooExt', $extensionName);
    }
}
