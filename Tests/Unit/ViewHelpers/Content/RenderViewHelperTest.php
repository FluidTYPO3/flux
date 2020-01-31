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
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * RenderViewHelperTest
 */
class RenderViewHelperTest extends AbstractViewHelperTestCase
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
        $GLOBALS['TYPO3_DB'] = $this->getMockBuilder('TYPO3\\CMS\\Core\\Database\\DatabaseConnection')->setMethods(array('exec_SELECTgetRows'))->disableOriginalConstructor()->getMock();
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->will($this->returnValue(array()));
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
    public function isUnaffectedByRenderArgumentBeingFalse()
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
        $this->assertIsString($output);
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

}
