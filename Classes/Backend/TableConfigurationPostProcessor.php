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
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		/** @var Tx_Flux_Service_FluxService $fluxService */
		$fluxService = $objectManager->get('Tx_Flux_Service_FluxService');
		$fluxService->initializeObject();
		/** @var Tx_Extbase_Persistence_Mapper_DataMapFactory $dataMapFactory */
		$dataMapFactory = $objectManager->get('Tx_Extbase_Persistence_Mapper_DataMapFactory');
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
		$extensionKey = t3lib_div::camelCaseToLowerCaseUnderscored($extensionNameWithoutVendor);
		$tableConfiguration = self::$tableTemplate;
		$fields = array();
		$labelFields = $form->getOption('labels');
		$enableColumns = array();
		foreach ($form->getFields() as $field) {
			$name = $field->getName();
			$fields[$name] = array(
				'label' => $field->getLabel(),
				'config' => $field->buildConfiguration(),
				'exclude' => $field->getExclude()
			);
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
		$tableConfiguration['iconfile'] = t3lib_extMgm::extRelPath($extensionKey) . $form->getIcon();
		$showRecordsFieldList = implode(',', array_keys($fields));
		$GLOBALS['TCA'][$table] = array(
			'title' => $form->getLabel(),
			'ctrl' => $tableConfiguration,
			'interface' => array(
				'showRecordFieldList' => $showRecordsFieldList
			),
			'columns' => $fields,
			'enableColumns' => $enableColumns,
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
		foreach ($values as $propertyName => $settings) {
			$field = $form->createField($settings['type'], $propertyName);
			foreach ($settings['config'] as $parameter => $value) {
				Tx_Extbase_Reflection_ObjectAccess::setProperty($field, $parameter, $value);
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

}
