<?php
namespace FluidTYPO3\Flux\Tests\Unit\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\Preview;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\View\PreviewView;
use FluidTYPO3\Flux\View\PageLayoutView;
use TYPO3\CMS\Core\Versioning\VersionState;

/**
 * PreviewViewTest
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
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetSingleRow')
			->willReturn(Records::$contentRecordWithoutParentAndWithoutChildren);
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
	public function testGetOptionModeReturnsDefaultIfNoValidOptionsFound() {
		$instance = $this->createInstance();
		$options = array(PreviewView::OPTION_MODE => 'someinvalidvalue');
		$result = $this->callInaccessibleMethod($instance, 'getOptionMode', $options);
		$this->assertEquals(PreviewView::MODE_APPEND, $result);
	}

	/**
	 * @test
	 */
	public function testDrawRecordDrawsEachRecord() {
		$column = new Form\Container\Column();
		$column->setLabel('test');
		$record = array();
		$instance = $this->getMock(
			$this->createInstanceClassName(),
			array(
				'getRecords',
				'drawRecord',
				'registerTargetContentAreaInSession',
				'drawNewIcon',
				'getInitializedPageLayoutView'
			)
		);
		$instance->expects($this->once())->method('getRecords')->willReturn(array(array('foo' => 'bar'), array('bar' => 'foo')));
		$instance->expects($this->exactly(2))->method('drawRecord');
		$instance->expects($this->once())->method('getInitializedPageLayoutView')->willReturn(new PageLayoutView());
		$instance->expects($this->once())->method('drawNewIcon');
		$instance->expects($this->once())->method('registerTargetContentAreaInSession');
		$result = $this->callInaccessibleMethod($instance, 'drawGridColumn', $record, $column);
		$this->assertNotEmpty($result);
	}

	/**
	 * @dataProvider getWorkspaceVersionOfRecordOrRecordItselfTestValues
	 * @param array $record
	 * @param $workspaceId
	 * @param array $expected
	 */
	public function testGetWorkspaceVersionOfRecordOrRecordItself(array $record, $workspaceId, array $expected) {
		$instance = $this->getMock($this->createInstanceClassName(), array('getActiveWorkspaceId'));
		$instance->expects($this->once())->method('getActiveWorkspaceId')->willReturn($workspaceId);
		$result = $this->callInaccessibleMethod($instance, 'getWorkspaceVersionOfRecordOrRecordItself', $record);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getWorkspaceVersionOfRecordOrRecordItselfTestValues() {
		return array(
			array(array(), 0, array()),
			array(array(), 1, array())
		);
	}

	/**
	 * @test
	 */
	public function testDrawRecord() {
		$parentRow = array('bar' => 'foo');
		$record = array('foo' => 'bar');
		$column = new Form\Container\Column();
		$view = $this->getMock('FluidTYPO3\\Flux\\View\\PageLayoutView', array('tt_content_drawHeader'));
		$view->expects($this->any())->method('tt_content_drawHeader')
			->with($record, $this->anything(), $this->anything(), $this->anything());
		$instance = $this->createInstance();
		$result = $this->callInaccessibleMethod($instance, 'drawRecord', $parentRow, $column, $record, $view);
		$this->assertNotEmpty($result);
	}

	/**
	 * @test
	 */
	public function testGetNewLink() {
		$instance = $this->createInstance();
		$result = $this->callInaccessibleMethod($instance, 'getNewLink', array(), 123, 'myareaname');
		$this->assertContains('123', $result);
		$this->assertContains('myareaname', $result);
	}

	/**
	 * @dataProvider getProcessRecordOverlaysTestValues
	 * @param array $input
	 * @param array $expected
	 */
	public function testProcessRecordOverlays(array $input, array $expected) {
		$instance = $this->getMock($this->createInstanceClassName(), array('getWorkspaceVersionOfRecordOrRecordItself'));
		$instance->expects($this->any())->method('getWorkspaceVersionOfRecordOrRecordItself')->willReturnArgument(0);
		$view = new PageLayoutView();
		$result = $this->callInaccessibleMethod($instance, 'processRecordOverlays', $input, $view);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getProcessRecordOverlaysTestValues() {
		return array(
			array(array(), array()),
			array(array(array('foo' => 'bar')), array(array('foo' => 'bar', 'isDisabled' => FALSE))),
			array(
				array(array('t3ver_state' => VersionState::MOVE_PLACEHOLDER)),
				array(array('t3ver_state' => VersionState::MOVE_PLACEHOLDER, 'isDisabled' => FALSE))
			),
			array(
				array(array('t3ver_state' => VersionState::DELETE_PLACEHOLDER)),
				array()
			),
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
		$previewView->injectConfigurationManager(
			$this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager')
		);
		$previewView->injectWorkspacesAwareRecordService(
			$this->objectManager->get('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService')
		);
		$preview = $previewView->getPreview($provider, Records::$contentRecordIsParentAndHasChildren);
		$this->$finalAssertionMethod($preview);
	}

	/**
	 * @test
	 */
	public function avoidsRenderPreviewSectionIfTemplateFileDoesNotExist() {
		$provider = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', array('getTemplatePathAndFilename'));
		$provider->expects($this->atLeastOnce())->method('getTemplatePathAndFilename')->willReturn(NULL);
		$previewView = $this->getMock($this->createInstanceClassName(), array('dummy'));
		$this->callInaccessibleMethod($previewView, 'renderPreviewSection', $provider, array());
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
			array(
				array(PreviewView::OPTION_MODE => PreviewView::MODE_NONE, PreviewView::OPTION_TOGGLE => FALSE),
				'assertPreviewIsEmpty'
			),
			array(
				array(PreviewView::OPTION_MODE => PreviewView::MODE_PREPEND, PreviewView::OPTION_TOGGLE => FALSE),
				'assertPreviewComesAfterGrid'
			),
			array(
				array(PreviewView::OPTION_MODE => PreviewView::MODE_APPEND, PreviewView::OPTION_TOGGLE => FALSE),
				'assertPreviewComesBeforeGrid'
			),
			array(
				array(PreviewView::OPTION_MODE => PreviewView::MODE_PREPEND, PreviewView::OPTION_TOGGLE => TRUE),
				'assertPreviewContainsToggle'
			)
		);
	}

	/**
	 * @test
	 */
	public function configurePageLayoutViewForLanguageModeSetsSpecialVariablesInLanguageMode() {
		$languageService = $this->getMock('TYPO3\\CMS\\Lang\\LanguageService', array('getLL'));
		$languageService->expects($this->once())->method('getLL');
		$view = $this->getMock('FluidTYPO3\\Flux\\View\\PageLayoutView', array('initializeLanguages'));
		$view->expects($this->once())->method('initializeLanguages');
		$instance = $this->getMock($this->createInstanceClassName(), array('getPageModuleSettings', 'getLanguageService'));
		$instance->expects($this->once())->method('getPageModuleSettings')->willReturn(array('function' => 2));
		$instance->expects($this->once())->method('getLanguageService')->willReturn($languageService);
		$result = $this->callInaccessibleMethod($instance, 'configurePageLayoutViewForLanguageMode', $view);
		$this->assertSame($view, $result);
		$this->assertEquals(1, $result->tt_contentConfig['languageMode']);
	}

	/**
	 * @test
	 */
	public function testParseGridColumnTemplate() {
		$column = $this->getMock('FluidTYPO3\\Flux\\Form\\Container\\Column', array('getColspan', 'getRowspan', 'getStyle', 'getLabel'));
		$column->expects($this->once())->method('getColSpan')->willReturn('foobar-colSpan');
		$column->expects($this->once())->method('getRowSpan')->willReturn('foobar-rowSpan');
		$column->expects($this->once())->method('getStyle')->willReturn('foobar-style');
		$column->expects($this->once())->method('getLabel')->willReturn('foobar-label');
		$subject = $this->getMock('FluidTYPO3\\Flux\\View\\PreviewView', array('drawNewIcon', 'drawPasteIcon'));
		$subject->expects($this->once())->method('drawNewIcon');
		$mockPageLayoutView = $this->getMock('TYPO3\\CMS\\Backend\\View\\PageLayoutView');
		$this->callInaccessibleMethod($subject, 'parseGridColumnTemplate', array(), $column, 1, $mockPageLayoutView, 'f-target', 2, 'f-content');
	}

}
