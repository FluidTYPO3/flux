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
 * Base class for all FlexForm fields.
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
abstract class Tx_Flux_ViewHelpers_Flexform_Field_AbstractMultiValueFieldViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('validate', 'string', 'FlexForm-type validation configuration for this input', FALSE, 'trim');
		$this->registerArgument('size', 'integer', 'Size of the selector box', FALSE, 1);
		$this->registerArgument('multiple', 'boolean', 'If TRUE, allows multiple selections', FALSE, FALSE);
		$this->registerArgument('minItems', 'integer', 'Minimum required number of items to be selected', FALSE, 0);
		$this->registerArgument('maxItems', 'integer', 'Maxium allowed number of items to be selected', FALSE, 1);
		$this->registerArgument('itemListStyle', 'string', 'Overrides the default list style when maxItems > 1', FALSE, NULL);
		$this->registerArgument('selectedListStyle', 'string', 'Overrides the default selected list style when maxItems > 1 and renderMode is default', FALSE, NULL);
	}

	/**
	 * @param string $type
	 * @return Tx_Flux_Form_MultiValueFieldInterface
	 */
	protected function getPreparedComponent($type) {
		/** @var Tx_Flux_Form_MultiValueFieldInterface $component */
		$component = parent::getPreparedComponent($type);
		$component->setMinItems($this->arguments['minItems']);
		$component->setMaxItems($this->arguments['maxItems']);
		$component->setSize($this->arguments['size']);
		$component->setMultiple($this->arguments['multiple']);
		$component->setItemListStyle($this->arguments['itemListStyle']);
		$component->setSelectedListStyle($this->arguments['selectedListStyle']);
		return $component;
	}


}
