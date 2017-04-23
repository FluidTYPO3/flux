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
use FluidTYPO3\Flux\View\PageLayoutView;
use FluidTYPO3\Flux\View\PreviewView;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * PreviewViewTest
 */
class PreviewViewTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function setUp()
    {
        $GLOBALS['TYPO3_DB'] = $this->getMockBuilder(
            'TYPO3\\CMS\\Core\\Database\\DatabaseConnection'
        )->setMethods(
            array('exec_SELECTgetSingleRow', 'exec_SELECTgetRows', 'exec_SELECT_queryArray', 'fetch_assoc', 'sql_fetch_assoc')
        )->getMock();
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetSingleRow')
            ->willReturn(Records::$contentRecordWithoutParentAndWithoutChildren);
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->willReturn(array());
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECT_queryArray')->willReturn($GLOBALS['TYPO3_DB']);
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('fetch_assoc')->willReturn(array());
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('sql_fetch_assoc')->willReturn(array());
        $GLOBALS['BE_USER'] = $this->getMockBuilder('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication')->setMethods(array('calcPerms'))->getMock();
        $GLOBALS['BE_USER']->expects($this->any())->method('calcPerms');
        $GLOBALS['LANG'] = $this->getMockBuilder('TYPO3\\CMS\\Lang\\LanguageService')->setMethods(array('sL'))->getMock();
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
    public function testGetOptionModeReturnsDefaultIfNoValidOptionsFound()
    {
        $instance = $this->createInstance();
        $options = array(PreviewView::OPTION_MODE => 'someinvalidvalue');
        $result = $this->callInaccessibleMethod($instance, 'getOptionMode', $options);
        $this->assertEquals(PreviewView::MODE_APPEND, $result);
    }

    /**
     * @test
     */
    public function testDrawRecordDrawsEachRecord()
    {
        $column = new Form\Container\Column();
        $column->setLabel('test');
        $record = array();
        $instance = $this->getMockBuilder(
            $this->createInstanceClassName()
        )->setMethods(
            array(
                'getRecords',
                'drawRecord',
                'registerTargetContentAreaInSession',
                'drawNewIcon',
                'getInitializedPageLayoutView',
                'configurePageLayoutViewForLanguageMode'
            )
        )->getMock();
        $instance->expects($this->once())->method('getRecords')->willReturn(array(array('foo' => 'bar'), array('bar' => 'foo')));
        $instance->expects($this->exactly(2))->method('drawRecord');
        $instance->expects($this->once())->method('getInitializedPageLayoutView')->willReturn(new PageLayoutView());
        $instance->expects($this->once())->method('drawNewIcon');
        $instance->expects($this->once())->method('registerTargetContentAreaInSession');
        $result = $this->callInaccessibleMethod($instance, 'drawGridColumn', $record, $column);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testDrawRecord()
    {
        $parentRow = array('bar' => 'foo');
        $record = array('foo' => 'bar');
        $column = new Form\Container\Column();
        $view = $this->getMockBuilder('FluidTYPO3\\Flux\\View\\PageLayoutView')->setMethods(array('tt_content_drawHeader'))->getMock();
        $view->expects($this->any())->method('tt_content_drawHeader')
            ->with($record, $this->anything(), $this->anything(), $this->anything());
        $instance = $this->createInstance();
        $result = $this->callInaccessibleMethod($instance, 'drawRecord', $parentRow, $column, $record, $view);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function testGetNewLink()
    {
        $instance = $this->createInstance();
        $result = $this->callInaccessibleMethod($instance, 'getNewLink', array(), 123, 'myareaname');
        $this->assertContains('123', $result);
        $this->assertContains('myareaname', $result);
    }

    /**
     * @test
     */
    public function returnsDefaultsWithoutForm()
    {
        $instance = $this->createInstance();
        $result = $this->callInaccessibleMethod($instance, 'getPreviewOptions');
        $this->assertEquals(array(
            PreviewView::OPTION_MODE => PreviewView::MODE_APPEND,
            PreviewView::OPTION_TOGGLE => true,
        ), $result);
    }

    /**
     * @test
     * @dataProvider getPreviewTestOptions
     * @param array $options
     * @param string $finalAssertionMethod
     * @return void
     */
    public function rendersPreviews(array $options, $finalAssertionMethod)
    {
        $provider = $this->objectManager->get('FluidTYPO3\\Flux\\Provider\\Provider');
        $form = Form::create(array('name' => 'test', 'options' => array('preview' => $options)));
        $grid = Form\Container\Grid::create(array());
        $grid->createContainer('Row', 'row')->createContainer('Column', 'column');
        $provider->setGrid($grid);
        $provider->setForm($form);
        $provider->setTemplatePaths(array());
        $provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW));
        $databaseConnectionMock = $this->getMockBuilder(DatabaseConnection::class)->getMock();
        $databaseConnectionMock->expects($this->any())->method('sql_fetch_assoc')->willReturn([]);
        $pageLayoutView = $this->getMockBuilder(PageLayoutView::class)->setMethods(['initializeLanguages'])->getMock();
        $previewView = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(array('registerTargetContentAreaInSession', 'getDatabaseConnection', 'getPageLayoutView'))
            ->getMock();
        $previewView->expects($this->any())->method('getDatabaseConnection')->willReturn($databaseConnectionMock);
        $previewView->expects($this->any())->method('registerTargetContentAreaInSession');
        $previewView->expects($this->any())->method('getPageLayoutView')->willReturn($pageLayoutView);
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
    public function avoidsRenderPreviewSectionIfTemplateFileDoesNotExist()
    {
        $provider = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('getTemplatePathAndFilename'))->getMock();
        $provider->expects($this->atLeastOnce())->method('getTemplatePathAndFilename')->willReturn(null);
        $previewView = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('dummy'))->getMock();
        $this->callInaccessibleMethod($previewView, 'renderPreviewSection', $provider, array());
    }

    /**
     * @param string $preview
     * @return void
     */
    protected function assertPreviewIsEmpty($preview)
    {
        $this->assertEquals('Preview text', $preview);
    }

    /**
     * @param string $preview
     * @return void
     */
    protected function assertPreviewComesAfterGrid($preview)
    {
        $this->assertStringStartsNotWith('Preview text', $preview);
    }

    /**
     * @param string $preview
     * @return void
     */
    protected function assertPreviewComesBeforeGrid($preview)
    {
        $this->assertStringStartsWith('Preview text', $preview);
    }

    /**
     * @param string $preview
     * @return void
     */
    protected function assertPreviewContainsToggle($preview)
    {
        $this->assertStringStartsWith('<div class="grid-visibility-toggle" ', $preview);
    }

    /**
     * @return array
     */
    public function getPreviewTestOptions()
    {
        return array(
            array(
                array(PreviewView::OPTION_MODE => PreviewView::MODE_NONE, PreviewView::OPTION_TOGGLE => false),
                'assertPreviewIsEmpty'
            ),
            array(
                array(PreviewView::OPTION_MODE => PreviewView::MODE_PREPEND, PreviewView::OPTION_TOGGLE => false),
                'assertPreviewComesAfterGrid'
            ),
            array(
                array(PreviewView::OPTION_MODE => PreviewView::MODE_APPEND, PreviewView::OPTION_TOGGLE => false),
                'assertPreviewComesBeforeGrid'
            ),
            array(
                array(PreviewView::OPTION_MODE => PreviewView::MODE_PREPEND, PreviewView::OPTION_TOGGLE => true),
                'assertPreviewContainsToggle'
            )
        );
    }

    /**
     * @test
     */
    public function configurePageLayoutViewForLanguageModeSetsSpecialVariablesInLanguageMode()
    {
        $languageService = $this->getMockBuilder('TYPO3\\CMS\\Lang\\LanguageService')->setMethods(array('getLL'))->getMock();
        $languageService->expects($this->once())->method('getLL');
        $view = $this->getMockBuilder('FluidTYPO3\\Flux\\View\\PageLayoutView')->setMethods(array('initializeLanguages'))->getMock();
        $view->expects($this->once())->method('initializeLanguages');
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getPageModuleSettings', 'getLanguageService'))->getMock();
        $instance->expects($this->once())->method('getPageModuleSettings')->willReturn(array('function' => 2));
        $instance->expects($this->once())->method('getLanguageService')->willReturn($languageService);
        $result = $this->callInaccessibleMethod($instance, 'configurePageLayoutViewForLanguageMode', $view);
        $this->assertSame($view, $result);
        $this->assertEquals(1, $result->tt_contentConfig['languageMode']);
    }

    /**
     * @test
     */
    public function testParseGridColumnTemplate()
    {
        $column = $this->getMockBuilder('FluidTYPO3\\Flux\\Form\\Container\\Column')->setMethods(array('getColspan', 'getRowspan', 'getStyle'))->getMock();
        $column->expects($this->once())->method('getColSpan')->willReturn('foobar-colSpan');
        $column->expects($this->once())->method('getRowSpan')->willReturn('foobar-rowSpan');
        $column->expects($this->once())->method('getStyle')->willReturn('foobar-style');
        $subject = $this->getMockBuilder('FluidTYPO3\\Flux\\View\\PreviewView')->setMethods(array('drawNewIcon', 'drawPasteIcon'))->getMock();
        $subject->expects($this->once())->method('drawNewIcon');
        $this->callInaccessibleMethod($subject, 'parseGridColumnTemplate', array(), $column, 'f-target', 2, 'f-content');
    }

    /**
     * @return object
     */
    protected function createInstance()
    {
        $instance = $this->getMockBuilder(PreviewView::class)->setMethods(['configurePageLayoutViewForLanguageMode'])->getMock();
        $instance->expects($this->any())->method('configurePageLayoutViewForLanguageMode')->willReturnArgument(0);
        return $instance;
    }
}
