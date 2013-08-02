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
 * OOP representation of a TypoScript ConfigurationProvider
 * definition. Loads TypoScript settings from an array and
 * uses this settings array when asked to return various
 * values; Form and Grid instances for example.
 *
 * @package Flux
 * @subpackage Provider/Configuration
 */
class Tx_Flux_Provider_Configuration_TypoScriptConfigurationProvider extends Tx_Flux_Provider_AbstractConfigurationProvider implements Tx_Flux_Provider_ConfigurationProviderInterface {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var Tx_Flux_Form
	 */
	protected $form = NULL;

	/**
	 * @var Tx_Flux_Grid
	 */
	protected $grid = NULL;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function loadSettings(array $settings) {
		if (TRUE === isset($settings['name'])) {
			$this->setName($settings['name']);
		}
		if (TRUE === isset($settings['form'])) {
			$settings['form'] = Tx_Flux_Form::createFromDefinition($settings['form']);
		}
		if (TRUE === isset($settings['grid'])) {
			$settings['grid'] = Tx_Flux_Form_Container_Grid::createFromDefinition($settings['grid']);
		}
		foreach ($settings as $name => $value) {
			$this->$name = $value;
		}
		$GLOBALS['TCA'][$this->tableName]['columns'][$this->fieldName]['config']['type'] = 'flex';
		$this->settings = $settings;
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
	 * @param array $row
	 * @return Tx_Flux_Form
	 */
	public function getForm(array $row) {
		if (NULL !== $this->form) {
			return $this->form;
		}
		return parent::getForm($row);
	}

	/**
	 * @param array $row
	 * @return Tx_Flux_Form_Container_Grid
	 */
	public function getGrid(array $row) {
		if (NULL !== $this->grid) {
			return $this->grid;
		}
		return parent::getGrid($row);
	}

}
