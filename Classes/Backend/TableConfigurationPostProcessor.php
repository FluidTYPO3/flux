<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 ***************************************************************/

/**
 * Table Configuration (TCA) post processor
 *
 * Simply loads the Flux service and lets methods
 * on this Service load necessary configuration.
 *
 * @package Flux
 * @subpackage Backend
 */
class Tx_Flux_Backend_TableConfigurationPostProcessor implements t3lib_extTables_PostProcessingHook {

	/**
	 * @var array
	 */
	private static $tableTemplate = array(
		'title'     => NULL,
		'label'     => NULL,
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'enablecolumns' => array(),
		'iconfile' => '',
		'hideTable' => FALSE,
	);

	/**
	 * @return void
	 */
	public function processData() {
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		/** @var Tx_Flux_Service_FluxService $fluxService */
		$fluxService = $objectManager->get('Tx_Flux_Service_FluxService');
		$fluxService->initializeObject();
		$forms = Tx_Flux_Core::getRegisteredFormsForTables();
		foreach ($forms as $fullTableName => $form) {
			$extensionName = $form->getExtensionName();
			$extensionKey = t3lib_div::camelCaseToLowerCaseUnderscored($extensionName);
			$tableConfiguration = self::$tableTemplate;
			$tableConfiguration['title'] = $form->getLabel();
			$fields = array();
			foreach ($form->getFields() as $field) {
				$name = $field->getName();
				$fields[$name] = array(
					'label' => $field->getLabel(),
					'config' => $field->buildConfiguration(),
					'exclude' => $field->getExclude()
				);
			}
			reset($fields);
			$tableConfiguration['label'] = key($fields);
			$tableConfiguration['iconfile'] = t3lib_extMgm::extRelPath($extensionKey) . $form->getIcon();
			$showRecordsFieldList = implode(',', array_keys($fields));
			$GLOBALS['TCA'][$fullTableName] = array(
				'ctrl' => $tableConfiguration,
				'interface' => array(
					'showRecordFieldList' => $showRecordsFieldList
				),
				'columns' => $fields,
				'types' => array(
					0 => array(
						'showitem' => $showRecordsFieldList
					)
				)
			);
		}
	}



}
