<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * ContentViewHelperTest
 */
class ContentViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @test
     */
    public function createsGridIfNotSet()
    {
        /** @var ViewHelperVariableContainer $viewHelperContainer */
        $viewHelperContainer = $this->objectManager->get(ViewHelperVariableContainer::class);
        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        /** @var ControllerContext $controllerContext */
        $controllerContext = $this->objectManager->get(ControllerContext::class);
        $controllerContext->setRequest($request);
        $column = $this->getMockBuilder(Column::class)->setMethods(array('setName', 'setLabel'))->getMock();
        $column->expects($this->once())->method('setName');
        $column->expects($this->once())->method('setLabel');
        $row = $this->getMockBuilder(Row::class)->setMethods(array('createContainer'))->getMock();
        $grid = $this->getMockBuilder(Grid::class)->setMethods(array('createContainer'))->getMock();
        $grid->expects($this->once())->method('createContainer')->will($this->returnValue($row));
        $row->expects($this->once())->method('createContainer')->will($this->returnValue($column));
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('dummy'))->getMock();
        $viewHelperContainer->addOrUpdate(
            AbstractFormViewHelper::SCOPE,
            AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS,
            array('grid' => $grid)
        );
        $renderingcontext = $this->getMockBuilder(
            RenderingContext::class
        )->setMethods(
            array(
                'getTemplateVariableContainer', 'getViewHelperVariableContainer', 'getControllerContext'
            )
        )->getMock();
        $renderingcontext->expects($this->atLeastOnce())->method('getViewHelperVariableContainer')->willReturn($viewHelperContainer);
        $renderingcontext->expects($this->any())->method('getControllerContext')->willReturn($controllerContext);
        $mock->setRenderingContext($renderingcontext);
        $mock->setArguments(array());
        $mock::getComponent($renderingcontext, array());
    }
}
