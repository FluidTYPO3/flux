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

use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Database\DatabaseConnection;
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
	public function canDetectParentElementAreaFromRecord() {
		$mock = $this->createMock(array('loadRecordFromDatabase'));
		$mock->expects($this->once())->method('loadRecordFromDatabase');
		$result = $mock->detectParentElementAreaFromRecord(0);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canDetectParentUidFromRecord() {
		$mock = $this->createMock(array('loadRecordFromDatabase'));
		$mock->expects($this->once())->method('loadRecordFromDatabase');
		$result = $mock->detectParentUidFromRecord(0);
		$this->assertIsInteger($result);
	}

	/**
	 * @test
	 */
	public function affectByRequestParametersReturnsEarlyWithUnrecognisedUrl() {
		$parameters = array(
			'returnUrl' => 'some.php?arg=1#hascutoffpointbutnovalues'
		);
		$record = Records::$contentRecordIsParentAndHasChildren;
		$tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$result = $this->createInstance()->affectRecordByRequestParameters($record, $parameters, $tceMain);
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function affectByRequestParametersAppliesContentAreaAndParentWithRecognisedUrl() {
		$parameters = array(
			'returnUrl' => 'some.php?arg=1#areaname:999999'
		);
		$record = Records::$contentRecordIsParentAndHasChildren;
		$tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$result = $this->createInstance()->affectRecordByRequestParameters($record, $parameters, $tceMain);
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
		$record = Records::$contentRecordIsParentAndHasChildren;
		$oldSorting = $record['sorting'];
		$tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$result = $this->createInstance()->affectRecordByRequestParameters($record, $parameters, $tceMain);
		$this->assertTrue($result);
		$this->assertSame('areaname', $record['tx_flux_column']);
		$this->assertSame('999999', $record['tx_flux_parent']);
		$this->assertNotSame($oldSorting, $record['sorting']);
	}

	/**
	 * @test
	 */
	public function canLoadRecordsFromDatabase() {
		$instance = $this->createInstance();
		$backup = $GLOBALS['TYPO3_DB'];
		$records = array(Records::$contentRecordWithParentAndWithoutChildren);
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('exec_SELECTgetRows'));
		$GLOBALS['TYPO3_DB']->expects($this->atLeastOnce())->method('exec_SELECTgetRows')->will($this->returnValue($records));
		$result = $this->callInaccessibleMethod($instance, 'loadRecordsFromDatabase', 'uid IN(0)');
		$this->assertEquals($records, $result);
		$GLOBALS['TYPO3_DB'] = $backup;
	}

	/**
	 * @test
	 */
	public function canLoadRecordFromDatabaseByUid() {
		$instance = $this->createInstance();
		$backup = $GLOBALS['TYPO3_DB'];
		$records = array(Records::$contentRecordWithParentAndWithoutChildren);
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('exec_SELECTquery', 'sql_fetch_assoc', 'sql_free_result'));
		$GLOBALS['TYPO3_DB']->expects($this->once())->method('sql_fetch_assoc')->will($this->returnValue($records));
		$result = $this->callInaccessibleMethod($instance, 'loadRecordFromDatabase', 9999999999999);
		$this->assertEquals($records, $result);
		$GLOBALS['TYPO3_DB'] = $backup;
	}

	/**
	 * @test
	 */
	public function canUpdateRecordInDatabase() {
		$instance = $this->createInstance();
		$row = array('uid' => 0);
		$backup = $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('exec_UPDATEquery'));
		$GLOBALS['TYPO3_DB']->expects($this->once())->method('exec_UPDATEquery');
		$this->callInaccessibleMethod($instance, 'updateRecordInDatabase', $row);
		$GLOBALS['TYPO3_DB'] = $backup;
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
	public function canInitializeBlankRecordWithLanguage() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$oldRecord = array(
			'sys_language_uid' => 1
		);
		$mock->expects($this->once())->method('loadRecordFromDatabase')->with(999999999999)->will($this->returnValue($oldRecord));
		$mock->expects($this->once())->method('loadRecordsFromDatabase')->will($this->returnValue(array(
			Records::$contentRecordWithParentAndChildren,
			Records::$contentRecordWithParentAndWithoutChildren
		)));
		$row = array('uid' => -1, 't3_origuid' => 999999999999, 'sys_language_uid' => 1);
		$tceMain = $this->getMock('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tceMain->substNEWwithIDs = array('NEW12345' => -1);
		$mock->initializeRecord('NEW12345', $row, $tceMain);
	}

	/**
	 * @test
	 */
	public function canInitializeBlankRecordWithLanguageInOldRecord() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$oldRecord = array(
			'sys_language_uid' => 1
		);
		$mock->expects($this->once())->method('loadRecordFromDatabase')->with(999999999999)->will($this->returnValue($oldRecord));
		$mock->expects($this->once())->method('loadRecordsFromDatabase')->will($this->returnValue(array(
			Records::$contentRecordWithParentAndChildren,
			Records::$contentRecordWithParentAndWithoutChildren
		)));
		$row = array('uid' => -1, 't3_origuid' => 999999999999);
		$tceMain = $this->getMock('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tceMain->substNEWwithIDs = array('NEW12345' => -1);
		$mock->initializeRecord('NEW12345', $row, $tceMain);
	}

	/**
	 * @test
	 */
	public function canInitializeCopiedRecordWithoutChildren() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$row = array('uid' => -1, 't3_origuid' => 99999999999999);
		$mock = $this->createMock($methods);
		$mock->expects($this->atLeastOnce())->method('loadRecordsFromDatabase');
		$tceMain = $this->getMock('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tceMain->substNEWwithIDs = array('NEW12345' => -1);
		$mock->initializeRecord('NEW12345', $row, $tceMain);
	}

	/**
	 * @test
	 */
	public function canInitializeCopiedRecordWithChildren() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$children = array(
			array('uid' => -1),
			array('uid' => -2)
		);
		$mock = $this->createMock($methods);
		$mock->expects($this->atLeastOnce())->method('loadRecordsFromDatabase')->will($this->returnValue($children));
		$mock->expects($this->exactly(2))->method('updateRecordInDatabase');
		$row = array('uid' => -1, 't3_origuid' => 99999999999999);
		$tceMain = $this->getMock('TYPO3\CMS\Core\DataHandling\DataHandler', array('localize'));
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
		$tceMain = new DataHandler();
		$tceMain->copyMappingArray['tt_content'][1] = $copiedRow['uid'];
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
