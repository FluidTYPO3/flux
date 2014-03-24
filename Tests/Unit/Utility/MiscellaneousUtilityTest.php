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
class MiscellaneousTest extends AbstractTestCase {

	/**
	 * Setup
	 */
	protected function setUp() {
		parent::setUp();
		$GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] = array();
	}

	/**
	 * @return array
	 */
	protected function getClipBoardDataFixture() {
		$clipBoardData = array(
			'current' => 'normal',
			'normal' => array(
				'el' => \FluidTYPO3\Flux\Tests\Fixtures\Data\Records::$contentRecordWithoutParentAndWithoutChildren
			)
		);
		return $clipBoardData;
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrl() {
		$clipBoardData = $this->getClipBoardDataFixture();
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3');
		$this->assertNotEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrlAsReference() {
		$clipBoardData = $this->getClipBoardDataFixture();
		$clipBoardData['normal']['mode'] = 'reference';
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3', TRUE);
		$this->assertNotEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrlAsReferenceReturnsEmptyStringIfModeIsCut() {
		$clipBoardData = $this->getClipBoardDataFixture();
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3', TRUE);
		$this->assertIsString($iconWithUrl);
		$this->assertEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
	}

}
