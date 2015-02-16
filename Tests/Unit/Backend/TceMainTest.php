<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\TceMain;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dbal\Database\DatabaseConnection;
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
		$GLOBALS['TYPO3_DB'] = $this->getMock(DatabaseConnection::class, array('exec_SELECTgetSingleRow'), array(), '', FALSE);
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->willReturn(FALSE);
		$GLOBALS['TCA'] = array(
			'tt_content' => array(
				'columns' => array(
					'pi_flexform' => array()
				)
			)
		);
	}

	/**
	 * @test
	 */
	public function canExecuteClearAllCacheCommandAndPassToProvidersForEveryTcaTable() {
		$instance = $this->getInstance();
		$mockedFluxService = $this->getMock(FluxService::class, array('resolveConfigurationProviders'));
		$mockedProvider = $this->getMock(Provider::class, array('clearCacheCommand'));
		$expectedExecutions = count($GLOBALS['TCA']);
		$mockedProvider->expects($this->exactly($expectedExecutions))->method('clearCacheCommand')->with('all');
		$mockedFluxService->expects($this->atLeastOnce())->method('resolveConfigurationProviders')->will($this->returnValue(array($mockedProvider)));
		ObjectAccess::setProperty($instance, 'configurationService', $mockedFluxService, TRUE);
		$instance->clearCacheCommand('all');
	}

	/**
	 * @test
	 */
	public function canExecuteClearAllCacheCommandTwiceWithoutDoubleCalling() {
		$instance = $this->getInstance();
		$mockedFluxService = $this->getMock(FluxService::class, array('resolveConfigurationProviders'));
		$mockedFluxService->expects($this->atLeastOnce())->method('resolveConfigurationProviders')->will($this->returnValue(array()));
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
		$instance->processDatamap_preProcessFieldArray($record, 'tt_content', $record['uid'], $tceMainParent);
	}

	/**
	 * @test
	 */
	public function canExecuteDataPreProcessHookWithoutRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = array();
		$instance->processDatamap_preProcessFieldArray($record, 'tt_content', NULL, $tceMainParent);
	}

	/**
	 * @test
	 */
	public function canExecuteDataPostProcessHook() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$instance->processDatamap_postProcessFieldArray('update', 'tt_content', $record['uid'], $record, $tceMainParent);
	}

	/**
	 * @test
	 */
	public function canExecuteDataPostProcessHookWithoutRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = array();
		$instance->processDatamap_postProcessFieldArray('update', 'tt_content', NULL, $record, $tceMainParent);
	}

	/**
	 * @test
	 */
	public function canExecuteAfterDatabaseOperationHook() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$instance->processDatamap_afterDatabaseOperations('update', 'tt_content', $record['uid'], $record, $tceMainParent);
	}

	/**
	 * @test
	 */
	public function canExecuteAfterDatabaseOperationHookWithoutRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = array();
		$instance->processDatamap_afterDatabaseOperations('update', 'tt_content', NULL, $record, $tceMainParent);
	}

	/**
	 * @test
	 */
	public function canExecuteAfterDatabaseOperationHookWithNewRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = array(
			'hidden' => 0
		);
		$instance->processDatamap_afterDatabaseOperations('update', 'tt_content', 'NEW4cds44', $record, $tceMainParent);
	}

	/**
	 * @test
	 */
	public function canExecuteCommandPreProcessHook() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$command = 'update';
		$instance->processCmdmap_preProcess($command, 'tt_content', $record['uid'], $record, $tceMainParent);
	}

	/**
	 * @test
	 */
	public function canExecuteCommandPreProcessHookWithNullRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = NULL;
		$command = 'update';
		$instance->processCmdmap_preProcess($command, 'tt_content', 'NEW532cf4', $record, $tceMainParent);
	}

	/**
	 * @test
	 */
	public function canExecuteCommandPostProcessHook() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$command = 'update';
		$instance->processCmdmap_postProcess($command, 'tt_content', $record['uid'], $record, $tceMainParent);
	}

	/**
	 * @return \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected function getCallerInstance() {
		/** @var \TYPO3\CMS\Core\DataHandling\DataHandler $tceMainParent */
		$tceMainParent = GeneralUtility::makeInstance(DataHandler::class);
		return $tceMainParent;
	}

	/**
	 * @return TceMain
	 */
	protected function getInstance() {
		/** @var ObjectManager $objectManager */
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		/** @var TceMain $tceMainInstance */
		$tceMainInstance = $objectManager->get(TceMain::class);
		ObjectAccess::setProperty($tceMainInstance, 'cachesCleared', FALSE, TRUE);
		return $tceMainInstance;
	}

	/**
	 * @test
	 */
	public function executeConfigurationProviderMethodDebugsOnException() {
		$exception = new \RuntimeException();
		$mock = $this->getMock($this->createInstanceClassName(), array('detectUniqueProviders'));
		$mock->expects($this->once())->method('detectUniqueProviders')->will($this->throwException($exception));
		$configurationService = $this->getMock(FluxService::class, array('debug'));
		$configurationService->expects($this->once())->method('debug')->with($exception);
		$handler = new DataHandler();
		$record = array();
		$parameters = array();
		$handler->substNEWwithIDs['NEW123'] = 123;
		ObjectAccess::setProperty($mock, 'configurationService', $configurationService, TRUE);
		$this->callInaccessibleMethod($mock, 'executeConfigurationProviderMethod',
			'method', 'tt_content', 'NEW123', $record, $parameters, $handler);
	}

	/**
	 * @test
	 * @dataProvider getResolveRecordUidTestValues
	 * @param mixed $input
	 * @param mixed $handlerInput
	 * @param integer $expectedOutput
	 */
	public function testResolveRecordUid($input, $handlerInput, $expectedOutput) {
		$instance = $this->getMock($this->createInstanceClassName());
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
		return array(
			array('123', NULL, 123),
			array('NEW123', '123', 123),
			array('', NULL, 0)
		);
	}

	/**
	 * @test
	 */
	public function detectUniqueProvidersReturnsExpectedValue() {
		$mock = $this->getMock($this->createInstanceClassName());
		$provider1 = $this->getMock(Provider::class);
		$provider2 = $this->getMock(Provider::class);
		$provider3 = $this->getMock(Provider::class);
		$provider4 = $provider1;
		$configurationService = $this->getMock(FluxService::class, array('resolveConfigurationProviders'));
		$configurationService->expects($this->at(0))->method('resolveConfigurationProviders')->will($this->returnValue(array($provider1)));
		$configurationService->expects($this->at(1))->method('resolveConfigurationProviders')->will($this->returnValue(array($provider2)));
		$configurationService->expects($this->at(2))->method('resolveConfigurationProviders')->will($this->returnValue(array($provider3)));
		$configurationService->expects($this->at(3))->method('resolveConfigurationProviders')->will($this->returnValue(array($provider4)));
		ObjectAccess::setProperty($mock, 'configurationService', $configurationService, TRUE);
		$record = array('foo' => 'bar', 'baz' => 'oof', 'x' => 'y');
		$result = $this->callInaccessibleMethod($mock, 'detectUniqueProviders', 'table', $record);
		$expected = array(get_class($provider1) => $provider1, get_class($provider2) => $provider2, get_class($provider3) => $provider3);
		$this->assertEquals($expected, $result);
	}

}
