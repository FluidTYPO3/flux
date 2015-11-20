<?php
namespace FluidTYPO3\Flux\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;

/**
 * ### Outlet Definition
 *
 * Defines one data outlet for a Fluid form. Each outlet
 * is updated with the information when the form is saved.
 */
abstract class AbstractOutlet implements OutletInterface {

	/**
	 * @var boolean
	 */
	protected $enabled = TRUE;

	/**
	 * @var mixed
	 */
	protected $data;

	/**
	 * @var PipeInterface[]
	 */
	protected $pipesIn = array();

	/**
	 * @var PipeInterface[]
	 */
	protected $pipesOut = array();

	/**
	 * @param boolean $enabled
	 * @return OutletInterface
	 */
	public function setEnabled($enabled) {
		$this->enabled = $enabled;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEnabled() {
		return $this->enabled;
	}

	/**
	 * @param PipeInterface[] $pipes
	 * @return OutletInterface
	 * @return void
	 */
	public function setPipesIn(array $pipes) {
		$this->pipesIn = array();
		foreach ($pipes as $pipe) {
			$this->addPipeIn($pipe);
		}
		return $this;
	}

	/**
	 * @return PipeInterface[]
	 */
	public function getPipesIn() {
		return $this->pipesIn;
	}

	/**
	 * @param PipeInterface[] $pipes
	 * @return OutletInterface
	 * @return void
	 */
	public function setPipesOut(array $pipes) {
		$this->pipesOut = array();
		foreach ($pipes as $pipe) {
			$this->addPipeOut($pipe);
		}
		return $this;
	}

	/**
	 * @return PipeInterface[]
	 */
	public function getPipesOut() {
		return $this->pipesOut;
	}

	/**
	 * @param PipeInterface $pipe
	 * @return OutletInterface
	 */
	public function addPipeIn(PipeInterface $pipe) {
		if (FALSE === in_array($pipe, $this->pipesIn)) {
			array_push($this->pipesIn, $pipe);
		}
		return $this;
	}

	/**
	 * @param PipeInterface $pipe
	 * @return OutletInterface
	 */
	public function addPipeOut(PipeInterface $pipe) {
		if (FALSE === in_array($pipe, $this->pipesOut)) {
			array_push($this->pipesOut, $pipe);
		}
		return $this;
	}

	/**
	 * @param mixed $data
	 * @return OutletInterface
	 */
	public function fill($data) {
		foreach ($this->pipesIn as $pipe) {
			$data = $pipe->conduct($data);
		}
		$this->data = $data;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function produce() {
		$data = $this->data;
		foreach ($this->pipesOut as $pipe) {
			$pipe->conduct($data);
		}
		return $data;
	}

}
