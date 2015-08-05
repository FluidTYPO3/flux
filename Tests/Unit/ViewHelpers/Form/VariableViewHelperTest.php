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
 * @package Flux
 */
class VariableViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * @test
	 */
	public function addsVariableToContainer() {
		$containerMock = $this->getMock('FluidTYPO3\Flux\Form', ['setVariable']);
		$containerMock->expects($this->once())->method('setVariable')->with('test', 'testvalue');
		$instance = $this->getMock($this->createInstanceClassName(), ['getContainer']);
		$instance->expects($this->once())->method('getContainer')->will($this->returnValue($containerMock));
		$instance->setArguments(['name' => 'test', 'value' => 'testvalue']);
		$instance->render();
	}

}
