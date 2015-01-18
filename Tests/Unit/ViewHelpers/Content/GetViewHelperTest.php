<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\ViewHelpers\Content\GetViewHelper;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @package Flux
 */
class GetViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * Setup
	 */
	protected function setUp() {
		parent::setUp();
		$GLOBALS['TSFE'] = new TypoScriptFrontendController($GLOBALS['TYPO3_CONF_VARS'], 0, 0, 1);
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('exec_SELECTgetRows'), array(), '', FALSE);
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->will($this->returnValue(array()));
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
		$content = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertIsString($content);
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

	/**
	 * @test
	 */
	public function canReturnArrayOfUnrenderedContentElements() {
		$arguments = array(
			'area' => 'void',
			'render' => FALSE,
			'order' => 'sorting'
		);
		$variables = array(
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$output = $this->executeViewHelper($arguments, $variables);
		$this->assertIsArray($output);
	}

	/**
	 * @test
	 */
	public function canReturnArrayOfRenderedContentElements() {
		$arguments = array(
			'area' => 'void',
			'render' => TRUE,
			'order' => 'sorting'
		);
		$variables = array(
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		);
		$output = $this->executeViewHelper($arguments, $variables);
		$this->assertIsArray($output);
	}

	/**
	 * @test
	 */
	public function canProcessRecords() {
		$this->objectManager->get('FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\DummyConfigurationManager')->getContentObject()
			->expects($this->once())->method('RECORDS');
		$GLOBALS['TSFE']->sys_page = $this->getMock('TYPO3\\CMS\\Frontend\\Page\\PageRepository', array('dummy'), array(), '', FALSE);
		$instance = $this->createInstance();
		$records = array(
			array('uid' => 0),
			array('uid' => 99999999999),
		);
		$output = $this->callInaccessibleMethod($instance, 'getRenderedRecords', $records);
		$this->assertIsArray($output);
	}

}
