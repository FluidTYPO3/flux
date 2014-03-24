<?php
namespace FluidTYPO3\Flux\Form\Field;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

use FluidTYPO3\Flux\Form\AbstractFormField;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Form\FormInterface;

/**
 * @package Flux
 * @subpackage Form\Field
 */
class UserFunction extends AbstractFormField implements FieldInterface {

	/**
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * @var string
	 */
	protected $function;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = $this->prepareConfiguration('user');
		$configuration['userFunc'] = $this->getFunction();
		$configuration['arguments'] = $this->getArguments();
		return $configuration;
	}

	/**
	 * @param string $function
	 * @return UserFunction
	 */
	public function setFunction($function) {
		$this->function = $function;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFunction() {
		return $this->function;
	}

	/**
	 * @param array $arguments
	 * @return UserFunction
	 */
	public function setArguments($arguments) {
		$this->arguments = $arguments;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getArguments() {
		return $this->arguments;
	}

}
