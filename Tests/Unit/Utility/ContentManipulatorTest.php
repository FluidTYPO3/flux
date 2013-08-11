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
class Tx_Flux_Utility_ContentManipulatorTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canDetectParentElementAreaFromRecord() {
		$result = Tx_Flux_Utility_ContentManipulator::detectParentElementAreaFromRecord(0);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canDetectParentUidFromRecord() {
		$result = Tx_Flux_Utility_ContentManipulator::detectParentUidFromRecord(0);
		$this->assertIsInteger($result);
	}

	/**
	 * @test
	 */
	public function affectByRequestParametersReturnsEarlyWithUnrecognisedUrl() {
		$parameters = array(
			'returnUrl' => 'some.php?arg=1#hascutoffpointbutnovalues'
		);
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
		$tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
		$result = Tx_Flux_Utility_ContentManipulator::affectRecordByRequestParameters($record, $parameters, $tceMain);
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function affectByRequestParametersAppliesContentAreaAndParentWithRecognisedUrl() {
		$parameters = array(
			'returnUrl' => 'some.php?arg=1#areaname:999999'
		);
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
		$tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
		$result = Tx_Flux_Utility_ContentManipulator::affectRecordByRequestParameters($record, $parameters, $tceMain);
		$this->assertTrue($result);
		$this->assertSame('areaname', $record['tx_flux_column']);
		$this->assertSame('999999', $record['tx_flux_parent']);
	}

	/**
	 * @test
	 */
	public function affectByRequestParametersAppliesContentAreaAndParentWithRecognisedUrlRelativeToElement() {
		$parameters = array(
			'returnUrl' => 'some.php?arg=1#areaname:999999:-999998'
		);
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
		$oldSorting = $record['sorting'];
		$tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
		$result = Tx_Flux_Utility_ContentManipulator::affectRecordByRequestParameters($record, $parameters, $tceMain);
		$this->assertTrue($result);
		$this->assertSame('areaname', $record['tx_flux_column']);
		$this->assertSame('999999', $record['tx_flux_parent']);
		$this->assertNotSame($oldSorting, $record['sorting']);
	}

}
