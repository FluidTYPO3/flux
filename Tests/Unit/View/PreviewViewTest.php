<?php
namespace FluidTYPO3\Flux\Tests\Unit\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\View\PreviewView;

/**
 * @package Flux
 */
class PreviewViewTest extends AbstractTestCase {

	/**
	 * @return void
	 */
	public function setUp() {
		$GLOBALS['TYPO3_DB'] = $this->getMock(
			'TYPO3\\CMS\\Core\\Database\\DatabaseConnection',
			array('exec_SELECTgetSingleRow', 'exec_SELECTgetRows', 'exec_SELECT_queryArray', 'fetch_assoc')
		);
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetSingleRow')->willReturn(Records::$contentRecordWithoutParentAndWithoutChildren);
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->willReturn(array());
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECT_queryArray')->willReturn($GLOBALS['TYPO3_DB']);
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('fetch_assoc')->willReturn(array());
		$GLOBALS['BE_USER'] = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication', array('calcPerms'));
		$GLOBALS['BE_USER']->expects($this->any())->method('calcPerms');
		$GLOBALS['LANG'] = $this->getMock('TYPO3\\CMS\\Lang\\LanguageService', array('sL'));
		$GLOBALS['LANG']->expects($this->any())->method('sL')->will($this->returnArgument(0));
		$GLOBALS['TCA'] = array(
			'tt_content' => array(
				'columns' => array(
					'CType' => array(
						'config' => array(
							'items' => array()
						)
					)
				)
			)
		);
	}

	/**
	 * @test
	 * @dataProvider getPreviewTestOptions
	 * @param array $options
	 * @param string $finalAssertionMethod
	 * @return void
	 */
	public function rendersPreviews(array $options, $finalAssertionMethod) {
		$provider = $this->objectManager->get('FluidTYPO3\\Flux\\Provider\\Provider');
		$form = Form::create(array('name' => 'test', 'options' => array('preview' => $options)));
		$grid = Form\Container\Grid::create(array());
		$grid->createContainer('Row', 'row')->createContainer('Column', 'column');
		$provider->setGrid($grid);
		$provider->setForm($form);
		$provider->setTemplatePaths(array());
		$provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW));
		$preview = $provider->getPreview(Records::$contentRecordIsParentAndHasChildren);
		$this->$finalAssertionMethod($preview);
	}

	/**
	 * @param array $preview
	 * @return void
	 */
	protected function assertPreviewIsEmpty(array $preview) {
		$this->assertNull($preview[0]);
		$this->assertEquals('Preview text', $preview[1]);
		$this->assertFalse($preview[2]);
	}

	/**
	 * @param array $preview
	 * @return void
	 */
	protected function assertPreviewComesAfterGrid(array $preview) {
		$this->assertNull($preview[0]);
		$this->assertStringStartsNotWith('Preview text', $preview[1]);
		$this->assertFalse($preview[2]);
	}

	/**
	 * @param array $preview
	 * @return void
	 */
	protected function assertPreviewComesBeforeGrid(array $preview) {
		$this->assertNull($preview[0]);
		$this->assertStringStartsWith('Preview text', $preview[1]);
		$this->assertFalse($preview[2]);
	}

	/**
	 * @param array $preview
	 * @return void
	 */
	protected function assertPreviewContainsToggle(array $preview) {
		$this->assertNull($preview[0]);
		$this->assertStringStartsWith('<div class="grid-visibility-toggle">', $preview[1]);
		$this->assertFalse($preview[2]);
	}

	/**
	 * @return array
	 */
	public function getPreviewTestOptions() {
		return array(
			array(array(PreviewView::OPTION_MODE => PreviewView::MODE_NONE, PreviewView::OPTION_TOGGLE => FALSE), 'assertPreviewIsEmpty'),
			array(array(PreviewView::OPTION_MODE => PreviewView::MODE_PREPEND, PreviewView::OPTION_TOGGLE => FALSE), 'assertPreviewComesAfterGrid'),
			array(array(PreviewView::OPTION_MODE => PreviewView::MODE_APPEND, PreviewView::OPTION_TOGGLE => FALSE), 'assertPreviewComesBeforeGrid'),
			array(array(PreviewView::OPTION_MODE => PreviewView::MODE_PREPEND, PreviewView::OPTION_TOGGLE => TRUE), 'assertPreviewContainsToggle')
		);
	}

}
