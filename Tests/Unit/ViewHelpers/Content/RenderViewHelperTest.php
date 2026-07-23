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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;

class RenderViewHelperTest extends AbstractViewHelperTestCase
{
    protected ?ContentObjectRenderer $contentObjectRenderer = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentObjectRenderer = $this->getMockBuilder(ContentObjectRenderer::class)
            ->onlyMethods(['getRecords', 'cObjGetSingle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentObjectRenderer->method('getRecords')->willReturn([]);

        $GLOBALS['TYPO3_REQUEST'] = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $GLOBALS['TYPO3_REQUEST']->method('getAttribute')
            ->with('currentContentObject')
            ->willReturn($this->contentObjectRenderer);

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
        $this->contentObjectRenderer->expects($this->once())->method('getRecords')->willReturn([]);
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
