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
 * Tree (select supertype) FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_TreeViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_SelectViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('parentField', 'string', 'Field containing UID of parent record', TRUE);
		$this->registerArgument('expandAll', 'boolean', 'If TRUE, expands all branches', FALSE, FALSE);
		$this->registerArgument('showHeader', 'boolean', 'If TRUE, displays tree header', FALSE, FALSE);
		$this->registerArgument('width', 'integer', 'Width of TreeView component', FALSE, 400);
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		$config = $this->getFieldConfig();
		$config['type'] = 'Select';
		$config['renderMode'] = 'tree';
		$config['treeConfig'] = array(
			'parentField' => $this->arguments['parentField'],
			'expandAll' => $this->arguments['expandAll'],
			'showHeader' => $this->arguments['showHeader'],
			'width' => $this->arguments['width']
		);
		$this->addField($config);
	}

}
