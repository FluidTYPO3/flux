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
 * Field Wizard: Add
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field/Wizard
 */
class Tx_Flux_ViewHelpers_Flexform_Field_Wizard_AddViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_Wizard_AbstractWizardViewHelper implements Tx_Flux_ViewHelpers_Flexform_Field_Wizard_WizardViewHelperInterface {

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerArgument('title', 'string', 'Title of the Add Wizard', FALSE, 'Add new record');
		$this->registerArgument('table', 'string', 'Table name that records are added to', TRUE);
		$this->registerArgument('pid', 'mixed', 'Storage page UID or (as is default) ###CURRENT_PID###', FALSE, '###CURRENT_PID###');
		$this->registerArgument('setValue', 'string', 'How to treat the record once created', FALSE, 'prepend');
		$this->registerArgument('hideParent', 'boolean', 'If TRUE, hides the "real" field as a hidden input field and renders the wizard', FALSE, FALSE);
	}

	/**
	 * Build the configuration array
	 *
	 * @return array
	 */
	public function build() {
		return array(
			'add' => array(
				'type' => 'script',
				'title' => $this->arguments['title'],
				'icon' => 'add.gif',
				'script' => 'wizard_add.php',
				'hideParent' => (bool) $this->arguments['hideParent'] === TRUE ? 1 : 0,
				'params' => array(
					'table' => $this->arguments['table'],
					'pid' => $this->arguments['pid'],
					'setValue' => $this->arguments['setValue']
				)
			)
		);
	}

}

?>