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
use TYPO3\CMS\Fluid\View\StandaloneView;

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
        $templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename($template);
        $view = $this->objectManager->get(StandaloneView::class);
        $view->setTemplatePathAndFilename($templatePathAndFilename);
        $view->renderSection('Configuration', []);
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
}
