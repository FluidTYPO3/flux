<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

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
		$pipes = [
			$this->objectManager->get('FluidTYPO3\Flux\Outlet\Pipe\StandardPipe')
		];
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
		$pipes = [
			$this->objectManager->get('FluidTYPO3\Flux\Outlet\Pipe\StandardPipe')
		];
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
		$data = ['test'];
		$pipe = $this->getMock('FluidTYPO3\Flux\Outlet\Pipe\StandardPipe', ['conduct']);
		$pipe->expects($this->exactly(2))->method('conduct')->with($data)->will($this->returnValue($data));
		$pipes = [
			$pipe
		];
		$output = $instance->setPipesIn($pipes)->setPipesOut($pipes)->fill($data)->produce();
		$this->assertSame($data, $output);
	}

}
