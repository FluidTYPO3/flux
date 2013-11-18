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
class Tx_Flux_ViewHelpers_Content_RenderViewHelperTest extends Tx_Flux_ViewHelpers_AbstractViewHelperTest {

	/**
	 * Setup
	 */
	protected function setUp() {
		parent::setUp();
		$GLOBALS['TSFE'] = new tslib_fe($GLOBALS['TYPO3_CONF_VARS'], 1, 0);
	}

	/**
	 * @test
	 */
	public function canRenderViewHelper() {
		$arguments = array(
			'area' => 'void',
			'as' => 'records',
			'order' => 'sorting'
		);
		$variables = array(
			'record' => Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$node = new \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode('Hello loopy world!');
		$output = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertSame($node->getText(), $output);
	}

	/**
	 * @test
	 */
	public function isUnaffectedByRenderArgumentBeingFalse() {
		$arguments = array(
			'area' => 'void',
			'render' => FALSE,
			'order' => 'sorting'
		);
		$variables = array(
			'record' => Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$output = $this->executeViewHelper($arguments, $variables);
		$this->assertIsString($output);
	}

	/**
	 * @test
	 */
	public function canRenderViewHelperWithLoadRegister() {
		$arguments = array(
			'area' => 'void',
			'as' => 'records',
			'order' => 'sorting',
			'loadRegister' => array(
				'maxImageWidth' => 300
			)
		);
		$variables = array(
			'record' => Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$node = new \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode('Hello loopy world!');
		$output = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertSame($node->getText(), $output);
	}

	/**
	 * @test
	 */
	public function canRenderViewHelperWithExistingAsArgumentAndTakeBackup() {
		$arguments = array(
			'area' => 'void',
			'as' => 'nameTaken',
			'order' => 'sorting'
		);
		$variables = array(
			'nameTaken' => 'taken',
			'record' => Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$node = new \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode('Hello loopy world!');
		$output = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertSame($node->getText(), $output);
	}

	/**
	 * @test
	 */
	public function canRenderViewHelperWithNonExistingAsArgument() {
		$arguments = array(
			'area' => 'void',
			'as' => 'freevariablename',
			'order' => 'sorting'
		);
		$variables = array(
			'record' => Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$node = new \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode('Hello loopy world!');
		$output = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertSame($node->getText(), $output);
	}

}
