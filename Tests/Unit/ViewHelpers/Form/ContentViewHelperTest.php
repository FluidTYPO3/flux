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

class ContentViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @test
     */
    public function createsGridIfNotSet()
    {
        $column = $this->getMockBuilder(Column::class)->setMethods(['setName', 'setLabel'])->getMock();
        $column->expects($this->once())->method('setName');
        $column->expects($this->once())->method('setLabel');
        $row = $this->getMockBuilder(Row::class)->setMethods(['createContainer'])->getMock();
        $grid = $this->getMockBuilder(Grid::class)->setMethods(['createContainer'])->getMock();
        $grid->expects($this->once())->method('createContainer')->will($this->returnValue($row));
        $row->expects($this->once())->method('createContainer')->will($this->returnValue($column));

        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(['dummy'])->getMock();

        $this->viewHelperVariableContainer->addOrUpdate(
            AbstractFormViewHelper::SCOPE,
            AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS,
            ['grid' => $grid]
        );

        $mock->setRenderingContext($this->renderingContext);
        $mock->setArguments([]);
        $mock::getComponent($this->renderingContext, $this->buildViewHelperArguments($mock, []));
    }
}
