<?php

/* * *************************************************************
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
 * ************************************************************* */

/**
 * Field Wizard: Edit
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field/Wizard
 */
class Tx_Flux_ViewHelpers_Flexform_Field_Wizard_EditViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_Wizard_AbstractWizardViewHelper implements Tx_Flux_ViewHelpers_Flexform_Field_Wizard_WizardViewHelperInterface {

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerArgument('title', 'string', 'Title of the Add Wizard', FALSE, 'Edit selected record');
		$this->registerArgument('width', 'integer', 'Width of the popup window', FALSE, 580);
		$this->registerArgument('height', 'integer', 'height of the popup window', FALSE, 580);
		$this->registerArgument('openOnlyIfSelected', 'boolean', 'Only open the edit dialog if an item is selected', FALSE, TRUE);
	}

	/**
	 * Build the configuration array
	 *
	 * @return array
	 */
	public function build() {
		return array(
			'edit' => array(
				'type' => 'popup',
				'title' => $this->arguments['title'],
				'icon' => 'edit2.gif',
				'script' => 'wizard_edit.php',
				'popup_onlyOpenIfSelected' => (bool) $this->arguments['openOnlyIfSelected'] === TRUE ? 1 : 0,
				'JSopenParams' => 'height=' . $this->arguments['height'] . ',width=' . $this->arguments['width'] . ',status=0,menubar=0,scrollbars=1'
			)
		);
	}

}

?>