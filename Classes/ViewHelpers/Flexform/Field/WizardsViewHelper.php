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
 * Wizards ViewHelper. When used as child of a FlexForm field ViewHelper, the
 * contents of this tag gets placed in the <wizards> ... </wizards> section of
 * the output FlexForm.
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_WizardsViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * @return string
	 */
	public function build() {
		return $this->renderChildren();
	}

}

?>