<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Object;
use FluidTYPO3\Flux\ViewHelpers\Content\GetViewHelper;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

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
		$GLOBALS['TSFE']->cObj = new ContentObjectRenderer();
		$GLOBALS['TSFE']->sys_page = new PageRepository();
		$GLOBALS['TT'] = new NullTimeTracker();
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', ['exec_SELECTgetRows'], [], '', FALSE);
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->will($this->returnValue([]));
		$GLOBALS['TCA']['tt_content']['ctrl'] = [];
	}

	/**
	 * @test
	 */
	public function canRenderViewHelper() {
		$arguments = [
			'area' => 'void',
			'as' => 'records',
			'order' => 'sorting'
		];
		$variables = [
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		];
		$node = new TextNode('Hello loopy world!');
		$output = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertSame($node->getText(), $output);
	}

	/**
	 * @test
	 */
	public function canRenderViewHelperWithLoadRegister() {
		$arguments = [
			'area' => 'void',
			'as' => 'records',
			'order' => 'sorting',
			'loadRegister' => [
				'maxImageWidth' => 300
			]
		];
		$variables = [
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		];
		$node = new TextNode('Hello loopy world!');
		$output = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertSame($node->getText(), $output);
	}

	/**
	 * @test
	 */
	public function canRenderViewHelperWithExistingAsArgumentAndTakeBackup() {
		$arguments = [
			'area' => 'void',
			'as' => 'nameTaken',
			'order' => 'sorting'
		];
		$variables = [
			'nameTaken' => 'taken',
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		];
		$node = new TextNode('Hello loopy world!');
		$content = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertIsString($content);
	}

	/**
	 * @test
	 */
	public function canRenderViewHelperWithNonExistingAsArgument() {
		$arguments = [
			'area' => 'void',
			'as' => 'freevariablename',
			'order' => 'sorting'
		];
		$variables = [
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		];
		$node = new TextNode('Hello loopy world!');
		$output = $this->executeViewHelper($arguments, $variables, $node);
		$this->assertSame($node->getText(), $output);
	}

	/**
	 * @test
	 */
	public function canReturnArrayOfUnrenderedContentElements() {
		$arguments = [
			'area' => 'void',
			'render' => FALSE,
			'order' => 'sorting'
		];
		$variables = [
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		];
		$output = $this->executeViewHelper($arguments, $variables);
		$this->assertIsArray($output);
	}

	/**
	 * @test
	 */
	public function canReturnArrayOfRenderedContentElements() {
		$arguments = [
			'area' => 'void',
			'render' => TRUE,
			'order' => 'sorting'
		];
		$variables = [
			'record' => Records::$contentRecordWithoutParentAndWithoutChildren
		];
		$output = $this->executeViewHelper($arguments, $variables);
		$this->assertIsArray($output);
	}

	/**
	 * @test
	 */
	public function canProcessRecords() {
		$configurationManager = $this->getMock('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager', ['getContentObject']);
		$contentObject = $this->getMock('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer', ['cObjGetSingle']);
		$contentObject->expects($this->any())->method('cObjGetSingle');
		$configurationManager->expects($this->any())->method('getContentObject')->willReturn($contentObject);
		$GLOBALS['TSFE']->sys_page = $this->getMock('TYPO3\\CMS\\Frontend\\Page\\PageRepository', ['dummy'], [], '', FALSE);
		$instance = $this->createInstance();
		$instance->injectConfigurationManager($configurationManager);
		$records = [
			['uid' => 0],
			['uid' => 99999999999],
		];
		$output = $this->callInaccessibleMethod($instance, 'getRenderedRecords', $records);
		$this->assertIsArray($output);
	}

}
