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
class Tx_Flux_Utility_MiscellaneousTest extends Tx_Flux_Tests_AbstractFunctionalTest {

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
				'el' => Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren
			)
		);
		return $clipBoardData;
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrl() {
		$clipBoardData = $this->getClipBoardDataFixture();
		Tx_Flux_Utility_ClipBoard::setClipBoardData($clipBoardData);
		$iconWithUrl = Tx_Flux_Utility_ClipBoard::createIconWithUrl('1-2-3');
		$this->assertNotEmpty($iconWithUrl);
		Tx_Flux_Utility_ClipBoard::clearClipBoardData();
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrlAsReference() {
		$clipBoardData = $this->getClipBoardDataFixture();
		$clipBoardData['normal']['mode'] = 'reference';
		Tx_Flux_Utility_ClipBoard::setClipBoardData($clipBoardData);
		$iconWithUrl = Tx_Flux_Utility_ClipBoard::createIconWithUrl('1-2-3', TRUE);
		$this->assertNotEmpty($iconWithUrl);
		Tx_Flux_Utility_ClipBoard::clearClipBoardData();
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrlAsReferenceReturnsEmptyStringIfModeIsCut() {
		$clipBoardData = $this->getClipBoardDataFixture();
		Tx_Flux_Utility_ClipBoard::setClipBoardData($clipBoardData);
		$iconWithUrl = Tx_Flux_Utility_ClipBoard::createIconWithUrl('1-2-3', TRUE);
		$this->assertIsString($iconWithUrl);
		$this->assertEmpty($iconWithUrl);
		Tx_Flux_Utility_ClipBoard::clearClipBoardData();
	}

}
