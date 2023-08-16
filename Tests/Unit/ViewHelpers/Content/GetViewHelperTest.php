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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class GetViewHelperTest extends AbstractViewHelperTestCase
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
        $GLOBALS['TSFE']->sys_page = $this->getMockBuilder(PageRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['enableFields'])
            ->getMock();
        $GLOBALS['TCA']['tt_content']['ctrl'] = [];

        $grid = Grid::create(
            [
                'children' => [
                    [
                        'type' => Row::class, 'children' => [
                            [
                                'type' => Column::class, 'name' => 'void'
                            ]
                        ]
                    ]
                ]
            ]
        );

        $provider = $this->getMockBuilder(Provider::class)
            ->addMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->setGrid($grid);
        $provider->setForm($this->getMockBuilder(Form::class)->addMethods(['dummy'])->getMock());

        $this->viewHelperVariableContainer->addOrUpdate(FormViewHelper::class, 'provider', $provider);
        $this->viewHelperVariableContainer->addOrUpdate(FormViewHelper::class, 'record', ['uid' => 123]);

        $contentObjectFactory = $this->getMockBuilder(ContentObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        GeneralUtility::addInstance(ContentObjectFactory::class, $contentObjectFactory);

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
    public function canRenderViewHelper(): void
    {
        $arguments = [
            'area' => 'void',
            'as' => 'records',
            'order' => 'sorting'
        ];
        $variables = [
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren,
            'provider' => $this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock()
        ];
        $node = $this->createNode('Text', 'Hello loopy world!');
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables, $node);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertSame($node->getText(), $output);
    }

    /**
     * @test
     */
    public function canRenderViewHelperWithLoadRegister(): void
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
        $node = $this->createNode('Text', 'Hello loopy world!');
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables, $node);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertSame($node->getText(), $output);
    }

    /**
     * @test
     */
    public function canRenderViewHelperWithExistingAsArgumentAndTakeBackup(): void
    {
        $arguments = [
            'area' => 'void',
            'as' => 'nameTaken',
            'order' => 'sorting'
        ];
        $variables = [
            'nameTaken' => 'taken',
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        ];
        $node = $this->createNode('Text', 'Hello loopy world!');
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables, $node);
        $content = $viewHelper->initializeArgumentsAndRender();
        $this->assertIsString($content);
    }

    /**
     * @test
     */
    public function canRenderViewHelperWithNonExistingAsArgument(): void
    {
        $arguments = [
            'area' => 'void',
            'as' => 'freevariablename',
            'order' => 'sorting'
        ];
        $variables = [
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        ];
        $node = $this->createNode('Text', 'Hello loopy world!');
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables, $node);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertSame($node->getText(), $output);
    }

    /**
     * @test
     */
    public function canReturnArrayOfUnrenderedContentElements(): void
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
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertIsArray($output);
    }

    /**
     * @test
     */
    public function canReturnArrayOfRenderedContentElements(): void
    {
        $GLOBALS['TSFE']->cObj->expects($this->once())->method('getRecords')->willReturn([]);
        $arguments = [
            'area' => 'void',
            'render' => true,
            'order' => 'sorting'
        ];
        $variables = [
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        ];
        $viewHelper = $this->buildViewHelperInstance($arguments, $variables);
        $output = $viewHelper->initializeArgumentsAndRender();
        $this->assertIsArray($output);
    }

    /**
     * @test
     */
    public function canProcessRecords(): void
    {
        $GLOBALS['TSFE']->sys_page = $this->getMockBuilder(PageRepository::class)
            ->addMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $instance = $this->createInstance();
        $records = [
            ['uid' => 0],
            ['uid' => 99999999999],
        ];
        $output = $this->callInaccessibleMethod($instance, 'getRenderedRecords', $records);
        $this->assertIsArray($output);
    }
}
