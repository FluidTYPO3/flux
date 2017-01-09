<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;

/**
 * VariableViewHelperTest
 */
class VariableViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @test
     */
    public function addsVariableToContainer()
    {
        $containerMock = $this->getMockBuilder('FluidTYPO3\Flux\Form')->setMethods(array('setVariable'))->getMock();
        $containerMock->expects($this->once())->method('setVariable')->with('test', 'testvalue');
        $viewHelperVariableContainerMock = $this->getMockBuilder(
            'TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer'
        )->setMethods(
            array('exists', 'get')
        )->getMock();
        $viewHelperVariableContainerMock->expects($this->once())->method('exists')->willReturn(true);
        $viewHelperVariableContainerMock->expects($this->once())->method('get')->willReturn($containerMock);
        $renderingContext = $this->getMockBuilder(
            'TYPO3\CMS\Fluid\Core\Rendering\RenderingContext'
        )->setMethods(
            array('getTemplateVariableContainer', 'getViewHelperVariableContainer', 'getControllerContext')
        )->getMock();
        $renderingContext->expects($this->atLeastOnce())
            ->method('getViewHelperVariableContainer')
            ->willReturn($viewHelperVariableContainerMock);
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('buildRenderChildrenClosure'))->getMock();
        $instance->expects($this->once())->method('buildRenderChildrenClosure')->willReturn(function() { return null; });
        $instance->setRenderingContext($renderingContext);
        $instance->setArguments(array('name' => 'test', 'value' => 'testvalue'));
        $instance->render();
    }
}
