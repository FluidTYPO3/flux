<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * GetViewHelperTest
 */
class GetViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();
        $GLOBALS['TSFE'] = new TypoScriptFrontendController([], 0, 0);
        $GLOBALS['TSFE']->cObj = $this->getMockBuilder(ContentObjectRenderer::class)->setMethods(['getRecords'])->getMock();
        $GLOBALS['TSFE']->sys_page = $this->getMockBuilder(PageRepository::class)->setMethods(['enableFields'])->getMock();
        $GLOBALS['TCA']['tt_content']['ctrl'] = array();
    }

    /**
     * @test
     */
    public function canRenderViewHelper()
    {
        $arguments = array(
            'area' => 'void',
            'as' => 'records',
            'order' => 'sorting'
        );
        $variables = array(
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren,
            'provider' => new Provider()
        );
        $node = $this->createNode('Text', 'Hello loopy world!');
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables, $node);
        $renderingContext = ObjectAccess::getProperty($viewHelper, 'renderingContext', true);
        $provider = $this->objectManager->get(Provider::class);
        $provider->setGrid(Grid::create(['children' => [['type' => Row::class, 'children' => [['type' => Column::class, 'name' => 'void']]]]]));
        $provider->setForm(Form::create());
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(FormViewHelper::class, 'provider', $provider);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertSame($node->getText(), $output);
    }

    /**
     * @test
     */
    public function canRenderViewHelperWithLoadRegister()
    {
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
        $node = $this->createNode('Text', 'Hello loopy world!');
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables, $node);
        $renderingContext = ObjectAccess::getProperty($viewHelper, 'renderingContext', true);
        $provider = $this->objectManager->get(Provider::class);
        $provider->setGrid(Grid::create(['children' => [['type' => Row::class, 'children' => [['type' => Column::class, 'name' => 'void']]]]]));
        $provider->setForm(Form::create());
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(FormViewHelper::class, 'provider', $provider);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertSame($node->getText(), $output);
    }

    /**
     * @test
     */
    public function canRenderViewHelperWithExistingAsArgumentAndTakeBackup()
    {
        $arguments = array(
            'area' => 'void',
            'as' => 'nameTaken',
            'order' => 'sorting'
        );
        $variables = array(
            'nameTaken' => 'taken',
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        );
        $node = $this->createNode('Text', 'Hello loopy world!');
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables, $node);
        $renderingContext = ObjectAccess::getProperty($viewHelper, 'renderingContext', true);
        $provider = $this->objectManager->get(Provider::class);
        $provider->setGrid(Grid::create(['children' => [['type' => Row::class, 'children' => [['type' => Column::class, 'name' => 'void']]]]]));
        $provider->setForm(Form::create());
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(FormViewHelper::class, 'provider', $provider);
        $content = $viewHelper->initializeArgumentsAndRender();
        $this->assertIsString($content);
    }

    /**
     * @test
     */
    public function canRenderViewHelperWithNonExistingAsArgument()
    {
        $arguments = array(
            'area' => 'void',
            'as' => 'freevariablename',
            'order' => 'sorting'
        );
        $variables = array(
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        );
        $node = $this->createNode('Text', 'Hello loopy world!');
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables, $node);
        $renderingContext = ObjectAccess::getProperty($viewHelper, 'renderingContext', true);
        $provider = $this->objectManager->get(Provider::class);
        $provider->setGrid(Grid::create(['children' => [['type' => Row::class, 'children' => [['type' => Column::class, 'name' => 'void']]]]]));
        $provider->setForm(Form::create());
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(FormViewHelper::class, 'provider', $provider);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertSame($node->getText(), $output);
    }

    /**
     * @test
     */
    public function canReturnArrayOfUnrenderedContentElements()
    {
        $GLOBALS['TSFE']->cObj->expects($this->once())->method('getRecords')->willReturn([]);
        $arguments = array(
            'area' => 'void',
            'render' => false,
            'order' => 'sorting'
        );
        $variables = array(
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        );
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables);
        $renderingContext = ObjectAccess::getProperty($viewHelper, 'renderingContext', true);
        $provider = $this->objectManager->get(Provider::class);
        $provider->setGrid(Grid::create(['children' => [['type' => Row::class, 'children' => [['type' => Column::class, 'name' => 'void']]]]]));
        $provider->setForm(Form::create());
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(FormViewHelper::class, 'provider', $provider);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertIsArray($output);
    }

    /**
     * @test
     */
    public function canReturnArrayOfRenderedContentElements()
    {
        $GLOBALS['TSFE']->cObj->expects($this->once())->method('getRecords')->willReturn([]);
        $arguments = array(
            'area' => 'void',
            'render' => true,
            'order' => 'sorting'
        );
        $variables = array(
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        );
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables);
        $renderingContext = ObjectAccess::getProperty($viewHelper, 'renderingContext', true);
        $provider = $this->objectManager->get(Provider::class);
        $provider->setGrid(Grid::create(['children' => [['type' => Row::class, 'children' => [['type' => Column::class, 'name' => 'void']]]]]));
        $provider->setForm(Form::create());
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(FormViewHelper::class, 'provider', $provider);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertIsArray($output);
    }

    /**
     * @test
     */
    public function canProcessRecords()
    {
        $GLOBALS['TSFE']->sys_page = $this->getMockBuilder('TYPO3\\CMS\\Frontend\\Page\\PageRepository')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $instance = $this->createInstance();
        $records = array(
            array('uid' => 0),
            array('uid' => 99999999999),
        );
        $output = $this->callInaccessibleMethod($instance, 'getRenderedRecords', $records);
        $this->assertIsArray($output);
    }
}
