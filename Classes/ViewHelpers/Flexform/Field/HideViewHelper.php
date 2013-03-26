<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * Hide Field
 *
 * Hides an actual record field from output by manipulating TS
 * for tt_content.
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_HideViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->overrideArgument('label', 'string', 'Disabled label which is normally required - not used here, so not required', FALSE, NULL);
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		$storage = (array) $this->getStorage();
		array_push($storage['hidefields'], $this->arguments['name']);
		$this->setStorage($storage);
	}

}
