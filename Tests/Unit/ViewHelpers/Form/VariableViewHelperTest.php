<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;

class VariableViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @test
     */
    public function addsVariableToContainer()
    {
        $containerMock = $this->getMockBuilder(Form::class)->setMethods(['setVariable'])->getMock();
        $containerMock->expects($this->once())->method('setVariable')->with('test', 'testvalue');

        $this->viewHelperVariableContainer->addOrUpdate(FormViewHelper::class, 'container', $containerMock);
        $this->executeViewHelper(['name' => 'test', 'value' => 'testvalue']);
    }
}
