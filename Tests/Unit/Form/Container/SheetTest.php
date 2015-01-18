<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * author Claus Due <claus@namelesscoder.net>
 * @package Flux
 */
class SheetTest extends AbstractContainerTest {

	/**
	 * @test
	 */
	public function testDescriptionPropertyWorks() {
		$this->assertGetterAndSetterWorks('description', 'foobardescription', 'foobardescription', TRUE);
	}

	/**
	 * @test
	 */
	public function testShortDescriptionPropertyWorks() {
		$this->assertGetterAndSetterWorks('shortDescription', 'foobarshortdescription', 'foobarshortdescription', TRUE);
	}

}
