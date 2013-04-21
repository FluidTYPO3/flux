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
 * Textarea FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_TextViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

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
	 * Render method
	 * @return void
	 */
	public function render() {
		if ($this->arguments['enableRichText'] && $this->arguments['defaultExtras'] == '') {
				// a NULL value causes the FieldStructureProvider to insert the TS
			$this->configuration['defaultExtras'] = NULL;
		} else {
			$this->configuration['defaultExtras'] = $this->arguments['defaultExtras'];
		}
		parent::render();
	}

	/**
	 * @return array
	 */
	public function renderConfiguration() {
		$configuration = $this->getBaseConfig();
		$fieldConfiguration = array(
			'type' => 'text',
			'name' => $configuration['name'],
			'rows' => $configuration['rows'],
			'cols' => $configuration['cols'],
			'eval' => $configuration['validate'],
			'default' => $configuration['default']
		);
		return $fieldConfiguration;
	}

	/**
	 * @return array
	 */
	public function createStructure() {
		if ($this->configuration['defaultExtras'] === NULL) {
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManagerInterface');
			$typoScript = $configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			$defaultExtras = $typoScript['plugin.']['tx_flux.']['settings.']['flexform.']['rteDefaults'];
		} else {
			$defaultExtras = $this->configuration['defaultExtras'];
		}
		$structure = parent::createStructure();
		$structure['TCEforms']['defaultExtras'] = $defaultExtras;
		return $structure;
	}

}
