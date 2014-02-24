<?php
namespace FluidTYPO3\Flux\Domain\Model;
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
 ***************************************************************/

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Domain Model: Attribute for table/field/identity combination
 *
 * @package Flux
 */
class Value extends AbstractEntity {

	/**
	 * Parent Attribute of this Value.
	 *
	 * @var Attribute
	 */
	protected $attribute;

	/**
	 * Real (raw) value of the value
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * @param mixed $value
	 * @return void
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param Attribute $attribute
	 * @return void
	 */
	public function setAttribute(Attribute $attribute) {
		$this->attribute = $attribute;
	}

	/**
	 * @return Attribute
	 */
	public function getAttribute() {
		return $this->attribute;
	}

}
