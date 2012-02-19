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
 * Select-type FlexForm field ViewHelper
 *
 * @package Fed
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_SelectViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * Initialize
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('validate', 'string', 'FlexForm-type validation configuration for this input', FALSE, 'trim');
		$this->registerArgument('items', 'array', 'Items for the selector multidimensional, matching FlexForm/TCA', FALSE, array());
		$this->registerArgument('commaSeparatedItems', 'string', 'CSV list of item values which are both labels and values', FALSE);
		$this->registerArgument('size', 'integer', 'Size of the selector box', FALSE, 1);
		$this->registerArgument('multiple', 'boolean', 'If TRUE, allows multiple selections', FALSE, FALSE);
		$this->registerArgument('minItems', 'integer', 'Minimum required number of items to be selected', FALSE, 0);
		$this->registerArgument('maxItems', 'integer', 'Maxium allowed number of items to be selected', FALSE, 1);
		$this->registerArgument('table', 'string', 'Define foreign table name to turn selector into a record selector for that table', FALSE, NULL);
		$this->registerArgument('condition', 'string', 'Condition to use when selecting from "foreignTable", supports FlexForm "foregin_table_where" markers', FALSE, NULL);
		$this->registerArgument('mm', 'string', 'Optional name of MM table to use for record selection', FALSE, NULL);
		$this->registerArgument('showThumbs', 'boolean', 'If TRUE, adds thumbnail display when editing in BE', FALSE, TRUE);
		$this->registerArgument('itemsProcFunc', 'string', 'Optional class name of data provider to fill select options');
		$this->registerArgument('suggest', 'boolean', 'Add "suggest" box to search for entries', FALSE, FALSE);
	}

	/**
	 * Render method
	 */
	public function render() {
		$config = $this->getFieldConfig();
		$this->addField($config);
		$this->renderChildren();
	}

	/**
	 * @return array
	 */
	protected function getFieldConfig() {
		$config = $this->getBaseConfig();
		$config['type'] = 'Select';
		if ($this->arguments['commaSeparatedItems']) {
			$config['items'] = array();
			$itemNames = t3lib_div::trimExplode(',', $this->arguments['commaSeparatedItems']);
			foreach ($itemNames as $itemName) {
				array_push($config['items'], array($itemName, $itemName));
			}
		} else {
			$config['items'] = $this->arguments['items'];
		}
		$config['size'] = $this->arguments['size'];
		$config['minItems'] = $this->arguments['minItems'];
		$config['maxItems'] = $this->arguments['maxItems'];
		$config['multiple'] = $this->arguments['multiple'] ? 1 : 0;
		$config['table'] = $this->arguments['table'];
		$config['condition'] = $this->arguments['condition'];
		$config['mm'] = $this->arguments['mm'];
		$config['showThumbs'] = $this->getFlexFormBoolean($this->arguments['showThumbs']);
		$config['itemsProcFunc'] = $this->arguments['itemsProcFunc'];
		$config['suggest'] = $this->arguments['suggest'];
		return $config;
	}

}

?>