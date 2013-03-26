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
		$sheets = array();
		foreach ($configuration['fields'] as $field) {
			$groupKey = $field['sheet']['name'];
			$groupLabel = $field['sheet']['label'];
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
		if (($compactExtensionToggle || $compactConfigurationToggleOn) && count($sheets) < 2) {
			$dataStructArray['ROOT'] = array(
				'type' => 'array',
				'el' => array(),
			);
			$sheet = array_pop($sheets);
			foreach ($sheet['fields'] as $field) {
				unset($field['sheet']);
				$name = $field['name'];
				$structureProvider = $this->resolveFieldStructureProvider($field);
				$dataStructArray['ROOT']['el'][$name] =  $structureProvider->render($field);
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
