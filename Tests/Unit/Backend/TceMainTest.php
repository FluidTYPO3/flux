<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\TceMain;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class TceMainTest extends AbstractTestCase {

	/**
	 * @return void
	 */
	public function setUp() {
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', ['exec_SELECTgetSingleRow'], [], '', FALSE);
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->willReturn(FALSE);
		$GLOBALS['TCA'] = [
			'tt_content' => [
				'columns' => [
					'pi_flexform' => []
				]
			]
		];
	}

	/**
	 * @test
	 */
	public function canExecuteClearAllCacheCommandAndPassToProvidersForEveryTcaTable() {
		$instance = $this->getInstance();
		$mockedFluxService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', ['resolveConfigurationProviders']);
		$mockedProvider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', ['clearCacheCommand']);
		$expectedExecutions = count($GLOBALS['TCA']);
		$mockedProvider->expects($this->exactly($expectedExecutions))->method('clearCacheCommand')->with('all');
		$mockedFluxService->expects($this->atLeastOnce())->method('resolveConfigurationProviders')->will($this->returnValue([$mockedProvider]));
		ObjectAccess::setProperty($instance, 'configurationService', $mockedFluxService, TRUE);
		$instance->clearCacheCommand('all');
	}

	/**
	 * @test
	 */
	public function canExecuteClearAllCacheCommandTwiceWithoutDoubleCalling() {
		$instance = $this->getInstance();
		$mockedFluxService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', ['resolveConfigurationProviders']);
		$mockedFluxService->expects($this->atLeastOnce())->method('resolveConfigurationProviders')->will($this->returnValue([]));
		ObjectAccess::setProperty($instance, 'configurationService', $mockedFluxService, TRUE);
		$instance->clearCacheCommand('all');
		$instance->clearCacheCommand('all');
	}

	/**
	 * @test
	 */
	public function canExecuteDataPreProcessHook() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$result = $instance->processDatamap_preProcessFieldArray($record, 'tt_content', $record['uid'], $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteDataPreProcessHookWithoutRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = [];
		$result = $instance->processDatamap_preProcessFieldArray($record, 'tt_content', NULL, $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteDataPostProcessHook() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$result = $instance->processDatamap_postProcessFieldArray('update', 'tt_content', $record['uid'], $record, $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteDataPostProcessHookWithoutRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = [];
		$result = $instance->processDatamap_postProcessFieldArray('update', 'tt_content', NULL, $record, $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteAfterDatabaseOperationHook() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$result = $instance->processDatamap_afterDatabaseOperations('update', 'tt_content', $record['uid'], $record, $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteAfterDatabaseOperationHookWithoutRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = [];
		$result = $instance->processDatamap_afterDatabaseOperations('update', 'tt_content', NULL, $record, $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteAfterDatabaseOperationHookWithNewRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = [
			'hidden' => 0
		];
		$result = $instance->processDatamap_afterDatabaseOperations('update', 'tt_content', 'NEW4cds44', $record, $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteCommandPreProcessHook() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$command = 'update';
		$result = $instance->processCmdmap_preProcess($command, 'tt_content', $record['uid'], $record, $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteCommandPreProcessHookWithNullRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = NULL;
		$command = 'update';
		$result = $instance->processCmdmap_preProcess($command, 'tt_content', 'NEW532cf4', $record, $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteCommandPostProcessHook() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$command = 'update';
		$result = $instance->processCmdmap_postProcess($command, 'tt_content', $record['uid'], $record, $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @return DataHandler
	 */
	protected function getCallerInstance() {
		/** @var DataHandler $tceMainParent */
		$tceMainParent = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		return $tceMainParent;
	}

	/**
	 * @return TceMain
	 */
	protected function getInstance() {
		/** @var ObjectManager $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$tceMainInstance = new TceMain();
		ObjectAccess::setProperty($tceMainInstance, 'cachesCleared', FALSE, TRUE);
		return $tceMainInstance;
	}

	/**
	 * @test
	 */
	public function executeConfigurationProviderMethodDebugsOnException() {
		$exception = new \RuntimeException();
		$mock = new TceMain();
		$configurationService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['debug', 'resolveConfigurationProviders']);
		$configurationService->expects($this->once())->method('debug')->with($exception);
		$configurationService->expects($this->once())->method('resolveConfigurationProviders')->will($this->throwException($exception));
		$handler = new DataHandler();
		$record = [];
		$parameters = [];
		$handler->substNEWwithIDs['NEW123'] = 123;
		$mock->injectConfigurationService($configurationService);
		$result = $this->callInaccessibleMethod($mock, 'executeConfigurationProviderMethod',
			'method', 'tt_content', 'NEW123', $record, $parameters, $handler);
		$this->assertEmpty($result);
	}

	/**
	 * @test
	 */
	public function executeConfigurationProviderMethodCallsMethodOnProvidersAndTracksExecution() {
		$command = 'postProcessDatabaseOperation';
		$mock = $this->getMock($this->createInstanceClassName(), ['resolveRecordUid', 'ensureRecordDataIsLoaded']);
		$mock->expects($this->once())->method('resolveRecordUid')->willReturn(1);
		$mock->expects($this->once())->method('ensureRecordDataISLoaded')->willReturnArgument(2);
		$caller = $this->getCallerInstance();
		$row = ['uid' => 1];
		$arguments = ['status' => $command, 'id' => 1, 'row' => $row];
		$provider = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', [$command]);
		$provider->expects($this->exactly(1))->method($command);
		$providers = [$provider, $provider];
		$configurationService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['resolveConfigurationProviders']);
		$configurationService->expects($this->once())->method('resolveConfigurationProviders')->willReturn($providers);
		$mock->injectConfigurationService($configurationService);
		$result = $this->callInaccessibleMethod($mock, 'executeConfigurationProviderMethod', $command, 'void', 1, $row, $arguments, $caller);
		$this->assertEquals($row, $result);
	}

	/**
	 * @test
	 * @dataProvider getResolveRecordUidTestValues
	 * @param mixed $input
	 * @param mixed $handlerInput
	 * @param integer $expectedOutput
	 */
	public function testResolveRecordUid($input, $handlerInput, $expectedOutput) {
		$instance = $this->getMock($this->createInstanceClassName(), ['dummy'], [], '', TRUE);
		$dataHandler = new DataHandler();
		if (NULL !== $handlerInput) {
			$dataHandler->substNEWwithIDs[$input] = $handlerInput;
		}
		$result = $this->callInaccessibleMethod($instance, 'resolveRecordUid', $input, $dataHandler);
		$this->assertTrue($expectedOutput === $result, 'Resolved record UID was not expected value');
	}

	/**
	 * @return array
	 */
	public function getResolveRecordUidTestValues() {
		return [
			['123', NULL, 123],
			['NEW123', '123', 123],
			['', NULL, 0]
		];
	}

	/**
	 * @test
	 */
	public function postProcessDatabaseOperationWithNewStatusAndContentTableCallsInitializeRecord() {
		$contentService = $this->getMock('FluidTYPO3\\Flux\\Service\\ContentService', ['initializeRecord']);
		$contentService->expects($this->once())->method('initializeRecord');
		/** @var DataHandler $tceMain */
		$tceMain = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
		$instance = $this->getMock($this->createInstanceClassName(), ['executeConfigurationProviderMethod'], [], '', TRUE);
		$instance->injectContentService($contentService);
		$row = [];
		$instance->processDatamap_afterDatabaseOperations('new', 'tt_content', 1, $row, $tceMain);
	}

}
