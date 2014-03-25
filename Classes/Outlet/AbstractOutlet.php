<?php
namespace FluidTYPO3\Flux\Outlet;
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
 *****************************************************************/

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;

/**
 * ### Outlet Definition
 *
 * Defines one data outlet for a Fluid form. Each outlet
 * is updated with the information when the form is saved.
 *
 * @package Fluidbackend
 * @subpackage Outlet
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
