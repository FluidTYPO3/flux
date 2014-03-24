<?php
namespace FluidTYPO3\Flux\ViewHelpers\Be\Link\Content;
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

use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;

/**
 * @package Flux
 */
class PasteViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * Setup
	 */
	protected function setUp() {
		parent::setUp();
		$GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] = array();
	}

	/**
	 * @test
	 */
	public function canCreateRelativeToValue() {
		$arguments = array(
			'area' => 'test',
			'row' => Records::$contentRecordWithoutParentAndWithoutChildren,
			'relativeTo' => Records::$contentRecordWithParentAndWithoutChildren['uid']
		);
		$instance = $this->buildViewHelperInstance($arguments);
		$relativeToValue = $this->callInaccessibleMethod($instance, 'getRelativeToValue');
		$this->assertNotEmpty($relativeToValue);
	}

	/**
	 * @test
	 */
	public function canCreateRelativeToValueWithoutAreaName() {
		$arguments = array(
			'area' => NULL,
			'row' => Records::$contentRecordWithoutParentAndWithoutChildren,
			'relativeTo' => Records::$contentRecordWithParentAndWithoutChildren['uid']
		);
		$instance = $this->buildViewHelperInstance($arguments);
		$relativeToValue = $this->callInaccessibleMethod($instance, 'getRelativeToValue');
		$this->assertNotEmpty($relativeToValue);
	}

	/**
	 * @test
	 */
	public function canCreateRelativeToValueAsReference() {
		$arguments = array(
			'area' => 'test',
			'row' => Records::$contentRecordWithoutParentAndWithoutChildren,
			'relativeTo' => Records::$contentRecordWithParentAndWithoutChildren['uid'],
			'reference' => TRUE
		);
		$instance = $this->buildViewHelperInstance($arguments);
		$relativeToValue = $this->callInaccessibleMethod($instance, 'getRelativeToValue');
		$this->assertNotEmpty($relativeToValue);
	}

}
