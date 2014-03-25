<?php
namespace FluidTYPO3\Flux\ViewHelpers\Content;
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
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @package Flux
 */
class RenderViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * Setup
	 */
	protected function setUp() {
		parent::setUp();
		$GLOBALS['TSFE'] = new TypoScriptFrontendController($GLOBALS['TYPO3_CONF_VARS'], 1, 0);
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
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$node = new TextNode('Hello loopy world!');
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
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
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
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$node = new TextNode('Hello loopy world!');
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
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$node = new TextNode('Hello loopy world!');
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
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$node = new TextNode('Hello loopy world!');
		$output = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertSame($node->getText(), $output);
	}

}
