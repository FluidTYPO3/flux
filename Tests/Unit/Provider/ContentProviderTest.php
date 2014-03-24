<?php
namespace FluidTYPO3\Flux\Provider;
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
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class ContentProviderTest extends AbstractProviderTest {

	/**
	 * @test
	 */
	public function triggersContentManipulatorOnDatabaseOperationNew() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$provider = $this->getConfigurationProviderInstance();
		$tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$provider->postProcessDatabaseOperation('new', $row['uid'], $row, $tceMain);
	}

	/**
	 * @test
	 */
	public function triggersContentManipulatorOnPasteCommandWithCallbackInUrl() {
		$_GET['CB'] = array('paste' => 'tt_content|0');
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$provider = $this->getConfigurationProviderInstance();
		$tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$relativeUid = 0;
		$provider->postProcessCommand('move', 0, $row, $relativeUid, $tceMain);
	}

	/**
	 * @test
	 */
	public function canGetExtensionKey() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$extensionKey = $provider->getExtensionKey($record);
		$this->assertSame('flux', $extensionKey);
	}

	/**
	 * @test
	 */
	public function canGetTableName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$tableName = $provider->getTableName($record);
		$this->assertSame('tt_content', $tableName);
	}

	/**
	 * @test
	 */
	public function canGetCallbackCommand() {
		$instance = $this->createInstance();
		$command = $this->callInaccessibleMethod($instance, 'getCallbackCommand');
		$this->assertIsArray($command);
	}

	/**
	 * @test
	 */
	public function canUpdateRecord() {
		$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid', 'tt_content', '1=1', 1);
		if (FALSE !== $record) {
			$instance = $this->createInstance();
			$this->callInaccessibleMethod($instance, 'updateRecord', $record, $record['uid']);
		}
	}

	/**
	 * @test
	 */
	public function postProcessCommandCallsExpectedMethodToMoveRecord() {
		$mock = $this->getMock(substr(get_class($this), 0, -4), array('getCallbackCommand', 'updateRecord'));
		$mock->expects($this->once())->method('getCallbackCommand')->will($this->returnValue(array('move' => 1)));
		$mock->expects($this->once())->method('updateRecord');
		$mockContentService = $this->getMock('FluidTYPO3\Flux\Service\ContentService', array('pasteAfter', 'moveRecord'));
		$mockContentService->expects($this->once())->method('moveRecord');
		ObjectAccess::setProperty($mock, 'contentService', $mockContentService, TRUE);
		$command = 'move';
		$id = 0;
		$record = $this->getBasicRecord();
		$relativeTo = 0;
		$reference = new DataHandler();
		$mock->postProcessCommand($command, $id, $record, $relativeTo, $reference);
	}

	/**
	 * @test
	 */
	public function postProcessCommandCallsExpectedMethodToCopyRecord() {
		$mock = $this->getMock(substr(get_class($this), 0, -4), array('getCallbackCommand', 'updateRecord'));
		$mock->expects($this->once())->method('getCallbackCommand')->will($this->returnValue(array('paste' => 1)));
		$mock->expects($this->once())->method('updateRecord');
		$mockContentService = $this->getMock('FluidTYPO3\Flux\Service\ContentService', array('pasteAfter', 'moveRecord'));
		$mockContentService->expects($this->once())->method('pasteAfter');
		ObjectAccess::setProperty($mock, 'contentService', $mockContentService, TRUE);
		$command = 'copy';
		$id = 0;
		$record = $this->getBasicRecord();
		$relativeTo = 0;
		$reference = new DataHandler();
		$mock->postProcessCommand($command, $id, $record, $relativeTo, $reference);
	}

	/**
	 * @test
	 */
	public function postProcessCommandCallsExpectedMethodToPasteRecord() {
		$mock = $this->getMock(substr(get_class($this), 0, -4), array('getCallbackCommand', 'updateRecord'));
		$mock->expects($this->once())->method('getCallbackCommand')->will($this->returnValue(array('paste' => 1)));
		$mock->expects($this->once())->method('updateRecord');
		$mockContentService = $this->getMock('FluidTYPO3\Flux\Service\ContentService', array('pasteAfter', 'moveRecord'));
		$mockContentService->expects($this->once())->method('pasteAfter');
		ObjectAccess::setProperty($mock, 'contentService', $mockContentService, TRUE);
		$command = 'move';
		$id = 0;
		$record = $this->getBasicRecord();
		$relativeTo = 0;
		$reference = new DataHandler();
		$mock->postProcessCommand($command, $id, $record, $relativeTo, $reference);
	}

}
