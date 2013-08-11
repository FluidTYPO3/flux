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
interface Tx_Flux_Form_FormInterface {

	/**
	 * @return array
	 */
	public function build();

	/**
	 * @param string $name
	 */
	public function setName($name);

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @param string $label
	 */
	public function setLabel($label);

	/**
	 * @return string
	 */
	public function getLabel();

	/**
	 * @param string $localLanguageFileRelativePath
	 * @return Tx_Flux_FormInterface
	 */
	public function setLocalLanguageFileRelativePath($localLanguageFileRelativePath);

	/**
	 * @return string
	 */
	public function getLocalLanguageFileRelativePath();


	/**
	 * @param boolean $disableLocalLanguageLabels
	 * @return Tx_Flux_FormInterface
	 */
	public function setDisableLocalLanguageLabels($disableLocalLanguageLabels);

	/**
	 * @return boolean
	 */
	public function getDisableLocalLanguageLabels();

	/**
	 * @param Tx_Flux_Form_ContainerInterface $parent
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function setParent($parent);

	/**
	 * @return Tx_Flux_Form_FormContainerInterface
	 */
	public function getParent();

	/**
	 * @return Tx_Flux_Form_FormContainerInterface
	 */
	public function getRoot();

	/**
	 * @param string $type
	 * @return boolean
	 */
	public function isChildOfType($type);

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function createField($type, $name, $label = NULL);

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return Tx_Flux_Form_ContainerInterface
	 */
	public function createContainer($type, $name, $label = NULL);

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return Tx_Flux_Form_WizardInterface
	 */
	public function createWizard($type, $name, $label = NULL);

}
