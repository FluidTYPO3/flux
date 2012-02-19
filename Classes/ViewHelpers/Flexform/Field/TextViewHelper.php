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
 * Textarea FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_TextViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	const RTE_DEFAULT = "richtext[*]:rte_transform[mode=ts_css]";

	/**
	 * Initialize
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('validate', 'string', 'FlexForm-type validation configuration for this input', FALSE, 'trim');
		$this->registerArgument('cols', 'int', 'Number of columns in editor', FALSE, 85);
		$this->registerArgument('rows', 'int', 'Number of rows in editor', FALSE, 10);
		$this->registerArgument('defaultExtras', 'string', 'FlexForm-syntax "defaultExtras" definition, example: "richtext[*]:rte_transform[mode=ts_css]"', FALSE, '');
		$this->registerArgument('enableRichText', 'boolean', 'Shortcut for adding "richtext[*]:rte_transform[mode=ts_css]" to "defaultExtras"', FALSE, FALSE);
	}

	/**
	 * Render method
	 */
	public function render() {
		$config = $this->getBaseConfig();
		$config['type'] = 'Text';
		$config['validate'] = $this->arguments['validate'];
		$config['cols'] = $this->arguments['cols'];
		$config['rows'] = $this->arguments['rows'];
		if ($this->arguments['enableRichText'] && $this->arguments['defaultExtras'] == '') {
			$config['defaultExtras'] = self::RTE_DEFAULT;
		} else {
			$config['defaultExtras'] = $this->arguments['defaultExtras'];
		}
		$this->addField($config);
		$this->renderChildren();
	}

}

?>