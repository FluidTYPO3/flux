<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Input FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_InputViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('eval', 'string', 'FlexForm-type validation configuration for this input', FALSE, 'trim');
		$this->registerArgument('size', 'integer', 'Size of field', FALSE, 32);
		$this->registerArgument('maxCharacters', 'integer', 'Maximum number of characters allowed', FALSE);
		$this->registerArgument('minimum', 'integer', 'Minimum value for integer type fields', FALSE);
		$this->registerArgument('maximum', 'integer', 'Maximum value for integer type fields', FALSE);
		$this->registerArgument('placeholder', 'string', 'Placeholder text which vanishes if field is filled and/or field is focused');
	}

	/**
	 * @return Tx_Flux_Form_Field_Input
	 */
	public function getComponent() {
		/** @var Tx_Flux_Form_Field_Input $input */
		$input = $this->getPreparedComponent('Input');
		$input->setValidate($this->arguments['eval']);
		$input->setMaxCharacters($this->arguments['maxCharacters']);
		$input->setMinimum($this->arguments['minimum']);
		$input->setMaximum($this->arguments['maximum']);
		$input->setPlaceholder($this->arguments['placeholder']);
		$input->setSize($this->arguments['size']);
		return $input;
	}

}
