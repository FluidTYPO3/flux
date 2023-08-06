<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\Query\QueryBuilder;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;

class RenderViewHelperTest extends AbstractViewHelperTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TSFE'] = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['TSFE']->cObj = $this->getMockBuilder(ContentObjectRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRecords', 'cObjGetSingle'])
            ->getMock();
        $GLOBALS['TSFE']->cObj->method('getRecords')->willReturn([]);
        $GLOBALS['TSFE']->cObj->method('cObjGetSingle')->willReturn('object');

        $GLOBALS['TCA']['tt_content']['ctrl'] = [];

        $grid = Grid::create(
            [
                'children' => [
                    [
                        'type' => Row::class,
                        'children' => [
                            [
                                'type' => Column::class,
                                'name' => 'void'
                            ]
                        ]
                    ]
                ]
            ]
        );

        $provider = $this->getMockBuilder(Provider::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->setGrid($grid);
        $provider->setForm($this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock());

        $this->viewHelperVariableContainer->addOrUpdate(FormViewHelper::class, 'provider', $provider);
        $this->viewHelperVariableContainer->addOrUpdate(FormViewHelper::class, 'record', ['uid' => 123]);

        $expressionBuilder = $this->getMockBuilder(ExpressionBuilder::class)
            ->onlyMethods(['eq'])
            ->disableOriginalConstructor()
            ->getMock();
        $connectionPool = $this->getMockBuilder(ConnectionPool::class)
            ->onlyMethods(['getQueryBuilderForTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->onlyMethods(['expr'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder->method('expr')->willReturn($expressionBuilder);

        GeneralUtility::addInstance(ConnectionPool::class, $connectionPool);
    }

    /**
     * @test
     */
    public function canRenderViewHelper()
    {
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
    public function isUnaffectedByRenderArgumentBeingFalse()
    {
        $GLOBALS['TSFE']->cObj->expects($this->once())->method('getRecords')->willReturn([]);
        $arguments = [
            'area' => 'void',
            'render' => false,
            'order' => 'sorting'
        ];
        $variables = [
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        ];
        $output = $this->executeViewHelper($arguments, $variables);
        $this->assertIsString($output);
    }

    /**
     * @test
     */
    public function canRenderViewHelperWithLoadRegister()
    {
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
}
