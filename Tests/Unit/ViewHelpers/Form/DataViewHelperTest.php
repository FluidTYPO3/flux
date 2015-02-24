<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class DataViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * @return void
	 */
	public static function setUpBeforeClass() {
		$GLOBALS['TCA'] = array(
			'tt_content' => array(
				'columns' => array(
					'pi_flexform' => array()
				)
			),
			'be_users' => array(
				'columns' => array(
					'username' => array()
				)
			),
		);
	}

	/**
	 * @return void
	 */
	public static function tearDownAfterClass() {
		unset($GLOBALS['TCA']);
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		unset($GLOBALS['TYPO3_DB']);
	}

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
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('exec_SELECTgetSingleRow'));
		$GLOBALS['TYPO3_DB']->expects($this->never())->method('exec_SELECTgetSingleRow');
		$output = $viewHelper->initializeArgumentsAndRender();
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
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('exec_SELECTgetSingleRow'));
		$GLOBALS['TYPO3_DB']->expects($this->once())->method('exec_SELECTgetSingleRow');
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
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('exec_SELECTgetSingleRow'));
		$GLOBALS['TYPO3_DB']->expects($this->once())->method('exec_SELECTgetSingleRow');
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
		$result = $this->executeViewHelper($arguments);
		$this->assertIsArray($result);
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

	/**
	 * @test
	 */
	public function readDataArrayFromProvidersOrUsingDefaultMethodCallsConfigurationServiceConvertOnEmptyProviderArray() {
		$mock = $this->createInstance();
		$configurationService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', array('convertFlexFormContentToArray'));
		$providers = array();
		$record = array();
		$field = NULL;
		$mock->injectConfigurationService($configurationService);
		$result = $this->callInaccessibleMethod($mock, 'readDataArrayFromProvidersOrUsingDefaultMethod', $providers, $record, $field);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function readDataArrayFromProvidersOrUsingDefaultMethodUsesProvidersToReadData() {
		$mock = $this->createInstance();
		$provider1 = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', array('getFlexFormValues'));
		$provider1->expects($this->once())->method('getFlexFormValues')->willReturn(array('foo' => array('bar' => 'test')));
		$provider2 = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', array('getFlexFormValues'));
		$provider2->expects($this->once())->method('getFlexFormValues')
			->willReturn(array('foo' => array('bar' => 'test2', 'baz' => 'test'), 'bar' => 'test'));
		$providers = array($provider1, $provider2);
		$record = Records::$contentRecordIsParentAndHasChildren;
		$field = 'pi_flexform';
		$result = $this->callInaccessibleMethod($mock, 'readDataArrayFromProvidersOrUsingDefaultMethod', $providers, $record, $field);
		$this->assertEquals(array('foo' => array('bar' => 'test2', 'baz' => 'test'), 'bar' => 'test'), $result);
	}

}
