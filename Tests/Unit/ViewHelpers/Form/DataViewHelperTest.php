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
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class DataViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * @test
	 */
	public function failsWithInvalidTable() {
		$arguments = array(
			'table' => 'invalid',
			'field' => 'pi_flexform',
			'uid' => 1
		);
		$viewHelper = $this->buildViewHelperInstance($arguments);
		$backup = $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('exec_SELECTgetSingleRow'));
		$GLOBALS['TYPO3_DB']->expects($this->never())->method('exec_SELECTgetSingleRow');
		$output = $viewHelper->initializeArgumentsAndRender();
		$this->assertEquals('Invalid table:field "' . $arguments['table'] . ':' . $arguments['field'] . '" - does not exist in TYPO3 TCA.', $output);
		$GLOBALS['TYPO3_DB'] = $backup;
	}

	/**
	 * @test
	 */
	public function failsWithMissingArguments() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'pi_flexform',
		);
		$output = $this->executeViewHelper($arguments);
		$this->assertEquals('Either table "' . $arguments['table'] . '", field "' . $arguments['field'] . '" or record with uid 0 do not exist and you did not manually provide the "record" attribute.', $output);
	}

	/**
	 * @test
	 */
	public function failsWithInvalidField() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'invalid',
			'uid' => 1
		);
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
			'uid' => 1
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
	public function canUseChildNodeAsRecord() {
		$arguments = array(
			'table' => 'tt_content',
			'field' => 'pi_flexform',
			'uid' => 1
		);
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$content = $this->createNode('Array', $record);
		$viewHelper = $this->buildViewHelperInstance($arguments, array(), $content);
		$output = $viewHelper->initializeArgumentsAndRender();
		$this->assertIsArray($output);
	}

	/**
	 * @test
	 */
	public function canExecuteViewHelperWithUnregisteredTableAndReturnEmptyArray() {
		$arguments = array(
			'table' => 'be_users',
			'field' => 'username',
			'uid' => 1
		);
		$viewHelper = $this->buildViewHelperInstance($arguments);
		$mockRecordService = $this->getMock('FluidTYPO3\Flux\Service\RecordService', array('getSingle'));
		$mockRecordService->expects($this->once())->method('getSingle')->will($this->returnValue(NULL));
		ObjectAccess::setProperty($viewHelper, 'recordService', $mockRecordService, TRUE);
		$output = $viewHelper->initializeArgumentsAndRender();
		$this->assertEquals('Either table "' . $arguments['table'] . '", field "' . $arguments['field'] . '" or record with uid ' . $arguments['uid'] . ' do not exist and you did not manually provide the "record" attribute.', $output);
	}

	/**
	 * @test
	 */
	public function supportsAsArgument() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$row['pi_flexform'] = $row['test'];
		$arguments = array(
			'record' => $row,
			'table' => 'tt_content',
			'field' => 'pi_flexform',
			'as' => 'test'
		);
		$output = $this->executeViewHelperUsingTagContent('Text', 'Some text', $arguments);
		$this->assertEquals($output, 'Some text');
	}

	/**
	 * @test
	 */
	public function supportsAsArgumentAndBacksUpExistingVariable() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$row['pi_flexform'] = $row['test'];
		$arguments = array(
			'record' => $row,
			'table' => 'tt_content',
			'field' => 'pi_flexform',
			'as' => 'test'
		);
		$output = $this->executeViewHelperUsingTagContent('Text', 'Some text', $arguments, array('test' => 'somevar'));
		$this->assertEquals($output, 'Some text');
	}

}
