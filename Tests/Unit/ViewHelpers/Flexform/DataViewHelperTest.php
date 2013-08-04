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
class Tx_Flux_ViewHelpers_Flexform_DataViewHelperTest extends Tx_Flux_ViewHelpers_AbstractViewHelperTest {

	/**
	 * @param string $table
	 * @return array
	 */
	protected function getTestingRecordUid($table) {
		$records = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', $table, '1=1', 'uid', 1);
		if (FALSE === is_array($records)) {
			return 0;
		}
		$record = array_pop($records);
		return TRUE === is_array($record) ? array_pop($record) : 0;
	}

	/**
	 * @test
	 */
	public function failsWithInvalidTable() {
		$arguments = array(
			'table' => 'invalid',
			'field' => 'pi_flexform',
			'uid' => $this->getTestingRecordUid('invalid')
		);
		$this->setUseOutputBuffering(TRUE);
		$this->executeViewHelper($arguments);
	}

	/**
	 * @test
	 */
	public function failsWithInvalidField() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'invalid',
			'uid' => $this->getTestingRecordUid('tt_content')
		);
		$this->setUseOutputBuffering(TRUE);
		$output = $this->executeViewHelper($arguments);
		$this->assertContains('Either table', $output);
		$this->assertContains('field', $output);
		$this->assertContains('or record with uid', $output);
		$this->assertContains('do not exist', $output);
	}

	/**
	 * @test
	 */
	public function canExecuteViewHelper() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'pi_flexform',
			'uid' => $this->getTestingRecordUid('tt_content')
		);
		$this->executeViewHelper($arguments);
	}

	/**
	 * @test
	 */
	public function canExecuteViewHelperWithUnregisteredTableAndReturnEmptyArray() {
		$arguments = array(
			'table' => 'be_users',
			'field' => 'uid',
			'uid' => $this->getTestingRecordUid('be_users')
		);
		$output = $this->executeViewHelper($arguments);
		$this->assertIsArray($output);
		$this->assertEmpty($output);
	}

	/**
	 * @test
	 */
	public function canExecuteViewHelperAndTriggerCache() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'pi_flexform',
			'uid' => $this->getTestingRecordUid('tt_content')
		);
		$this->executeViewHelper($arguments);
		$this->executeViewHelper($arguments);
	}

}
