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
class VariableViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * @test
	 */
	public function addsVariableToContainer() {
		$containerMock = $this->getMock('FluidTYPO3\Flux\Form', array('setVariable'));
		$containerMock->expects($this->once())->method('setVariable')->with('test', 'testvalue');
		$viewHelperVariableContainerMock = $this->getMock(
			'TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer',
			array('exists', 'get')
		);
		$viewHelperVariableContainerMock->expects($this->once())->method('exists')->willReturn(TRUE);
		$viewHelperVariableContainerMock->expects($this->once())->method('get')->willReturn($containerMock);
		$renderingContext = $this->getMock(
			'TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface',
			array('getTemplateVariableContainer', 'getViewHelperVariableContainer', 'getControllerContext')
		);
		$renderingContext->expects($this->atLeastOnce())
			->method('getViewHelperVariableContainer')
			->willReturn($viewHelperVariableContainerMock);
		$instance = $this->getMock($this->createInstanceClassName(), array('dummy'));
		$instance->setRenderingContext($renderingContext);
		$instance->setArguments(array('name' => 'test', 'value' => 'testvalue'));
		$instance->render();
	}

}
