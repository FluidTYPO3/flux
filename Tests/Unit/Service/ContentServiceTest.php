<?php
namespace FluidTYPO3\Flux\Service;
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
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Flux
 */
class ContentServiceTest extends AbstractTestCase {

	/**
	 * @return ContentService
	 */
	protected function createInstance() {
		$class = substr(get_class($this), 0, -4);
		$instance = $this->objectManager->get($class);
		return $instance;
	}

	/**
	 * @test
	 */
	public function affectByRequestParametersAppliesParent() {
		$parameters['overrideVals']['tt_content']['tx_flux_parent'] = 999999;
		$record = Records::$contentRecordIsParentAndHasChildren;
		$this->assertSame(0, $record['tx_flux_parent']);
		$this->assertSame(0, $record['colPos']);
		$tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$this->createInstance()->affectRecordByRequestParameters('NEW12345', $record, $parameters, $tceMain);
		$this->assertSame(999999, $record['tx_flux_parent']);
		$this->assertSame(ContentService::COLPOS_FLUXCONTENT, $record['colPos']);
	}

	/**
	 * @test
	 */
	public function canInitializeBlankRecord() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$row = array('uid' => -1);
		$tceMain = $this->getMock('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tceMain->substNEWwithIDs = array('NEW12345' => -1);
		$mock->initializeRecord('NEW12345', $row, $tceMain);
	}

	/**
	 * @test
	 */
	public function moveRecordWithNegativeRelativeToValueLoadsRelativeRecordFromDatabaseAndCopiesValuesToRecordAndSetsColumnPositionAndUpdatesRelativeToValue() {
		$methods = array('loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$row = array(
			'pid' => 1
		);
		$relativeRecord = array(
			'tx_flux_column' => 2,
			'tx_flux_parent' => 2,
			'colPos' => ContentService::COLPOS_FLUXCONTENT
		);
		$relativeTo = -1;
		$tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$mock->expects($this->once())->method('loadRecordFromDatabase')->with(1)->will($this->returnValue($relativeRecord));
		$mock->expects($this->once())->method('updateRecordInDatabase');
		$mock->moveRecord($row, $relativeTo, array(), $tceMain);
		$this->assertEquals($relativeRecord['tx_flux_column'], $row['tx_flux_column']);
		$this->assertEquals($relativeRecord['tx_flux_parent'], $row['tx_flux_parent']);
		$this->assertEquals($relativeRecord['colPos'], $row['colPos']);
		$this->assertEquals(-1, $relativeTo);
	}

	public function pasteAfterAsCopyRelativeToRecord() {
		$methods = array('loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$command = 'copy';
		$row = array(
			'uid' => 1
		);
		$copiedRow = array(
			'uid' => 3,
			'sorting' => 0,
			'pid' => 1,
			'tx_flux_column' => '',
			'tx_flux_parent' => ''
		);
		$parameters = array(
			1,
			-2
		);
		$tceMain = new DataHandler();
		$tceMain->copyMappingArray['tt_content'][1] = $copiedRow['uid'];
		$mock->expects($this->at(0))->method('loadRecordFromDatabase')->with($copiedRow['uid'])->will($this->returnValue($copiedRow));
		$mock->pasteAfter($command, $row, $parameters, $tceMain);
	}

	/**
	 * @test
	 */
	public function pasteAfterAsReferenceRelativeToRecord() {
		$methods = array('loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);

		$command = 'copy';
		$row = array(
			'uid' => 1
		);
		$copiedRow = array(
			'uid' => 3,
			'sorting' => 0,
			'pid' => 1,
			'tx_flux_column' => '',
			'tx_flux_parent' => ''
		);
		$parameters = array(
			1,
			'1-reference-2-2-0'
		);
		$cmdMap = array(
			'tt_content' => array(
				$copiedRow['uid'] => array(
					$row['uid'] => 'copy'),
			),
		);
		$tceMain = new DataHandler();
		$tceMain->copyMappingArray['tt_content'][1] = $copiedRow['uid'];
		$tceMain->cmdmap = $cmdMap;
		$mock->expects($this->any())->method('loadRecordFromDatabase')->will($this->returnValue($copiedRow));
		$mock->pasteAfter($command, $row, $parameters, $tceMain);
	}

	/**
	 * @test
	 */
	public function pasteAfterAsMoveRelativeToRecord() {
		$methods = array('loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$command = 'move';
		$row = array(
			'uid' => 1
		);
		$parameters = array(
			1,
			1,
		);
		$tceMain = new DataHandler();
		$mock->expects($this->never())->method('loadRecordFromDatabase');
		$mock->pasteAfter($command, $row, $parameters, $tceMain);
	}

	/**
	 * @test
	 */
	public function pasteAfterAsMoveIntoContentArea() {
		$methods = array('loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$command = 'move';
		$row = array(
			'uid' => 1
		);
		$parameters = array(
			1,
			'1-move-2-2-area-1',
		);
		$tceMain = new DataHandler();
		$mock->expects($this->any())->method('loadRecordFromDatabase')->will($this->returnValue($row));
		$mock->pasteAfter($command, $row, $parameters, $tceMain);
	}

	/**
	 * @param array $functions
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	protected function createMock($functions = array()) {
		$class = substr(get_class($this), 0, -4);
		$mock = $this->getMock($class, $functions);
		return $mock;
	}

}
