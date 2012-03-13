<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * FlexForm field section ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Flexform_SectionViewHelper extends Tx_Flux_Core_ViewHelper_AbstractFlexformViewHelper {

	/**
	 * Initialize
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the attribute, FlexForm XML-valid tag name string', TRUE);
		$this->registerArgument('label', 'string', 'Label for the attribute, can be LLL: value', TRUE);
	}

	/**
	 * Render method
	 */
	public function render() {
		if ($this->viewHelperVariableContainer->exists('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sheet')) {
			$sheet = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sheet');
		} else {
			$sheet = array(
				'name' => 'options',
				'label' => 'Options',
			);
		}
		$baseConfig = array();
		$baseConfig['name'] = $this->arguments['name'];
		$baseConfig['label'] = $this->arguments['label'];
		$baseConfig['type'] = 'Section';
		$baseConfig['fields'] = array();
		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section', $baseConfig);
		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionLabels', array());
		$this->renderChildren();
		if ($this->viewHelperVariableContainer->exists('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionObjectName')) {
			$this->viewHelperVariableContainer->remove('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionObjectName');
		}
		$compiledConfig = (array) $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section');
		$this->viewHelperVariableContainer->remove('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section');
		$config = array_merge($baseConfig, $compiledConfig);
		$config['enabled'] = TRUE;
		$config['sheet'] = $sheet;
		$config['section'] = NULL;
		$config['wrap'] = FALSE;
		$config['labels'] = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionLabels');
		$this->addField($config);
	}

}

?>