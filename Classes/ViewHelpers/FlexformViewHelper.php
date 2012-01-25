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
 * ************************************************************* */

/**
 * FlexForm configuration container ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers
 */
class Tx_Flux_ViewHelpers_FlexformViewHelper extends Tx_Flux_Core_ViewHelper_AbstractFlexformViewHelper {

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerArgument('id', 'string', 'Identifier of this Flexible Content Element, [a-z0-9\-] allowed', TRUE);
		$this->registerArgument('label', 'string', 'Label for this FlexForm, used when human-readable labels are displayed', FALSE, NULL);
		$this->registerArgument('description', 'string', 'Short description of this content element', FALSE);
		$this->registerArgument('icon', 'string', 'Optional icon file to use when displaying this content element in the new content element wizard', FALSE, '../typo3conf/ext/fed/Resources/Public/Icons/Plugin.png');
		$this->registerArgument('mergeValues', 'boolean', 'If TRUE, enables overriding of record values with corresponding values from this FlexForm', FALSE, FALSE);
		$this->registerArgument('enabled', 'boolean', 'If FALSE, makes the FCE inactive', FALSE, TRUE);
	}

	/**
	 * Render method
	 */
	public function render() {
		$this->setStorage(array(
			'label' => $this->arguments['label'],
			'enabled' => $this->arguments['enabled'],
			'mergeValues' => $this->arguments['mergeValues'],
			'id' => $this->arguments['id'],
			'fields' => array(),
		));
		$this->renderChildren();
		return '';
	}

}

?>