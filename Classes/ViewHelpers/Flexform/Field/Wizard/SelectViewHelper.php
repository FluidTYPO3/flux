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
class Tx_Flux_ViewHelpers_Flexform_Field_Wizard_SelectViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_Wizard_AbstractWizardViewHelper implements Tx_Flux_ViewHelpers_Flexform_Field_Wizard_WizardViewHelperInterface {

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerArgument('mode', 'string', 'Selection mode - substitution, append or prepend', FALSE, 'substitution');
		$this->registerArgument('items', 'mixed', 'Comma-separated, comma-and-semicolon-separated or array list of possible values', TRUE);
		$this->registerArgument('hideParent', 'boolean', 'If TRUE, hides the "real" field as a hidden input field and renders the wizard', FALSE, FALSE);
	}

	/**
	 * Build the configuration array
	 *
	 * @return array
	 */
	public function build() {
		$fieldName = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'fieldName');
		return array(
			$fieldName . '_picker' => array(
				'type' => 'select',
				'mode' => $this->arguments['mode'],
				'hideParent' => (bool) $this->arguments['hideParent'] === TRUE ? 1 : 0,
				'items' => is_array($this->arguments['items']) ? $this->arguments['items'] : $this->buildItems($this->arguments['items'])
			)
		);
	}

	/**
	 * Builds an array of selector options based on a type of string
	 *
	 * @param string $itemsString
	 */
	protected function buildItems($itemsString) {
		$itemsString = trim($itemsString, ',');
		if (strpos($itemsString, ',') && strpos($itemsString, ';')) {
			$return = array();
			$items = explode(',', $itemsString);
			foreach ($items as $itemPair) {
				$item = explode(';', $itemPair);
				$return[$item[0]] = $item[1];
			}
			return $return;
		} else if (strpos($itemsString, ',')) {
			$items = explode(',', $itemsString);
			return array_combine($items, $items);
		} else {
			return array($itemsString => $itemsString);
		}

	}
}

?>