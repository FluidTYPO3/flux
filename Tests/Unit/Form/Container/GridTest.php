<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Integration\FormEngine\SelectOption;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\TemplateView;

class GridTest extends AbstractContainerTest
{
    public function testBuildColumnPositionValues(): void
    {
        $record = ['uid' => 123];
        $subject = $this->getGridBuildingTestSubject();
        $output = $subject->buildColumnPositionValues($record);
        self::assertSame(
            [
                0 => 12301,
                1 => 12302,
            ],
            $output
        );
    }

    public function testBuildBackendLayout(): void
    {
        $expectedConfigurationString = <<<STRING
backend_layout.colCount = 1
backend_layout.rowCount = 2
backend_layout.rows.1.columns.1.name = LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.test.columns.column1
backend_layout.rows.1.columns.1.icon = 
backend_layout.rows.1.columns.1.colPos = 12301
backend_layout.rows.1.columns.1.colspan = 1
backend_layout.rows.1.columns.1.rowspan = 1
backend_layout.rows.2.columns.1.name = LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.test.columns.column2
backend_layout.rows.2.columns.1.icon = 
backend_layout.rows.2.columns.1.colPos = 12302
backend_layout.rows.2.columns.1.colspan = 1
backend_layout.rows.2.columns.1.rowspan = 1

STRING;

        $subject = $this->getGridBuildingTestSubject();
        $subject->expects(self::once())
            ->method('createBackendLayout')
            ->with(
                'test',
                'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.test.grids.test',
                $expectedConfigurationString
            )
            ->willReturn(
                $this->getMockBuilder(BackendLayout::class)->disableOriginalConstructor()->getMock()
            );

        $subject->buildBackendLayout(123);
    }

    public function testBuildExtendedBackendLayout(): void
    {
        $expectedConfiguration = $this->createExpectedExtendedLayout(12300);

        $subject = $this->getGridBuildingTestSubject();
        $output = $subject->buildExtendedBackendLayoutArray(123);
        self::assertSame($expectedConfiguration, $output);
    }

    public function testBuildExtendedBackendLayoutForPageLevelColumns(): void
    {
        $virtualColumn = [
            'foo3', 8001, 'baz3',
        ];
        $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'] = [
            [
                'foo1', 1, 'baz1',
            ],
            [
                'foo2', 2, 'baz2',
            ],
            $virtualColumn,
        ];
        $expectedConfiguration = $this->createExpectedExtendedLayout(0);
        $expectedConfiguration['__items'][] = $virtualColumn;

