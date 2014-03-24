<?php
namespace FluidTYPO3\Flux\Backend;
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class TceMainTest extends \FluidTYPO3\Flux\Tests\Unit\AbstractTestCase {

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

}
