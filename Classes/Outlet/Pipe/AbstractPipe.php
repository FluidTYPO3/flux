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
use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Form\Field\None;
use FluidTYPO3\Flux\Form\Field\Select;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Abstract Pipe
 *
 * Base class for all Pipes
 *
 * @package Flux
 * @subpackage Outlet\Pipe
 */
abstract class AbstractPipe implements PipeInterface {

	/**
	 * @param array $settings
	 * @return void
	 */
	public function loadSettings(array $settings) {
		foreach ($settings as $name => $value) {
			if (TRUE === property_exists($this, $name)) {
				ObjectAccess::setProperty($this, $name, $value);
			}
		}
	}

	/**
	 * @param mixed $data
	 * @return mixed
	 */
	public function conduct($data) {
		return $data;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return substr(lcfirst(array_pop(explode('\\', get_class($this)))), 0, -4);
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		$type = $this->getType();
		$translated = LocalizationUtility::translate('pipes.' . $type . '.label', 'flux');
		return (NULL === $translated ? $type : $translated);
	}

	/**
	 * @return FieldInterface[]
	 */
	public function getFormFields() {
		$class = get_class($this);
		$fields = array(
			'label' => Input::create(array('type' => 'Input'))->setName('label')->setDefault($this->getLabel()),
			'class' => Select::create(array('type' => 'Select'))->setName('class')->setItems(array($class => $class))
		);
		return $fields;
	}
}
