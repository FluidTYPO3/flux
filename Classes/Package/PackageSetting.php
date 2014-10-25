<?php
namespace FluidTYPO3\Flux\Package;
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

use FluidTYPO3\Flux\Collection\CollectableInterface;

/**
 * Class PackageSetting
 */
class PackageSetting implements CollectableInterface {

	const DEFAULT_GROUP = 'default';

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $group = self::DEFAULT_GROUP;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var mixed
	 */
	protected $defaultValue;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @param string $name
	 * @param string $type
	 * @param string $label
	 * @param string $group
	 * @param string $defaultValue
	 * @param null $value
	 */
	public function __construct($name, $type, $label, $group = self::DEFAULT_GROUP, $defaultValue = '', $value = NULL) {
		$this->setName($name);
		$this->setType($type);
		$this->setLabel($label);
		$this->setGroup($group);
		$this->setDefaultValue($defaultValue);
		$this->setValue($value);
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $label
	 * @return void
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * @return mixed
	 */
	public function getDefaultValue() {
		return $this->defaultValue;
	}

	/**
	 * @param mixed $defaultValue
	 * @return void
	 */
	public function setDefaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
	}

	/**
	 * @return string
	 */
	public function getGroup() {
		return $this->group;
	}

	/**
	 * @param string $group
	 * @return void
	 */
	public function setGroup($group) {
		$this->group = $group;
	}

}
