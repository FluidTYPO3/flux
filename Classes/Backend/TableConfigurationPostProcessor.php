<?php
namespace FluidTYPO3\Flux\Backend;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\AbstractFormField;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\AnnotationUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Table Configuration (TCA) post processor
 *
 * Simply loads the Flux service and lets methods
 * on this Service load necessary configuration.
 *
 * @package Flux
 * @subpackage Backend
 */
class TableConfigurationPostProcessor implements TableConfigurationPostProcessingHookInterface {

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
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var FluxService $fluxService */
		$fluxService = $objectManager->get('FluidTYPO3\Flux\Service\FluxService');
		$fluxService->initializeObject();
		/** @var DataMapFactory $dataMapFactory */
		$dataMapFactory = $objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory');
		$forms = Core::getRegisteredFormsForTables();
		$models = Core::getRegisteredFormsForModelObjectClasses();
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
	 * @param Form $form
	 */
	protected function processFormForTable($table, Form $form) {
		$extensionName = $form->getExtensionName();
		$extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);
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
		$tableConfiguration['iconfile'] = ExtensionManagementUtility::extRelPath($extensionKey) . $form->getIcon();
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
	 * @return Form
	 */
	public function generateFormInstanceFromClassName($class, $table) {
		$labelFields = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Label', NULL);
		$extensionName = $this->getExtensionNameFromModelClassName($class);
		$values = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Form\Field', NULL);
		$sheets = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Form\Sheet', NULL);
		$labels = TRUE === is_array($labelFields) ? array_keys($labelFields) : array(key($values));
		$hasVisibilityToggle = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\Hide');
		$hasDeleteToggle = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\Delete');
		$hasStartTimeToggle = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\StartTime');
		$hasEndTimeToggle = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\EndTime');
		$hasFrontendGroupToggle = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\FrontendUserGroup');
		$form = Form::create();
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
				if (TRUE === isset($settings['type'])) {
					$field = AbstractFormField::create($settings);
					$sheets[$sheetName]->add($field);
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
				$extensionName = reset($candidate);
			} else {
				$extensionName = implode('.', $candidate);
			}
		}
		return $extensionName;
	}

	/**
	 * @param Form $form
	 * @return string
	 */
	protected function buildShowItemList(Form $form) {
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
