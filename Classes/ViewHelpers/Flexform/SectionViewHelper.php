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
 *****************************************************************/

/**
 * FlexForm field section ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Flexform_SectionViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the attribute, FlexForm XML-valid tag name string', TRUE);
		$this->registerArgument('label', 'string', 'Label for section, can be LLL: value. Optional - if not specified, ' .
			'Flux tries to detect an LLL label named "flux.fluxFormId.sections.foobar" based on section name, in scope of ' .
			'extension rendering the Flux form.', FALSE, NULL);
		//$this->registerArgument('maxItems', 'integer', 'Maximum allowed items', FALSE);
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		$this->configuration = $this->renderConfiguration();
		$this->structure = $this->createStructure();
		$this->addField($this->configuration);
	}

	/**
	 * @return array
	 */
	public function renderConfiguration() {
		if ($this->viewHelperVariableContainer->exists('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sheet') === TRUE) {
			$sheet = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sheet');
		} else {
			$sheet = array(
				'name' => 'options',
				'label' => 'Options',
			);
		}
		if ($this->viewHelperVariableContainer->exists('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section') === TRUE) {
			$parentSection = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section');
			$parentSectionLabels = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionLabels');
			$parentSectionObjectName = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionObjectName');
		} else {
			$parentSection = $parentSectionObjectName = $parentSectionLabels = NULL;
		}

		$baseConfig = array();
		$baseConfig['name'] = $this->arguments['name'];
		$baseConfig['label'] = $this->getLabel();
		//$baseConfig['maxItems'] = $this->arguments['maxItems'];
		$baseConfig['type'] = 'section';
		$baseConfig['fields'] = array();
		$baseConfig['enabled'] = TRUE;
		$baseConfig['sheet'] = $sheet;
		$baseConfig['wrap'] = FALSE;

		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section', $baseConfig);
		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionLabels', array());
		$this->renderChildren();

		$compiledConfig = (array) $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section');
		$compiledConfig['labels'] = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionLabels');

		$this->viewHelperVariableContainer->remove('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section');
		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section', $parentSection);
		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionLabels', $parentSectionLabels);
		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'sectionObjectName', $parentSectionObjectName);

		$config = array_merge($baseConfig, $compiledConfig);
		return $config;
	}

	/**
	 * @return array
	 */
	public function createStructure() {
		$configuration = $this->configuration;
		$fieldStructureArray = array(
			'title' => $configuration['label'], // read only by >4.7 and required in order to prevent the tx_templavoila from generating a deprecation warning
			'tx_templavoila' => array( // TODO: remove this when <4.7 no longer needs to be supported.
				'title' => $configuration['label']
			),
			'type' => 'array',
			'section' => 1,
			'el' => array()
		);
		$objects = array();
		foreach ($configuration['fields'] as $field) {
			$name = $field['sectionObjectName'];
			if (isset($objects[$name]) === FALSE) {
				$objects[$name] = array();
			}
			array_push($objects[$name], $field);
		};
		foreach ($objects as $objectName => $objectFields) {
			$fieldStructureArray['el'][$objectName] = array(
				'type' => 'array',
				'title' => $configuration['labels'][$objectName],
				'tx_templavoila' => array(
					'title' => $configuration['labels'][$objectName]
				),
				'el' => array(),
			);
			foreach ($objectFields as $field) {
				$name = $field['name'];
				$fieldStructureArray['el'][$objectName]['el'][$name] = $field->getStructure();
			}
		}
		return $fieldStructureArray;
	}


}
