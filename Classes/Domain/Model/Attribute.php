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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain Model: Attribute for table/field/identity combination
 *
 * @package Flux
 */
class Attribute extends AbstractEntity {

	/**
	 * Name of the attribute. A dotted name becomes expanded to a
	 * deep array which gets merged with other values.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Optional group name. If filled, can group attributes into
	 * logical groups (for example allowing queries to be generated
	 * to select a particular set of attributes onlye).
	 *
	 * @var string
	 */
	protected $sheet;

	/**
	 * Values of this attribute (sorted relations).
	 *
	 * @var string
	 * \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\FluidTYPO3\Flux\Domain\Model\Value>
	 */
	protected $attributeValues;

	/**
	 * Table this Attribute is assigned to.
	 *
	 * @var string
	 */
	protected $forTable;

	/**
	 * Field in the table this Attribute is assigned to. Can be NULL in
	 * custom relation configurations but must be filled if several sets
	 * of attributes must be used (for example, when two implements try
	 * to use the same table which often happens in tt_content).
	 *
	 * @var string
	 */
	protected $forField;

	/**
	 * Identity (UID) of the record which this attribute is attached to.
	 * Must not be zero (e.g. a valid attribute must be assigned to at
	 * least a table and an identity whereas a field is optional).
	 *
	 * @var string
	 */
	protected $forIdentity;

	/**
	 * @param string $forField
	 * @return void
	 */
	public function setForField($forField) {
		$this->forField = $forField;
	}

	/**
	 * @return string
	 */
	public function getForField() {
		return $this->forField;
	}

	/**
	 * @param string $forIdentity
	 * @return void
	 */
	public function setForIdentity($forIdentity) {
		$this->forIdentity = $forIdentity;
	}

	/**
	 * @return string
	 */
	public function getForIdentity() {
		return $this->forIdentity;
	}

	/**
	 * @param string $forTable
	 * @return void
	 */
	public function setForTable($forTable) {
		$this->forTable = $forTable;
	}

	/**
	 * @return string
	 */
	public function getForTable() {
		return $this->forTable;
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
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $sheet
	 * @return void
	 */
	public function setSheet($sheet) {
		$this->sheet = $sheet;
	}

	/**
	 * @return string
	 */
	public function getSheet() {
		return $this->sheet;
	}

	/**
	 * @param Value[] $values
	 * @return void
	 */
	public function setAttributeValues($values) {
		$this->attributeValues = $values;
	}

	/**
	 * @return Value[]
	 */
	public function getAttributeValues() {
		return $this->attributeValues;
	}

	/**
	 * Shortcut, virtual: Get first Value (ideal for
	 * single-value attributes)
	 *
	 * @return Value
	 */
	public function getValue() {
		return reset($this->attributeValues);
	}

	/**
	 * Shortcut, virtual: Set values to only contain
	 * this single Value regardless of prior Values.
	 *
	 * @param Value $value
	 */
	public function setValue(Value $value) {
		$this->attributeValues = new ObjectStorage();
		$this->attributeValues->attach($value);
	}

}
