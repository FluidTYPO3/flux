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
 * Sheet structure provider
 *
 * @package Flux
 * @subpackage Provider/Structure
 */
class Tx_Flux_Provider_Structure_SheetStructureProvider extends Tx_Flux_Provider_Structure_AbstractStructureProvider implements Tx_Flux_Provider_StructureProviderInterface {

	/**
	 * @param array $configuration
	 * @return array
	 */
	public function render($configuration) {
		return $this->renderSheet($configuration);
	}

	/**
	 * Renders a sheet of TCEforms field arrays
	 *
	 * @param array $sheet
	 * @return array
	 */
	protected function renderSheet($sheet) {
		$sheetStructArray = array(
			'ROOT' => array(
				'TCEforms' => array(
					'sheetTitle' => $sheet['label']
				),
				'type' => 'array',
				'el' => array()
			)
		);
		foreach ($sheet['fields'] as $field) {
			/** @var $field Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper */
			$name = $field['name'];
			$sheetStructArray['ROOT']['el'][$name] =  $field->getStructure();
		}
		return $sheetStructArray;
	}

}