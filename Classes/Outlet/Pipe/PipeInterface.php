<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;
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
use FluidTYPO3\Flux\Form\FieldInterface;

/**
 * Pipe Interface
 *
 * Interface for Pipes which process data for Outlets.
 *
 * @package Flux
 * @subpackage Outlet\Pipe
 */
interface PipeInterface {

	/**
	 * @param array $settings
	 * @return void
	 */
	public function loadSettings(array $settings);

	/**
	 * Accept $data and do whatever the Pipe should do before
	 * returning the same or a modified version of $data for
	 * chaining with other potential Pipes.
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	public function conduct($data);

	/**
	 * Get a human-readable name of this Pipe.
	 *
	 * @return string
	 */
	public function getLabel();

	/**
	 * Return the FormComponent "Field" instances which represent
	 * options this Pipe supports.
	 *
	 * @return FieldInterface[]
	 */
	public function getFormFields();

}
