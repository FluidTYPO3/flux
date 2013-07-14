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
abstract class Tx_Flux_Form_AbstractFormField extends Tx_Flux_Form_AbstractFormComponent implements Tx_Flux_Form_FieldInterface {

	/**
	 * @var boolean
	 */
	protected $required = FALSE;

	/**
	 * @var mixed
	 */
	protected $default;

	/**
	 * @var string
	 */
	protected $transform;

	/**
	 * @var string
	 */
	protected $displayCondition = NULL;

	/**
	 * @var boolean
	 */
	protected $requestUpdate = FALSE;

	/**
	 * @var integer
	 */
	protected $inherit = 0;

	/**
	 * @var boolean
	 */
	protected $inheritEmpty = FALSE;

	/**
	 * @var boolean
	 */
	protected $stopInheritance = FALSE;

	/**
	 * @var boolean
	 */
	protected $clearable = FALSE;

	/**
	 * @var integer
	 */
	protected $repeat;

	/**
	 * @var boolean
	 */
	protected $exclude = TRUE;

	/**
	 * @var boolean
	 */
	protected $enable = TRUE;

	/**
	 * @var SplObjectStorage
	 */
	protected $wizards;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->wizards = new SplObjectStorage();
	}

	/**
	 * @param Tx_Flux_Form_WizardInterface $wizard
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function add(Tx_Flux_Form_WizardInterface $wizard) {
		if (FALSE === $this->wizards->contains($wizard)) {
			$this->wizards->attach($wizard);
		}
		return $this;
	}

	/**
	 * @param string $wizardName
	 * @return Tx_Flux_Form_WizardInterface|FALSE
	 */
	public function remove($wizardName) {
		foreach ($this->wizards as $wizard) {
			if ($wizardName === $wizard->getName()) {
				$this->wizards->detach($wizard);
				$this->wizards->rewind();
				return $wizard;
			}
		}
		return FALSE;
	}

	/**
	 * Creates a TCEforms configuration array based on the
	 * configuration stored in this ViewHelper. Calls the
	 * expected-to-be-overridden stub method getConfiguration()
	 * to return the TCE field configuration - see that method
	 * for information about how to implement that method.
	 *
	 * @return array
	 */
	public function build() {
		$fieldStructureArray = array(
			'TCEforms' => array(
				'label' => $this->getLabel(),
				'required' => intval($this->getRequired()),
				'config' => $this->buildConfiguration(),
				'displayCond' => $this->getDisplayCondition()
			)
		);
		$fieldStructureArray['TCEforms']['config']['wizards'] = $this->buildChildren();
		if (TRUE === $this->getRequestUpdate()) {
			$fieldStructureArray['TCEforms']['onChange'] = 'reload';
		}
		return $fieldStructureArray;
	}

	/**
	 * @return array
	 */
	protected function buildChildren() {
		$structure = array();
		/** @var Tx_Flux_Form_FormInterface[] $children */
		$children = $this->wizards;
		foreach ($children as $child) {
			$name = $child->getName();
			$structure[$name] = $child->build();
		}
		return $structure;
	}

	/**
	 * @param string $type
	 * @return array
	 */
	protected function prepareConfiguration($type) {
		$fieldConfiguration = array(
			'type' => $type,
			'transform' => $this->getTransform(),
			'default' => $this->getDefault(),
		);
		return $fieldConfiguration;
	}

	/**
	 * @param boolean $required
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setRequired($required) {
		$this->required = $required;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRequired() {
		return $this->required;
	}

	/**
	 * @param mixed $default
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setDefault($default) {
		$this->default = $default;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDefault() {
		if (FALSE === empty($this->default)) {
			$defaultValue = $this->default;
		} else {
			$defaultValue = NULL;
		}
		return $defaultValue;
	}

	/**
	 * @param string $transform
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setTransform($transform) {
		$this->transform = $transform;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTransform() {
		return $this->transform;
	}

	/**
	 * @param string $displayCondition
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setDisplayCondition($displayCondition) {
		$this->displayCondition = $displayCondition;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDisplayCondition() {
		return $this->displayCondition;
	}

	/**
	 * @param boolean $requestUpdate
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setRequestUpdate($requestUpdate) {
		$this->requestUpdate = $requestUpdate;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRequestUpdate() {
		return $this->requestUpdate;
	}

	/**
	 * @param integer $inherit
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setInherit($inherit) {
		$this->inherit = $inherit;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getInherit() {
		return $this->inherit;
	}

	/**
	 * @param boolean $inheritEmpty
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setInheritEmpty($inheritEmpty) {
		$this->inheritEmpty = $inheritEmpty;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getInheritEmpty() {
		return $this->inheritEmpty;
	}

	/**
	 * @param boolean $stopInheritance
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setStopInheritance($stopInheritance) {
		$this->stopInheritance = $stopInheritance;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getStopInheritance() {
		return $this->stopInheritance;
	}

	/**
	 * @param integer $repeat
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setRepeat($repeat) {
		$this->repeat = $repeat;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getRepeat() {
		return $this->repeat;
	}

	/**
	 * @param boolean $exclude
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setExclude($exclude) {
		$this->exclude = $exclude;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getExclude() {
		return $this->exclude;
	}

	/**
	 * @param boolean $enable
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setEnable($enable) {
		$this->enable = $enable;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEnable() {
		return $this->enable;
	}

	/**
	 * @param boolean $clearable
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setClearable($clearable) {
		$this->clearable = $clearable;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getClearable() {
		return $this->clearable;
	}

}
