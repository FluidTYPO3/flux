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
use TYPO3\CMS\Backend\View\PageLayoutView;

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
							'items' => array(
								'foo'
							)
						)
					)
				)
			)
		);
	}

	/**
	 * @test
	 */
	public function returnsDefaultsWithoutForm() {
		$instance = $this->createInstance();
		$result = $this->callInaccessibleMethod($instance, 'getPreviewOptions');
		$this->assertEquals(array(
			PreviewView::OPTION_MODE => PreviewView::MODE_APPEND,
			PreviewView::OPTION_TOGGLE => TRUE,
		), $result);
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
		$previewView = $this->getMock($this->createInstanceClassName(), array('registerTargetContentAreaInSession'));
		$previewView->expects($this->any())->method('registerTargetContentAreaInSession');
		$previewView->injectConfigurationService($this->objectManager->get('FluidTYPO3\\Flux\\Service\\FluxService'));
		$previewView->injectConfigurationManager($this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager'));
		$previewView->injectWorkspacesAwareRecordService($this->objectManager->get('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService'));
		$preview = $previewView->getPreview($provider, Records::$contentRecordIsParentAndHasChildren);
		$this->$finalAssertionMethod($preview);
	}

	/**
	 * @param string $preview
	 * @return void
	 */
	protected function assertPreviewIsEmpty($preview) {
		$this->assertEquals('Preview text', $preview);
	}

	/**
	 * @param string $preview
	 * @return void
	 */
	protected function assertPreviewComesAfterGrid($preview) {
		$this->assertStringStartsNotWith('Preview text', $preview);
	}

	/**
	 * @param string $preview
	 * @return void
	 */
	protected function assertPreviewComesBeforeGrid($preview) {
		$this->assertStringStartsWith('Preview text', $preview);
	}

	/**
	 * @param string $preview
	 * @return void
	 */
	protected function assertPreviewContainsToggle($preview) {
		$this->assertStringStartsWith('<div class="grid-visibility-toggle">', $preview);
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

	/**
	 * @test
	 */
	public function configurePageLayoutViewForLanguageModeSetsSpecialVariablesInLanguageMode() {
		$languageService = $this->getMock('TYPO3\\CMS\\Lang\\LanguageService', array('getLL'));
		$languageService->expects($this->once())->method('getLL');
		$view = $this->getMock('TYPO3\\CMS\\Backend\\View\\PageLayoutView', array('initializeLanguages'));
		$view->expects($this->once())->method('initializeLanguages');
		$instance = $this->getMock($this->createInstanceClassName(), array('getPageModuleSettings', 'getLanguageService'));
		$instance->expects($this->once())->method('getPageModuleSettings')->willReturn(array('function' => 2));
		$instance->expects($this->once())->method('getLanguageService')->willReturn($languageService);
		$result = $this->callInaccessibleMethod($instance, 'configurePageLayoutViewForLanguageMode', $view);
		$this->assertSame($view, $result);
		$this->assertEquals(1, $result->tt_contentConfig['languageMode']);
	}

}
