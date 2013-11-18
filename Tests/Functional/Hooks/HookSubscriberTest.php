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
class Tx_Flux_Tests_Functional_Hook_HookSubscriberTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canExecuteUpdateCommandOnUnrecognisedRecord() {
		$record = $this->getSimpleRecordFixture();
		$this->attemptCommandExecution('update', $record);
		$this->attemptRecordManipulation($record, 'update');
		$this->anything();
	}

	/**
	 * @test
	 */
	public function canExecuteUpdateCommandOnRecognisedRecord() {
		$record = $this->getSimpleRecordFixtureWithSimpleFlexFormSource();
		$this->attemptCommandExecution('update', $record);
		$this->attemptRecordManipulation($record, 'update');
		$this->anything();
	}

	/**
	 * @test
	 */
	public function canExecuteMoveCommandOnUnrecognisedRecord() {
		$record = $this->getSimpleRecordFixture();
		$this->attemptCommandExecution('move', $record);
		$this->anything();
	}

	/**
	 * @test
	 */
	public function canExecuteMoveCommandOnRecognisedRecord() {
		$record = $this->getSimpleRecordFixtureWithSimpleFlexFormSource();
		$this->attemptCommandExecution('move', $record);
		$this->anything();
	}

	/**
	 * @test
	 */
	public function acceptsBasicRecordForModificationHookSubscribers() {
		$record = $this->getSimpleRecordFixture();
		$this->attemptRecordManipulation($record);
		$this->attemptRecordManipulation($record, 'update');
	}

	/**
	 * @param string $command
	 * @param array $record
	 * @param string $table
	 */
	protected function attemptCommandExecution($command, $record, $table = 'tt_content') {
		$id = Tx_Flux_Tests_Fixtures_Data_Records::UID_CONTENT_NOPARENTNOCHILDREN;
		$reference = $this->getTceMainFixture();
		$subscriber = $this->createTceMainHookSubscriberInstance();
		$relativeTo = 0;
		$arguments = array('command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'preProcessCommand', $table, $id, $record, $arguments, $reference);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'postProcessCommand', $table, $id, $record, $arguments, $reference);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'postProcessDatabaseOperation', $table, $id, $record, $arguments, $reference);
		$this->any();
	}

	/**
	 * @param array $record
	 * @param string $status
	 * @param string $table
	 */
	protected function attemptRecordManipulation($record, $status = NULL, $table = 'tt_content') {
		$id = Tx_Flux_Tests_Fixtures_Data_Records::UID_CONTENT_NOPARENTNOCHILDREN;
		$reference = $this->getTceMainFixture();
		$subscriber = $this->createTceMainHookSubscriberInstance();
		$arguments = array('record' => $record, 'table' => $table, 'id' => $id);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'preProcessRecord', $table, $id, $record, $arguments, $reference);
		$arguments = array('status' => $status, 'table' => $table, 'id' => $id, 'record' => $record);
		$this->callInaccessibleMethod($subscriber, 'executeConfigurationProviderMethod', 'postProcessRecord', $table, $id, $record, $arguments, $reference);
		$this->any();
	}

	/**
	 * @return \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected function getTceMainFixture() {
		/** @var $tceMain t3lib_TCEmain */
		$tceMain = $this->objectManager->get('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
		return $tceMain;
	}

	/**
	 * @return array
	 */
	protected function getSimpleRecordFixture() {
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		return $record;
	}

	/**
	 * @return array
	 */
	protected function getSimpleRecordFixtureWithSimpleFlexFormSource() {
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Tx_Flux_Tests_Fixtures_Data_Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		return $record;
	}

	/**
	 * @return Tx_Flux_Backend_TceMain
	 */
	protected function createTceMainHookSubscriberInstance() {
		/** @var $subscriber Tx_Flux_Backend_TceMain */
		$subscriber = $this->getAccessibleMock('Tx_Flux_Backend_TceMain');
		return $subscriber;
	}

}
