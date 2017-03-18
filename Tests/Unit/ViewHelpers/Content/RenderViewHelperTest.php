<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\Statement;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use Prophecy\Argument;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $GLOBALS['TSFE'] = new TypoScriptFrontendController($GLOBALS['TYPO3_CONF_VARS'], 0, 0, 1);
        $GLOBALS['TSFE']->cObj = new ContentObjectRenderer();
        $GLOBALS['TSFE']->sys_page = $this->getMockBuilder(PageRepository::class)->setMethods(['enableFields'])->getMock();
        $GLOBALS['TYPO3_DB'] = $this->getMockBuilder('TYPO3\\CMS\\Core\\Database\\DatabaseConnection')->setMethods(array('exec_SELECTgetRows'))->disableOriginalConstructor()->getMock();
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->will($this->returnValue(array()));
        $GLOBALS['TCA']['tt_content']['ctrl'] = array();
    }

    /**
     * @return void
     */
    protected function createAndRegisterMockForQueryBuilder()
    {
        $statement = $this->prophesize(Statement::class);
        $statement->fetchAll()->willReturn([]);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $queryBuilder->from('tt_content')->will(function ($arguments) use ($queryBuilder) { return $queryBuilder->reveal(); });
        $queryBuilder->select('*')->will(function ($arguments) use ($queryBuilder) { return $queryBuilder->reveal(); });
        $queryBuilder->where(Argument::type('string'))->will(function ($arguments) use ($queryBuilder) { return $queryBuilder->reveal(); });
        $queryBuilder->orderBy('sorting', '');
        $queryBuilder->setMaxResults(0);
        $queryBuilder->execute()->willReturn($statement->reveal());

        $prophecy = $this->prophesize(ConnectionPool::class);
        $prophecy->getQueryBuilderForTable('tt_content')->willReturn($queryBuilder->reveal());

        GeneralUtility::addInstance(ConnectionPool::class, $prophecy->reveal());
    }

    /**
     * @test
     */
    public function canRenderViewHelper()
    {
        $this->createAndRegisterMockForQueryBuilder();
        $arguments = array(
            'area' => 'void',
            'as' => 'records',
            'order' => 'sorting'
        );
        $variables = array(
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        );
        $node = $this->createNode('Text', 'Hello loopy world!');
        $output = $this->executeViewHelper($arguments, $variables, $node);
        $this->assertSame($node->getText(), $output);
    }

    /**
     * @test
     */
    public function isUnaffectedByRenderArgumentBeingFalse()
    {
        $this->createAndRegisterMockForQueryBuilder();
        $arguments = array(
            'area' => 'void',
            'render' => false,
            'order' => 'sorting'
        );
        $variables = array(
            'record' => Records::$contentRecordWithoutParentAndWithoutChildren
        );
        $output = $this->executeViewHelper($arguments, $variables);
        $this->assertIsString($output);
    }

    /**
     * @test
     */
    public function canRenderViewHelperWithLoadRegister()
    {
        $this->createAndRegisterMockForQueryBuilder();
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
        $output = $this->executeViewHelper($arguments, $variables, $node);
        $this->assertSame($node->getText(), $output);
    }

}
