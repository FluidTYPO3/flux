<?php
namespace FluidTYPO3\Flux\Form;
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

/**
 * @package Flux
 * @subpackage Form
 */
interface FieldInterface extends FormInterface {

	/**
	 * @return array
	 */
	public function buildConfiguration();

	/**
	 * @param boolean $clearable
	 * @return FieldInterface
	 */
	public function setClearable($clearable);

	/**
	 * @return boolean
	 */
	public function getClearable();

	/**
	 * @param boolean $required
	 * @return FieldInterface
	 */
	public function setRequired($required);

	/**
	 * @return boolean
	 */
	public function getRequired();

	/**
	 * @param mixed $default
	 * @return FieldInterface
	 */
	public function setDefault($default);

	/**
	 * @return mixed
	 */
	public function getDefault();

	/**
	 * @param string $transform
	 * @return FieldInterface
	 */
	public function setTransform($transform);

	/**
	 * @return string
	 */
	public function getTransform();

	/**
	 * @param string $displayCondition
	 * @return FieldInterface
	 */
	public function setDisplayCondition($displayCondition);

	/**
	 * @return string
	 */
	public function getDisplayCondition();

	/**
	 * @param boolean $requestUpdate
	 * @return FieldInterface
	 */
	public function setRequestUpdate($requestUpdate);

	/**
	 * @return boolean
	 */
	public function getRequestUpdate();

	/**
	 * @param integer $inherit
	 * @return FieldInterface
	 */
	public function setInherit($inherit);

	/**
	 * @return integer
	 */
	public function getInherit();

	/**
	 * @param boolean $inheritEmpty
	 * @return FieldInterface
	 */
	public function setInheritEmpty($inheritEmpty);

	/**
	 * @return boolean
	 */
	public function getInheritEmpty();

	/**
	 * @param boolean $stopInheritance
	 * @return FieldInterface
	 */
	public function setStopInheritance($stopInheritance);

	/**
	 * @return boolean
	 */
	public function getStopInheritance();

	/**
	 * @param boolean $exclude
	 * @return FieldInterface
	 */
	public function setExclude($exclude);

	/**
	 * @return boolean
	 */
	public function getExclude();

	/**
	 * @param boolean $enable
	 * @return FieldInterface
	 */
	public function setEnable($enable);

	/**
	 * @return boolean
	 */
	public function getEnable();

	/**
	 * @param WizardInterface $wizard
	 * @return FormInterface
	 */
	public function add(WizardInterface $wizard);

	/**
	 * @param string $wizardName
	 * @return WizardInterface|FALSE
	 */
	public function remove($wizardName);

}