        $subject = $this->getGridBuildingTestSubject();
        $output = $subject->buildExtendedBackendLayoutArray(0);
        self::assertSame($expectedConfiguration, $output);
    }

    protected function createExpectedExtendedLayout(int $colPosModifier): array
    {
        return [
            'usedColumns' => [
                1 + $colPosModifier => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.' .
                    'test.columns.column1',
                2 + $colPosModifier => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.' .
                    'test.columns.column2',
            ],
            '__config' => [
                'backend_layout.' => [
                    'colCount' => 1,
                    'rowCount' => 2,
                    'rows.' => [
                        '1.' => [
                            'columns.' => [
                                '1.' => [
                                    'name' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.' .
                                        'test.columns.column1',
                                    'icon' => null,
                                    'colPos' => 1 + $colPosModifier,
                                    'colspan' => 1,
                                    'rowspan' => 1,
                                ],
                            ],
                        ],
                        '2.' => [
                            'columns.' => [
                                '1.' => [
                                    'name' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.' .
                                        'test.columns.column2',
                                    'icon' => null,
                                    'colPos' => 2 + $colPosModifier,
                                    'colspan' => 1,
                                    'rowspan' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '__colPosList' => [
                1 + $colPosModifier => 1 + $colPosModifier,
                2 + $colPosModifier => 2 + $colPosModifier,
            ],
            '__items' => [
                (new SelectOption(
                    'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.test.columns.column1',
                    1 + $colPosModifier,
                    null,
                ))->toArray(),
                (new SelectOption(
                    'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.test.columns.column2',
                    2 + $colPosModifier,
                    null,
                ))->toArray(),
            ],
        ];
    }

    protected function getGridBuildingTestSubject(): Grid
    {
        $subject = $this->getMockBuilder(Grid::class)->setMethods(['createBackendLayout'])->getMock();
        $subject->setName('test');

        /** @var Column $column */
        $column = $subject->createContainer(Row::class, 'row1')->createContainer(Column::class, 'column1');
        $column->setColumnPosition(1);

        /** @var Column $column */
        $column = $subject->createContainer(Row::class, 'row2')->createContainer(Column::class, 'column2');
        $column->setColumnPosition(2);

        return $subject;
    }

    protected function getDummyGridFromTemplate(
        string $gridName = 'grid',
        string $template = self::FIXTURE_TEMPLATE_BASICGRID
    ): Grid {
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
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['getControllerExtensionName'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('getControllerExtensionName')->willReturn('Flux');

        $renderingContext = $this->getMockBuilder(RenderingContext::class)->setMethods(
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
                'getControllerContext',
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
        if (class_exists(ControllerContext::class)) {
            $controllerContext = new ControllerContext();
            $controllerContext->setRequest($request);
            $renderingContext->method('getControllerContext')->willReturn($controllerContext);
        }

        $namespaceDetectionTemplateProcessor->setRenderingContext($renderingContext);

        $templateParser->setRenderingContext($renderingContext);

        $view = new TemplateView($renderingContext);

        $view->renderSection('Configuration', [], true);
        return $view->getRenderingContext()
            ->getViewHelperVariableContainer()
            ->get(FormViewHelper::class, 'grids')[$gridName] ?? Grid::create();
    }

    /**
     * @test
     */
    public function canRetrieveStoredGrid(): void
    {
        $grid = $this->getDummyGridFromTemplate();
        $this->assertIsValidAndWorkingGridObject($grid);
    }

    /**
     * @test
     */
    public function canReturnGridObjectWithoutGridPresentInTemplate(): void
    {
        $grid = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_WITHOUTFORM);
        $this->assertIsValidAndWorkingGridObject($grid);
    }

    /**
     * @test
     */
    public function canReturnFallbackGridObjectWhenUsingIncorrectGridName(): void
    {
        $grid = $this->getDummyGridFromTemplate('doesnotexist', self::FIXTURE_TEMPLATE_BASICGRID);
        $this->assertIsValidAndWorkingGridObject($grid);
    }

    /**
     * @test
     */
    public function canReturnGridObjectWithDualGridsPresentInTemplate(): void
    {
        $grid1 = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_DUALGRID);
        $grid2 = $this->getDummyGridFromTemplate('grid2', self::FIXTURE_TEMPLATE_DUALGRID);
        $this->assertIsValidAndWorkingGridObject($grid1);
        $this->assertIsValidAndWorkingGridObject($grid2);
    }

    /**
     * @test
     */
    public function canReturnGridObjectOneFallbackWithDualGridsPresentInTemplate(): void
    {
        $grid1 = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_DUALGRID);
        $grid2 = $this->getDummyGridFromTemplate('doesnotexist', self::FIXTURE_TEMPLATE_DUALGRID);
        $this->assertIsValidAndWorkingGridObject($grid1);
        $this->assertIsValidAndWorkingGridObject($grid2);
    }

    /**
     * @test
     */
    public function canReturnOneGridWithTwoRowsFromTemplateWithDualGridsWithSameNameAndOneRowEach(): void
    {
        $grid = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_COLLIDINGGRID);
        $this->assertIsValidAndWorkingGridObject($grid);
        $this->assertSame(2, count($grid->getRows()));
    }

    /**
     * @test
     */
    public function canUseGetRowsMethod(): void
    {
        /** @var Grid $instance */
        $instance = $this->createInstance();
        $this->assertEmpty($instance->getRows());
    }

    /**
     * @dataProvider getEnsureDottedKeysTestValues
     */
    public function testEnsureDottedKeys(array $input, array $expected): void
    {
        $instance = new Grid();
        $result = $this->callInaccessibleMethod($instance, 'ensureDottedKeys', $input);
        $this->assertEquals($expected, $result);
    }

    public function getEnsureDottedKeysTestValues(): array
    {
        return [
            [
                ['foo' => ['bar' => 'bar']],
                ['foo.' => ['bar' => 'bar']]
            ],
            [
                ['foo.' => ['bar' => 'bar']],
                ['foo.' => ['bar' => 'bar']]
            ]
        ];
    }
}
