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
abstract class Tx_Flux_Form_AbstractWizard extends Tx_Flux_Form_AbstractFormComponent implements Tx_Flux_Form_WizardInterface {

	/**
	 * @var boolean
	 */
	protected $hideParent = FALSE;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $icon;

	/**
	 * @var string
	 */
	protected $script;

	/**
	 * @return array
	 */
	public function build() {
		$structure = array(
			'type' => $this->type,
			'title' => $this->getLabel(),
			'icon' => $this->icon,
			'script' => $this->script,
			'hideParent' => intval($this->getHideParent()),
		);
		$configuration = $this->buildConfiguration();
		$structure = array_merge($structure, $configuration);
		return $structure;
	}

	/**
	 * @param boolean $hideParent
	 * @return Tx_Flux_Form_Wizard_Add
	 */
	public function setHideParent($hideParent) {
		$this->hideParent = $hideParent;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getHideParent() {
		return $this->hideParent;
	}

	/**
	 * @return boolean
	 */
	public function hasChildren() {
		return FALSE;
	}
}

