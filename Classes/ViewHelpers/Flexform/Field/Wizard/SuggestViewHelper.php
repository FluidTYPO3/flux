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
 * Field Wizard: Suggest
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field/Wizard
 */
class Tx_Flux_ViewHelpers_Flexform_Field_Wizard_SuggestViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_Wizard_AbstractWizardViewHelper implements Tx_Flux_ViewHelpers_Flexform_Field_Wizard_WizardViewHelperInterface {

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerArgument('table', 'string', 'Table to search. If left out will use the table defined by the parent field', FALSE, NULL);
		$this->registerArgument('pidList', 'string', 'List of storage page UIDs', FALSE, '0');
		$this->registerArgument('pidDepth', 'integer', 'Depth of recursive storage page UID lookups', FALSE, 99);
		$this->registerArgument('minimumCharacters', 'integer', 'Minimum number of characters that must be typed before search begins', FALSE, 1);
		$this->registerArgument('maxPathTitleLength', 'integer', 'Maximum path segment length - crops titles over this length', FALSE, 15);
		$this->registerArgument('searchWholePhrase', 'boolean', 'A match requires a full word that matches the search value', FALSE, FALSE);
		$this->registerArgument('searchCondition', 'string', 'Search condition - for example, if table is pages "doktype = 1" to only allow standard pages', FALSE, '');
		$this->registerArgument('cssClass', 'string', 'Use this CSS class for all list items', FALSE, '');
		$this->registerArgument('receiverClass', 'string', 'Class reference, target class should be derived from "t3lib_tceforms_suggest_defaultreceiver"', FALSE, '');
		$this->registerArgument('renderFunc', 'string', 'Reference to function which processes all records displayed in results', FALSE, '');
	}

	/**
	 * Build the configuration array
	 *
	 * @return array
	 */
	public function build() {
		if ($this->arguments['table']) {
			return array(
				'suggest' . $this->arguments['table'] => array(
					'type' => 'suggest',
					$this->arguments['table'] => (array) $this->arguments
				)
			);
		} else {
			return array(
				'suggest' => array(
					'type' => 'suggest',
				)
			);
		}
	}

}

?>