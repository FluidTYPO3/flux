<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * Field Wizard: List
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field/Wizard
 */
class Tx_Flux_ViewHelpers_Flexform_Field_Wizard_ListViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_Wizard_AbstractWizardViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('table', 'string', 'Table name that records are added to', TRUE);
		$this->registerArgument('pid', 'mixed', 'Storage page UID or (as is default) ###CURRENT_PID###', FALSE, '###CURRENT_PID###');
		$this->registerArgument('width', 'integer', 'Width of the popup window', FALSE, 500);
		$this->registerArgument('height', 'integer', 'height of the popup window', FALSE, 500);
	}

	/**
	 * @return Tx_Flux_Form_Wizard_List
	 */
	public function getComponent() {
		/** @var Tx_Flux_Form_Wizard_List $component */
		$component = $this->getPreparedComponent('List');
		$component->setTable($this->arguments['table']);
		$component->setStoragePageUid($this->arguments['pid']);
		$component->setWidth($this->arguments['width']);
		$component->setHeight($this->arguments['height']);
		return $component;
	}

}
