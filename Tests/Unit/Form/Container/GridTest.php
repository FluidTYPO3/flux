<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * GridTest
 */
class GridTest extends AbstractContainerTest
{

    /**
     * @param string $gridName
     * @param string $template
     * @return Grid
     */
    protected function getDummyGridFromTemplate($gridName = 'grid', $template = self::FIXTURE_TEMPLATE_BASICGRID)
    {
        $templateCompiler = $this->getMockBuilder(TemplateCompiler::class)->getMock();
        $templateParser = new TemplateParser();
        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $variableProvider = new StandardVariableProvider();
        $viewHelperResolver = new ViewHelperResolver();
        $viewHelperInvoker = new ViewHelperInvoker();
        $namespaceDetectionTemplateProcessor = new NamespaceDetectionTemplateProcessor();
        $templatePaths = new TemplatePaths(
            [
                'partialRootPaths' => ['Tests/Fixtures/Partials/'],
                'templateRootPaths' => ['Tests/Fixtures/Templates/'],
                'layoutRootPaths' => ['Tests/Fixtures/Layouts/'],
            ]
        );

        $renderingContext = $this->getMockBuilder(\TYPO3\CMS\Fluid\Core\Rendering\RenderingContext::class)->setMethods(
            [
                'getTemplatePaths',
                'getViewHelperVariableContainer',
                'getVariableProvider',
                'getTemplateCompiler',
                'getViewHelperInvoker',
                'getTemplateParser',
                'getViewHelperResolver',
                'getTemplateProcessors',
                'getExpressionNodeTypes',
                'getControllerName',
                'getControllerAction',
            ]
        )->disableOriginalConstructor()->getMock();
        $renderingContext->method('getTemplatePaths')->willReturn($templatePaths);
        $renderingContext->method('getViewHelperVariableContainer')->willReturn($viewHelperVariableContainer);
        $renderingContext->method('getVariableProvider')->willReturn($variableProvider);
        $renderingContext->method('getTemplateCompiler')->willReturn($templateCompiler);
        $renderingContext->method('getTemplateParser')->willReturn($templateParser);
        $renderingContext->method('getViewHelperInvoker')->willReturn($viewHelperInvoker);
        $renderingContext->method('getViewHelperResolver')->willReturn($viewHelperResolver);
        $renderingContext->method('getTemplateProcessors')->willReturn([$namespaceDetectionTemplateProcessor]);
        $renderingContext->method('getExpressionNodeTypes')->willReturn([]);
        $renderingContext->method('getControllerName')->willReturn('Content');
        $renderingContext->method('getControllerAction')->willReturn(basename($template, '.html'));

        $namespaceDetectionTemplateProcessor->setRenderingContext($renderingContext);

        $templateParser->setRenderingContext($renderingContext);

        $view = new TemplateView($renderingContext);

        $view->renderSection('Configuration', [], true);
        return $view->getRenderingContext()->getViewHelperVariableContainer()->get(FormViewHelper::class, 'grids')[$gridName] ?? Grid::create();
    }

    /**
     * @test
     */
    public function canRetrieveStoredGrid()
    {
        $grid = $this->getDummyGridFromTemplate();
        $this->assertIsValidAndWorkingGridObject($grid);
    }

    /**
     * @test
     */
    public function canReturnGridObjectWithoutGridPresentInTemplate()
    {
        $grid = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_WITHOUTFORM);
        $this->assertIsValidAndWorkingGridObject($grid);
    }

    /**
     * @test
     */
    public function canReturnFallbackGridObjectWhenUsingIncorrectGridName()
    {
        $grid = $this->getDummyGridFromTemplate('doesnotexist', self::FIXTURE_TEMPLATE_BASICGRID);
        $this->assertIsValidAndWorkingGridObject($grid);
    }

    /**
     * @test
     */
    public function canReturnGridObjectWithDualGridsPresentInTemplate()
    {
        $grid1 = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_DUALGRID);
        $grid2 = $this->getDummyGridFromTemplate('grid2', self::FIXTURE_TEMPLATE_DUALGRID);
        $this->assertIsValidAndWorkingGridObject($grid1);
        $this->assertIsValidAndWorkingGridObject($grid2);
    }

    /**
     * @test
     */
    public function canReturnGridObjectOneFallbackWithDualGridsPresentInTemplate()
    {
        $grid1 = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_DUALGRID);
        $grid2 = $this->getDummyGridFromTemplate('doesnotexist', self::FIXTURE_TEMPLATE_DUALGRID);
        $this->assertIsValidAndWorkingGridObject($grid1);
        $this->assertIsValidAndWorkingGridObject($grid2);
    }

    /**
     * @test
     */
    public function canReturnOneGridWithTwoRowsFromTemplateWithDualGridsWithSameNameAndOneRowEach()
    {
        $grid = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_COLLIDINGGRID);
        $this->assertIsValidAndWorkingGridObject($grid);
        $this->assertSame(2, count($grid->getRows()));
    }

    /**
     * @test
     */
    public function canUseGetRowsMethod()
    {
        /** @var Grid $instance */
        $instance = $this->createInstance();
        $this->assertEmpty($instance->getRows());
    }

    /**
     * @dataProvider getEnsureDottedKeysTestValues
     * @param array $input
     * @param array $expected
     */
    public function testEnsureDottedKeys(array $input, array $expected)
    {
        $instance = new Grid();
        $result = $this->callInaccessibleMethod($instance, 'ensureDottedKeys', $input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getEnsureDottedKeysTestValues()
    {
        return array(
            array(
                array('foo' => array('bar' => 'bar')),
                array('foo.' => array('bar' => 'bar'))
            ),
            array(
                array('foo.' => array('bar' => 'bar')),
                array('foo.' => array('bar' => 'bar'))
            )
        );
    }
}
