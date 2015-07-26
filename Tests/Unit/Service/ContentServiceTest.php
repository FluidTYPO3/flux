<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class ContentServiceTest extends AbstractTestCase {

	/**
	 * @return ContentService
	 */
	protected function createInstance() {
		$class = substr(get_class($this), 0, -4);
		$class = str_replace('Tests\\Unit\\', '', $class);
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
		$methods = ['loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase'];
		$mock = $this->createMock($methods);
		$row = ['uid' => -1];
		$tceMain = $this->getMock('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tceMain->substNEWwithIDs = ['NEW12345' => -1];
		$result = $mock->initializeRecord('NEW12345', $row, $tceMain);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function moveRecordWithNegativeRelativeToValueLoadsRelativeRecordFromDatabaseAndCopiesValuesToRecordAndSetsColumnPositionAndUpdatesRelativeToValue() {
		$methods = ['loadRecordFromDatabase', 'updateRecordInDatabase', 'getTargetAreaStoredInSession'];
		$mock = $this->createMock($methods);
		$row = [
			'pid' => 1
		];
		$relativeRecord = [
			'tx_flux_column' => 2,
			'tx_flux_parent' => 2,
			'colPos' => ContentService::COLPOS_FLUXCONTENT
		];
		$relativeTo = -1;
		$tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$mock->expects($this->once())->method('loadRecordFromDatabase')->with(1)->will($this->returnValue($relativeRecord));
		$mock->expects($this->once())->method('updateRecordInDatabase');
		$mock->moveRecord($row, $relativeTo, [], $tceMain);
		$this->assertEquals($relativeRecord['tx_flux_column'], $row['tx_flux_column']);
		$this->assertEquals($relativeRecord['tx_flux_parent'], $row['tx_flux_parent']);
		$this->assertEquals($relativeRecord['colPos'], $row['colPos']);
		$this->assertEquals(-1, $relativeTo);
	}

	public function pasteAfterAsCopyRelativeToRecord() {
		$methods = ['loadRecordFromDatabase', 'updateRecordInDatabase'];
		$mock = $this->createMock($methods);
		$command = 'copy';
		$row = [
			'uid' => 1
		];
		$copiedRow = [
			'uid' => 3,
			'sorting' => 0,
			'pid' => 1,
			'tx_flux_column' => '',
			'tx_flux_parent' => ''
		];
		$parameters = [
			1,
			-2
		];
		$tceMain = new DataHandler();
		$tceMain->copyMappingArray['tt_content'][1] = $copiedRow['uid'];
		$mock->expects($this->at(0))->method('loadRecordFromDatabase')->with($copiedRow['uid'])->will($this->returnValue($copiedRow));
		$result = $mock->pasteAfter($command, $row, $parameters, $tceMain);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function pasteAfterAsReferenceRelativeToRecord() {
		$methods = ['loadRecordFromDatabase', 'updateRecordInDatabase'];
		$mock = $this->createMock($methods);

		$command = 'copy';
		$row = [
			'uid' => 1
		];
		$copiedRow = [
			'uid' => 3,
			'sorting' => 0,
			'pid' => 1,
			'tx_flux_column' => '',
			'tx_flux_parent' => ''
		];
		$parameters = [
			1,
			'1-reference-2-2-0'
		];
		$cmdMap = [
			'tt_content' => [
				$copiedRow['uid'] => [
					$row['uid'] => 'copy'],
			],
		];
		$tceMain = new DataHandler();
		$tceMain->copyMappingArray['tt_content'][1] = $copiedRow['uid'];
		$tceMain->cmdmap = $cmdMap;
		$mock->expects($this->any())->method('loadRecordFromDatabase')->will($this->returnValue($copiedRow));
		$result = $mock->pasteAfter($command, $row, $parameters, $tceMain);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function pasteAfterAsMoveRelativeToRecord() {
		$methods = ['loadRecordFromDatabase', 'updateRecordInDatabase'];
		$mock = $this->createMock($methods);
		$command = 'move';
		$row = [
			'uid' => 1
		];
		$parameters = [
			1,
			1,
		];
		$tceMain = new DataHandler();
		$mock->expects($this->never())->method('loadRecordFromDatabase');
		$result = $mock->pasteAfter($command, $row, $parameters, $tceMain);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function pasteAfterAsMoveIntoContentArea() {
		$methods = ['loadRecordFromDatabase', 'updateRecordInDatabase'];
		$mock = $this->createMock($methods);
		$command = 'move';
		$row = [
			'uid' => 1
		];
		$parameters = [
			1,
			'1-move-2-2-area-1',
		];
		$tceMain = new DataHandler();
		$mock->expects($this->any())->method('loadRecordFromDatabase')->will($this->returnValue($row));
		$result = $mock->pasteAfter($command, $row, $parameters, $tceMain);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function loadRecordsFromDatabaseDelegatesToRecordService() {
		$mock = new ContentService();
		/** @var WorkspacesAwareRecordService $mockService */
		$mockService = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', ['get']);
		$mockService->expects($this->once())->method('get')->with('tt_content', '*', "tx_flux_parent = '123'");
		$mock->injectWorkspacesAwareRecordService($mockService);
		$this->callInaccessibleMethod($mock, 'loadRecordsFromDatabase', 123);
	}

	/**
	 * @test
	 */
	public function testLoadRecordFromDatabaseWithLanguageUidZero() {
		$mock = new ContentService();
		/** @var WorkspacesAwareRecordService $mockService */
		$mockService = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', ['getSingle']);
		$mockService->expects($this->once())->method('getSingle')->with('tt_content', '*');
		$mock->injectWorkspacesAwareRecordService($mockService);
		$this->callInaccessibleMethod($mock, 'loadRecordFromDatabase', 123, 0);
	}

	/**
	 * @test
	 */
	public function testLoadRecordFromDatabaseWithLanguageUidNotZero() {
		$mock = new ContentService();
		/** @var WorkspacesAwareRecordService $mockService */
		$mockService = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', ['getSingle']);
		$mockService->expects($this->once())->method('getSingle')->with('tt_content', '*');
		$mock->injectWorkspacesAwareRecordService($mockService);
		$this->callInaccessibleMethod($mock, 'loadRecordFromDatabase', 123, 321);
	}

	/**
	 * @test
	 */
	public function testUpdateRecordInDatabaseWithVersionedRecord() {
		$row = [
			'uid' => 123,
			't3ver_oid' => 321,
			'tx_flux_parent' => '3',
			'tx_flux_column' => 'area'
		];
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\RecordService', ['getSingle']);
		$recordService->expects($this->at(0))->method('getSingle')->will($this->returnValue($row));
		$recordService->expects($this->at(1))->method('getSingle')->will($this->returnValue([]));
		$workspaceSercice = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', ['update']);
		$workspaceSercice->expects($this->exactly(2))->method('update');
		$mock = $this->objectManager->get($this->createInstanceClassName());
		$mock->injectRecordService($recordService);
		$mock->injectWorkspacesAwareRecordService($workspaceSercice);
		$this->callInaccessibleMethod($mock, 'updateRecordInDatabase', $row);
	}

	/**
	 * @test
	 */
	public function testUpdateMovePlaceholderWithPlaceholder() {
		$row = ['tx_flux_parent' => '', 'tx_flux_column' => '', 'colPos' => 0, 'uid' => 123];
		$mock = $this->getMock($this->createInstanceClassName(), ['getMovePlaceholder', 'updateRecordInDatabase']);
		$mock->expects($this->once())->method('getMovePlaceholder')->will($this->returnValue($row));
		$mock->expects($this->once())->method('updateRecordInDatabase');
		$this->callInaccessibleMethod($mock, 'updateMovePlaceholder', $row);
	}

	/**
	 * @test
	 * @dataProvider getLanguageInitializationTestValues
	 * @param integer $newUid
	 * @param integer $oldUid
	 * @param integer $newLanguageUid
	 * @param boolean $expectsInitialization
	 */
	public function testInitializeRecordByNewAndOldAndLanguageUids($newUid, $oldUid, $newLanguageUid, $expectsInitialization) {
		$mock = $this->getMock($this->createInstanceClassName(), ['loadRecordFromDatabase', 'updateRecordInDatabase']);
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', ['get']);
		$recordService->expects($this->any())->method('get')->willReturn(NULL);
		$mock->injectWorkspacesAwareRecordService($recordService);
		$dataHandler = $this->getMock('TYPO3\\CMS\\Core\\DataHandling\\DataHandler', ['resorting']);
		$row = ['pid' => 1, 'uid' => 1, 'language' => 1];
		$mock->expects($this->once())->method('loadRecordFromDatabase')->will($this->returnValue($row));
		if (TRUE === $expectsInitialization) {
			$mock->expects($this->once())->method('updateRecordInDatabase');
			$dataHandler->expects($this->once())->method('resorting');
		} else {
			$mock->expects($this->never())->method('updateRecordInDatabase');
			$dataHandler->expects($this->never())->method('resorting');
		}
		$this->callInaccessibleMethod($mock, 'initializeRecordByNewAndOldAndLanguageUids',
			$row, $newUid, $oldUid, $newLanguageUid, 'language', $dataHandler);
	}

	/**
	 * @return array
	 */
	public function getLanguageInitializationTestValues() {
		return [
			[1, 2, 2, TRUE],
			[1, 2, 1, FALSE]
		];
	}

	/**
	 * @test
	 * @dataProvider getMoveRecordTestValues
	 * @param array $parameters
	 * @param integer $relativeTo
	 */
	public function testMoveRecord($parameters, $relativeTo) {
		$row = [

		];
		$mock = $this->getMock($this->createInstanceClassName(),
			['loadRecordFromDatabase', 'updateRecordInDatabase', 'updateMovePlaceholder', 'getTargetAreaStoredInSession']);
		$mock->expects($this->any())->method('loadRecordFromDatabase')->will($this->returnValue($row));
		$mock->expects($this->any())->method('updateRecordInDatabase');
		$mock->expects($this->any())->method('updateMovePlaceholder');
		$dataHandler = $this->getMock('TYPO3\\CMS\\Core\\DataHandling\\DataHandler', ['resorting']);
		$dataHandler->expects($this->any())->method('resorting');
		$result = $mock->moveRecord($row, $relativeTo, $parameters, $dataHandler);
		$this->assertNull($result);
	}

	/**
	 * @return array
	 */
	public function getMoveRecordTestValues() {
		return [
			[['', 'prefix-column-prefix2-unused-unused-top-1-area'], 1],
			[['', 'prefix-column-prefix2-unused-unused-top-1-area'], -1],
			[['', 'colpos-column-page-unused-unused-top-1-area'], 1],
			[['', 'colpos-column-page-unused-unused-top-1-area'], 0 - MiscellaneousUtility::UNIQUE_INTEGER_OVERHEAD - 1],
		];
	}

	/**
	 * @param array $functions
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	protected function createMock($functions = []) {
		$class = substr(get_class($this), 0, -4);
		$class = str_replace('Tests\\Unit\\', '', $class);
		$mock = $this->getMock($class, $functions);
		return $mock;
	}

}
