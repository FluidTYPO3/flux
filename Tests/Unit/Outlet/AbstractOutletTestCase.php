<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * @package Flux
 */
abstract class AbstractOutletTestCase extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canGetAndSetEnabled() {
		$this->assertGetterAndSetterWorks('enabled', FALSE, FALSE, TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetPipesIn() {
		$pipes = array(
			$this->objectManager->get('FluidTYPO3\Flux\Outlet\Pipe\StandardPipe')
		);
		$this->assertGetterAndSetterWorks('pipesIn', $pipes, $pipes, TRUE);
	}

	/**
	 * @test
	 */
	public function canAddAndRetrievePipeIn() {
		$instance = $this->createInstance();
		$pipe = $this->objectManager->get('FluidTYPO3\Flux\Outlet\Pipe\StandardPipe');
		$instance->addPipeIn($pipe);
		$this->assertContains($pipe, $instance->getPipesIn());
	}

	/**
	 * @test
	 */
	public function canGetAndSetPipesOut() {
		$pipes = array(
			$this->objectManager->get('FluidTYPO3\Flux\Outlet\Pipe\StandardPipe')
		);
		$this->assertGetterAndSetterWorks('pipesOut', $pipes, $pipes, TRUE);
	}

	/**
	 * @test
	 */
	public function canAddAndRetrievePipeOut() {
		$instance = $this->createInstance();
		$pipe = $this->objectManager->get('FluidTYPO3\Flux\Outlet\Pipe\StandardPipe');
		$instance->addPipeOut($pipe);
		$this->assertContains($pipe, $instance->getPipesOut());
	}

	/**
	 * @test
	 */
	public function fillsWithDataAndConductsUsingPipes() {
		$instance = $this->createInstance();
		$data = array('test');
		$pipe = $this->getMock('FluidTYPO3\Flux\Outlet\Pipe\StandardPipe', array('conduct'));
		$pipe->expects($this->exactly(2))->method('conduct')->with($data)->will($this->returnValue($data));
		$pipes = array(
			$pipe
		);
		$output = $instance->setPipesIn($pipes)->setPipesOut($pipes)->fill($data)->produce();
		$this->assertSame($data, $output);
	}

}
