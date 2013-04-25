<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * Root FlexForm structure provider
 *
 * @package Flux
 * @subpackage Provider/Structure
 */
class Tx_Flux_Provider_Structure_FlexFormStructureProvider extends Tx_Flux_Provider_Structure_AbstractStructureProvider implements Tx_Flux_Provider_StructureProviderInterface {

	/**
	 * @param $configuration
	 * @return array
	 */
	public function render($configuration) {
		if (FALSE === is_array($configuration)) {
			$className = get_class($this);
			$exported = var_export($configuration, TRUE);
			$this->configurationService->message('Class ' . $className . ' asked to render an invalid configuration: ' . $exported, t3lib_div::SYSLOG_SEVERITY_FATAL);
			return array();
		}
		$sheets = array();
		foreach ($configuration['fields'] as $field) {
			if (FALSE === empty($field['sheet'])) {
				$sheet = $field['sheet'];
			} else {
				$sheet = array(
					'name' => 'options',
					'label' => Tx_Extbase_Utility_Localization::translate('tt_content.tx_flux_options', 'Flux'),
				);
			}
			$groupKey = $sheet['name'];
			$groupLabel = $sheet['label'];
			if (is_array($sheets[$groupKey]) === FALSE) {
				$sheets[$groupKey] = array(
					'name' => $groupKey,
					'label' => $groupLabel,
					'fields' => array()
				);
			}
			if ($field['section'] === NULL) {
				array_push($sheets[$groupKey]['fields'], $field);
			}
		}
		$dataStructArray = array(
			'meta' => array(
				'langDisable' => 1
			),
		);
		$compactExtensionToggleOn = 0 < $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'];
		$compactConfigurationToggleOn = 0 < $configuration['compact'];
		if (($compactExtensionToggleOn || $compactConfigurationToggleOn) && count($sheets) < 2) {
			$dataStructArray['ROOT'] = array(
				'type' => 'array',
				'el' => array(),
			);
			$sheet = array_pop($sheets);
			foreach ($sheet['fields'] as $field) {
				unset($field['sheet']);
				$name = $field['name'];
				$sheetStructArray['ROOT']['el'][$name] =  $field->getStructure();
			}
		} else {
			$dataStructArray['sheets'] = array();
			foreach ($sheets as $sheet) {
				$name = $sheet['name'];
				/** @var Tx_Flux_Provider_Structure_SheetStructureProvider $sheetStructureProvider */
				$sheetStructureProvider = $this->objectManager->get('Tx_Flux_Provider_Structure_SheetStructureProvider');
				$dataStructArray['sheets'][$name] = $sheetStructureProvider->render($sheet);
			}
		}
		return $dataStructArray;
	}

}
