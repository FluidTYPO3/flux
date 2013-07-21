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
 * Flexform Userfunc field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_UserFuncViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('userFunc', 'string', 'Classname->function notation of UserFunc to be called, example "Tx_Myext_Configuration_FlexForms_MyField->renderField" - Extbase classes need autoload registry for this', TRUE);
		$this->registerArgument('arguments', 'array', 'Optional array of arguments to pass to the UserFunction building this field');
	}

	/**
	 * Render method
	 * @param string $type
	 * @return Tx_Flux_Form_Field_UserFunction
	 */
	public function getComponent($type = 'UserFunction') {
		/** @var Tx_Flux_Form_Field_UserFunction $user */
		$user = $this->getPreparedComponent($type);
		$user->setFunction($this->arguments['userFunc']);
		$user->setArguments($this->arguments['arguments']);
		return $user;
	}

}
