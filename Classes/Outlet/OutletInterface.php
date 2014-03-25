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
 * Outlet Interface
 *
 * Implemented by all Outlet types.
 *
 * @package Flux
 * @subpackage Outlet
 */
interface OutletInterface {

	/**
	 * @param boolean $enabled
	 * @return void
	 * @abstract
	 */
	public function setEnabled($enabled);

	/**
	 * @return boolean
	 * @abstract
	 */
	public function getEnabled();

	/**
	 * @param array $data
	 * @return mixed
	 * @abstract
	 */
	public function fill($data);

	/**
	 * @return mixed
	 * @abstract
	 */
	public function produce();

	/**
	 * @param PipeInterface[] $pipes
	 * @return OutletInterface
	 * @return void
	 */
	public function setPipesIn(array $pipes);

	/**
	 * @return PipeInterface[]
	 */
	public function getPipesIn();

	/**
	 * @param PipeInterface[] $pipes
	 * @return OutletInterface
	 * @return void
	 */
	public function setPipesOut(array $pipes);

	/**
	 * @return PipeInterface[]
	 */
	public function getPipesOut();

	/**
	 * @param PipeInterface $pipe
	 * @return OutletInterface
	 */
	public function addPipeIn(PipeInterface $pipe);

	/**
	 * @param PipeInterface $pipe
	 * @return OutletInterface
	 */
	public function addPipeOut(PipeInterface $pipe);

}
