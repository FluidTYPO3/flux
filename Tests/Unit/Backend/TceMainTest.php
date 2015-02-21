<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class TceMainTest extends AbstractTestCase {

	/**
	 * @return void
	 */
	public function setUp() {
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('exec_SELECTgetSingleRow'), array(), '', FALSE);
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
		$mockedFluxService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('resolveConfigurationProviders'));
		$mockedProvider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', array('clearCacheCommand'));
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
		$mockedFluxService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('resolveConfigurationProviders'));
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
		$result = $instance->processDatamap_preProcessFieldArray($record, 'tt_content', $record['uid'], $tceMainParent);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canExecuteDataPreProcessHookWithoutRecord() {
		$instance = $this->getInstance();
		$tceMainParent = $this->getCallerInstance();
		$record = array();
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
		$record = array();
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
		$record = array();
		$result = $instance->processDatamap_afterDatabaseOperations('update', 'tt_content', NULL, $record, $tceMainParent);
		$this->assertNull($result);
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
	 * @return \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected function getCallerInstance() {
		/** @var \TYPO3\CMS\Core\DataHandling\DataHandler $tceMainParent */
		$tceMainParent = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		return $tceMainParent;
	}

	/**
	 * @return \FluidTYPO3\Flux\Backend\TceMain
	 */
	protected function getInstance() {
		/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var \FluidTYPO3\Flux\Backend\TceMain $tceMainInstance */
		$tceMainInstance = $objectManager->get('FluidTYPO3\Flux\Backend\TceMain');
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
		$configurationService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', array('debug'));
		$configurationService->expects($this->once())->method('debug')->with($exception);
		$handler = new DataHandler();
		$record = array();
		$parameters = array();
		$handler->substNEWwithIDs['NEW123'] = 123;
		ObjectAccess::setProperty($mock, 'configurationService', $configurationService, TRUE);
		$result = $this->callInaccessibleMethod($mock, 'executeConfigurationProviderMethod',
			'method', 'tt_content', 'NEW123', $record, $parameters, $handler);
		$this->assertEmpty($result);
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
		$provider1 = $this->getMock('FluidTYPO3\\Flux\\Provider');
		$provider2 = $this->getMock('FluidTYPO3\\Flux\\Provider');
		$provider3 = $this->getMock('FluidTYPO3\\Flux\\Provider');
		$provider4 = $provider1;
		$configurationService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', array('resolveConfigurationProviders'));
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
