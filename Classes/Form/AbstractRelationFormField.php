<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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

/**
 * @package Flux
 * @subpackage Form
 */
abstract class Tx_Flux_Form_AbstractRelationFormField extends Tx_Flux_Form_AbstractMultiValueFormField implements Tx_Flux_Form_RelationFieldInterface {

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $condition;

	/**
	 * @var string
	 */
	protected $foreignField;

	/**
	 * @var string|NULL
	 */
	protected $manyToMany = NULL;

	/**
	 * @param string $type
	 * @return array
	 */
	public function prepareConfiguration($type) {
		$configuration = parent::prepareConfiguration('select');
		$configuration['foreign_table'] = $this->getTable();
		$configuration['foreign_field'] = $this->getForeignField();
		$configuration['foreign_table_where'] = $this->getCondition();
		$configuration['MM'] = $this->getManyToMany();
		return $configuration;
	}

	/**
	 * @param string $condition
	 */
	public function setCondition($condition) {
		$this->condition = $condition;
	}

	/**
	 * @return string
	 */
	public function getCondition() {
		return $this->condition;
	}

	/**
	 * @param string $foreignField
	 */
	public function setForeignField($foreignField) {
		$this->foreignField = $foreignField;
	}

	/**
	 * @return string
	 */
	public function getForeignField() {
		return $this->foreignField;
	}

	/**
	 * @param NULL|string $manyToMany
	 */
	public function setManyToMany($manyToMany) {
		$this->manyToMany = $manyToMany;
	}

	/**
	 * @return NULL|string
	 */
	public function getManyToMany() {
		return $this->manyToMany;
	}

	/**
	 * @param string $table
	 */
	public function setTable($table) {
		$this->table = $table;
	}

	/**
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

}
