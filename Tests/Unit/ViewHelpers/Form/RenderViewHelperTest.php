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

/**
 * @package Flux
 */
class RenderViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * @test
	 */
	public function testRender() {
		$form = Form::create();
		$engine = $this->getMock(
			'TYPO3\\CMS\\Backend\\Form\\FormEngine',
			array('printNeededJSFunctions_top', 'getSoloField', 'printNeededJSFunctions'),
			array(), '', FALSE
		);
		$engine->expects($this->once())->method('printNeededJSFunctions_top')->willReturn('1');
		$engine->expects($this->once())->method('getSoloField')->willReturn('2');
		$engine->expects($this->once())->method('printNeededJSFunctions')->willReturn('3');
		$instance = $this->getMock($this->createInstanceClassName(), array('getFormEngine'));
		$instance->expects($this->once())->method('getFormEngine')->willReturn($engine);
		$result = $instance->render($form);
		$this->assertEquals('123', $result);
	}

}
