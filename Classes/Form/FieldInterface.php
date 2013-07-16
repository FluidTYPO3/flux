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
interface Tx_Flux_Form_FieldInterface extends Tx_Flux_Form_FormInterface {

	/**
	 * @return array
	 */
	public function buildConfiguration();

	/**
	 * @param boolean $clearable
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setClearable($clearable);

	/**
	 * @return boolean
	 */
	public function getClearable();

	/**
	 * @param boolean $required
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setRequired($required);

	/**
	 * @return boolean
	 */
	public function getRequired();

	/**
	 * @param mixed $default
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setDefault($default);

	/**
	 * @return mixed
	 */
	public function getDefault();

	/**
	 * @param string $transform
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setTransform($transform);

	/**
	 * @return string
	 */
	public function getTransform();

	/**
	 * @param string $displayCondition
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setDisplayCondition($displayCondition);

	/**
	 * @return string
	 */
	public function getDisplayCondition();

	/**
	 * @param boolean $requestUpdate
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setRequestUpdate($requestUpdate);

	/**
	 * @return boolean
	 */
	public function getRequestUpdate();

	/**
	 * @param integer $inherit
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setInherit($inherit);

	/**
	 * @return integer
	 */
	public function getInherit();

	/**
	 * @param boolean $inheritEmpty
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setInheritEmpty($inheritEmpty);

	/**
	 * @return boolean
	 */
	public function getInheritEmpty();

	/**
	 * @param boolean $stopInheritance
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setStopInheritance($stopInheritance);

	/**
	 * @return boolean
	 */
	public function getStopInheritance();

	/**
	 * @param boolean $exclude
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setExclude($exclude);

	/**
	 * @return boolean
	 */
	public function getExclude();

	/**
	 * @param boolean $enable
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setEnable($enable);

	/**
	 * @return boolean
	 */
	public function getEnable();

	/**
	 * @param Tx_Flux_Form_WizardInterface $wizard
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function add(Tx_Flux_Form_WizardInterface $wizard);

	/**
	 * @param string $wizardName
	 * @return Tx_Flux_Form_WizardInterface|FALSE
	 */
	public function remove($wizardName);

}
