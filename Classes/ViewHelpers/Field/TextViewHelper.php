<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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
 * Textarea FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class TextViewHelper extends AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('validate', 'string', 'FlexForm-type validation configuration for this input', FALSE, 'trim');
		$this->registerArgument('cols', 'int', 'Number of columns in editor', FALSE, 85);
		$this->registerArgument('rows', 'int', 'Number of rows in editor', FALSE, 10);
		$this->registerArgument('defaultExtras', 'string', 'FlexForm-syntax "defaultExtras" definition, example: "richtext[*]:rte_transform[mode=ts_css]"', FALSE, '');
		$this->registerArgument('enableRichText', 'boolean', 'Shortcut for adding value of TS plugin.tx_flux.settings.flexform.rteDefaults to "defaultExtras"', FALSE, FALSE);
	}

	/**
	 * @return \FluidTYPO3\Flux\Form\Field\Text
	 */
	public function getComponent() {
		/** @var \FluidTYPO3\Flux\Form\Field\Text $text */
		$text = $this->getPreparedComponent('Text', $this->arguments['name'], $this->arguments['label']);
		$text->setValidate($this->arguments['validate']);
		$text->setColumns($this->arguments['cols']);
		$text->setRows($this->arguments['rows']);
		$text->setDefaultExtras($this->arguments['defaultExtras']);
		$text->setEnableRichText($this->arguments['enableRichText']);
		return $text;
	}

}
