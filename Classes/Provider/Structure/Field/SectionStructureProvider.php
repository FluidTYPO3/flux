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
 * Section pseudo-field FlexForm XML structure provider
 *
 * @package Flux
 * @subpackage Provider/Structure
 */
class Tx_Flux_Provider_Structure_Field_SectionStructureProvider extends Tx_Flux_Provider_Structure_AbstractStructureProvider implements Tx_Flux_Provider_StructureProviderInterface {

	/**
	 * @param array $configuration
	 * @return array
	 */
	public function render($configuration) {
		$fieldStructureArray = array(
			'tx_templavoila' => array(
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
				'tx_templavoila' => array(
					'title' => $configuration['labels'][$objectName]
				),
				'el' => array(),
			);
			foreach ($objectFields as $field) {
				$name = $field['name'];
				$fieldStructureArray['el'][$objectName]['el'][$name] = $this->resolveStructureProviderAndRenderField($field);
			}
		}
		return $fieldStructureArray;
	}

}
