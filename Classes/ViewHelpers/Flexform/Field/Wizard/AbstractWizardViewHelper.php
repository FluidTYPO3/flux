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
 * Base class for Field Wizard style ViewHelpers
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field/Wizard
 */
class Tx_Flux_ViewHelpers_Flexform_Field_Wizard_AbstractWizardViewHelper extends Tx_Flux_ViewHelpers_AbstractFlexformViewHelper {

	/**
	 * @var string
	 */
	protected $label = NULL;

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('label', 'string', 'Optional title of this Wizard', FALSE, $this->label);
		$this->registerArgument('hideParent', 'boolean', 'If TRUE, hides the parent field', FALSE, FALSE);
	}

	/**
	 * @return void
	 */
	public function render() {
		$component = $this->getComponent();
		$field = $this->getContainer();
		$field->add($component);
	}

	/**
	 * @param string $type
	 * @return Tx_Flux_Form_WizardInterface
	 */
	protected function getPreparedComponent($type) {
		/** @var Tx_Flux_Form_WizardInterface $component */
		$component = $this->objectManager->get('Tx_Flux_Form_Wizard_' . $type);
		$component->setHideParent($this->arguments['hideParent']);
		$component->setLabel($this->arguments['label']);
		return $component;
	}

}
