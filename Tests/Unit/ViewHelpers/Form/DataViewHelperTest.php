<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;
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
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;

/**
 * @package Flux
 */
class DataViewHelperTest extends AbstractViewHelperTestCase {

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
		$output = $this->executeViewHelper($arguments);
		$this->assertEquals('Invalid table:field "' . $arguments['table'] . ':' . $arguments['field'] . '" - does not exist in TYPO3 TCA.', $output);
	}

	/**
	 * @test
	 */
	public function failsWithMissingArguments() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'pi_flexform',
		);
		$this->setUseOutputBuffering(TRUE);
		$output = $this->executeViewHelper($arguments);
		$this->assertEquals('Either table "' . $arguments['table'] . '", field "' . $arguments['field'] . '" or record with uid 0 do not exist and you did not manually provide the "row" attribute.', $output);
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
		$this->assertEquals('Invalid table:field "' . $arguments['table'] . ':' . $arguments['field'] . '" - does not exist in TYPO3 TCA.', $output);
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
	public function canUseRecordAsArgument() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'pi_flexform',
			'record' => Records::$contentRecordIsParentAndHasChildren
		);
		$this->executeViewHelper($arguments);
	}

	/**
	 * @test
	 */
	public function canExecuteViewHelperWithUnregisteredTableAndReturnEmptyArray() {
		$arguments = array(
			'table' => 'be_users',
			'field' => 'username',
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

	/**
	 * @test
	 */
	public function supportsAsArgument() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'pi_flexform',
			'uid' => $this->getTestingRecordUid('tt_content'),
			'as' => 'test'
		);
		$this->executeViewHelperUsingTagContent('Text', 'Some text', $arguments);
	}

	/**
	 * @test
	 */
	public function supportsAsArgumentAndBacksUpExistingVariable() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'pi_flexform',
			'uid' => $this->getTestingRecordUid('tt_content'),
			'as' => 'test'
		);
		$this->executeViewHelperUsingTagContent('Text', 'Some text', $arguments, array('test' => 'somevar'));
	}

}
