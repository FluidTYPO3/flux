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
abstract class Tx_Flux_Form_AbstractFormContainer extends Tx_Flux_Form_AbstractFormComponent implements Tx_Flux_Form_ContainerInterface, Tx_Flux_Form_FieldContainerInterface {

	/**
	 * @var SplObjectStorage
	 */
	protected $children;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->children = new SplObjectStorage();
	}

	/**
	 * @param Tx_Flux_Form_FormInterface $child
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function add(Tx_Flux_Form_FormInterface $child) {
		if (FALSE === $this->children->contains($child)) {
			$this->children->attach($child);
		}
	}

	/**
	 * @param array|Traversable $children
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function addAll($children) {
		foreach ($children as $child) {
			$this->add($child);
		}
		return $this;
	}

	/**
	 * @param string $childName
	 * @return Tx_Flux_Form_FormInterface|FALSE
	 */
	public function remove($childName) {
		foreach ($this->children as $child) {
			if ($childName === $child->getName()) {
				$this->children->detach($child);
				$this->children->rewind();
				return $child;
			}
		}
		return FALSE;
	}

	/**
	 * @param Tx_Flux_Form_FormInterface $child
	 * @return boolean
	 */
	public function has(Tx_Flux_Form_FormInterface $child) {
		return (FALSE !== $this->get($child->getName()));
	}

	/**
	 * @param string $childName
	 * @return Tx_Flux_Form_FormInterface|FALSE
	 */
	public function get($childName) {
		foreach ($this->children as $existingChild) {
			if ($childName === $existingChild->getName()) {
				return $existingChild;
			}
		}
		return FALSE;
	}

	/**
	 * @return Tx_Flux_Form_FormInterface|FALSE
	 */
	public function last() {
		$result = FALSE;
		foreach ($this->children as $result) {
			continue;
		}
		$this->children->rewind();
		return $result;
	}

	/**
	 * @return array
	 */
	public function build() {
		return $this->buildChildren();
	}

	/**
	 * @return array
	 */
	protected function buildChildren() {
		$structure = array();
		/** @var Tx_Flux_Form_FormInterface[] $children */
		$children = $this->children;
		foreach ($children as $child) {
			$name = $child->getName();
			$structure[$name] = $child->build();
		}
		return array_reverse($structure, FALSE);;
	}

}
