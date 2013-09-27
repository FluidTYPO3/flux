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
 * Field Wizard: Color Picker
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field/Wizard
 */
class Tx_Flux_ViewHelpers_Flexform_Field_Wizard_ColorPickerViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_Wizard_AbstractWizardViewHelper {

	/**
	 * @var string
	 */
	protected $label = 'Choose color';

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('dim', 'string', 'Dimensions (WxH, e.g. 20x20) of color picker', FALSE, '20x20');
		$this->registerArgument('width', 'integer', 'Width of the popup window', FALSE, 450);
		$this->registerArgument('height', 'integer', 'height of the popup window', FALSE, 720);
		$this->registerArgument('exampleImg', 'string', 'Example image from which to pick colors', FALSE, 'EXT:flux/Resources/Public/Icons/ColorWheel.png');
	}

	/**
	 * @return Tx_Flux_Form_Wizard_ColorPicker
	 */
	public function getComponent() {
		/** @var Tx_Flux_Form_Wizard_ColorPicker $component */
		$component = $this->getPreparedComponent('ColorPicker');
		$component->setIcon($this->arguments['exampleImg']);
		$component->setDimensions($this->arguments['dim']);
		$component->setWidth($this->arguments['width']);
		$component->setHeight($this->arguments['height']);
		return $component;
	}

}
