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
class Tx_Flux_Backend_TableConfigurationPostProcessor implements \TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface {

	/**
	 * @var array
	 */
	private static $tableTemplate = array(
		'title' => NULL,
		'label' => NULL,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
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
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		/** @var Tx_Flux_Service_FluxService $fluxService */
		$fluxService = $objectManager->get('Tx_Flux_Service_FluxService');
		$fluxService->initializeObject();
		/** @var Tx_Extbase_Persistence_Mapper_DataMapFactory $dataMapFactory */
		$dataMapFactory = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Mapper\\DataMapFactory');
		$forms = Tx_Flux_Core::getRegisteredFormsForTables();
		$models = Tx_Flux_Core::getRegisteredFormsForModelObjectClasses();
		foreach ($forms as $fullTableName => $form) {
			$this->processFormForTable($fullTableName, $form);
		}
		foreach ($models as $modelClassName => $form) {
			$map = $dataMapFactory->buildDataMap($modelClassName);
			$fullTableName = $map->getTableName();
			if (NULL === $form) {
				$form = $this->generateFormInstanceFromClassName($modelClassName, $fullTableName);
			}
			if (NULL === $form->getName()) {
				$form->setName($fullTableName);
			}
			$this->processFormForTable($fullTableName, $form);
		}
	}

	/**
	 * @param string $table
	 * @param Tx_Flux_Form $form
	 */
	protected function processFormForTable($table, Tx_Flux_Form $form) {
		$extensionName = $form->getExtensionName();
		$extensionNameWithoutVendor = FALSE === strpos($extensionName, '.') ? $extensionName : array_pop(explode('.', $extensionName));
		$extensionKey = \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($extensionNameWithoutVendor);
		$tableConfiguration = self::$tableTemplate;
		$fields = array();
		$labelFields = $form->getOption('labels');
		$enableColumns = array();
		foreach ($form->getFields() as $field) {
			$name = $field->getName();
			// note: extracts the TCEforms sub-array from the configuration, as required in TCA.
			$fields[$name] = array_pop($field->build());
		}
		if (TRUE === $form->getOption('hide')) {
			$enableColumns['disabled'] = 'hidden';
		}
		if (TRUE === $form->getOption('start')) {
			$enableColumns['start'] = 'starttime';
		}
		if (TRUE === $form->getOption('end')) {
			$enableColumns['end'] = 'endtime';
		}
		if (TRUE === $form->getOption('frontendUserGroup')) {
			$enableColumns['fe_group'] = 'fe_group';
		}
		$tableConfiguration['iconfile'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($extensionKey) . $form->getIcon();
		$tableConfiguration['enablecolumns'] = $enableColumns;
		$showRecordsFieldList = $this->buildShowItemList($form);
		$GLOBALS['TCA'][$table] = array(
			'title' => $form->getLabel(),
			'ctrl' => $tableConfiguration,
			'interface' => array(
				'showRecordFieldList' => implode(',', array_keys($fields))
			),
			'columns' => $fields,
			'types' => array(
				0 => array(
					'showitem' => $showRecordsFieldList
				)
			)
		);
		if (TRUE === $form->getOption('delete')) {
			$GLOBALS['TCA'][$table]['ctrl']['delete'] = 'deleted';
		}
		if (NULL === $labelFields) {
			reset($fields);
			$GLOBALS['TCA'][$table]['ctrl']['label'] = key($fields);
		} else {
			$GLOBALS['TCA'][$table]['ctrl']['label'] = array_shift($labelFields);
			$GLOBALS['TCA'][$table]['ctrl']['label_alt'] = implode(',', $labelFields);
		}
	}

	/**
	 * @param string $class
	 * @param string $table
	 * @return Tx_Flux_Form
	 */
	protected function generateFormInstanceFromClassName($class, $table) {
		$labelFields = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Label', NULL);
		$extensionName = $this->getExtensionNameFromModelClassName($class);
		$values = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Form\\Field', NULL);
		$sheets = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Form\\Sheet', NULL);
		$labels = TRUE === is_array($labelFields) ? array_keys($labelFields) : array(key($values));
		$hasVisibilityToggle = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Control\\Hide');
		$hasDeleteToggle = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Control\\Delete');
		$hasStartTimeToggle = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Control\\StartTime');
		$hasEndTimeToggle = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Control\\EndTime');
		$hasFrontendGroupToggle = Tx_Flux_Utility_Annotation::getAnnotationValueFromClass($class, 'Flux\\Control\\FrontendUserGroup');
		$form = Tx_Flux_Form::create();
		$form->setName($table);
		$form->setExtensionName($extensionName);
		$form->setOption('labels', $labels);
		$form->setOption('delete', $hasDeleteToggle);
		$form->setOption('hide', $hasVisibilityToggle);
		$form->setOption('start', $hasStartTimeToggle);
		$form->setOption('end', $hasEndTimeToggle);
		$form->setOption('frontendUserGroup', $hasFrontendGroupToggle);
		$fields = array();
		foreach ($sheets as $propertyName => $sheetAnnotation) {
			$sheetName = $sheetAnnotation['type'];
			if (FALSE === isset($fields[$sheetName])) {
				$fields[$sheetName] = array();
			}
			array_push($fields[$sheetName], $propertyName);
		}
		foreach ($fields as $sheetName => $propertyNames) {
			$form->remove($sheetName);
			$sheets[$sheetName] = $form->createContainer('Sheet', $sheetName);
			foreach ($propertyNames as $propertyName) {
				$settings = $values[$propertyName];
				$field = $sheets[$sheetName]->createField($settings['type'], $propertyName);
				foreach ($settings['config'] as $parameter => $value) {
					\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($field, $parameter, $value);
				}
			}
		}
		return $form;
	}

	/**
	 * @param string $class
	 * @return string
	 */
	protected function getExtensionNameFromModelClassName($class) {
		if (FALSE !== strpos($class, '_')) {
			$parts = explode('_Domain_Model_', $class);
			$extensionName = substr($parts[0], 3);
		} else {
			$parts = explode('\\', $class);
			$candidate = array_slice($parts, 0, -3);
			if (1 === count($candidate)) {
				$extensionName = array_pop($candidate);
			} else {
				$extensionName = implode('.', $candidate);
			}
		}
		return $extensionName;
	}

	/**
	 * @param Tx_Flux_Form $form
	 * @return string
	 */
	protected function buildShowItemList(Tx_Flux_Form $form) {
		$parts = array();
		foreach ($form->getSheets(FALSE) as $sheet) {
			array_push($parts, '--div--;' . $sheet->getLabel());
			foreach ($sheet->getFields() as $field) {
				array_push($parts, $field->getName());
			}
		}
		return implode(', ', $parts);
	}

}
