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
use FluidTYPO3\Flux\Utility\AnnotationUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
		if (TYPO3_REQUESTTYPE_INSTALL === (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
			return;
		}
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$objectManager->get('FluidTYPO3\Flux\Provider\ProviderResolver')->loadTypoScriptConfigurationProviderInstances();
		$forms = Core::getRegisteredFormsForTables();
		$models = Core::getRegisteredFormsForModelObjectClasses();
		foreach ($forms as $fullTableName => $form) {
			$this->processFormForTable($fullTableName, $form);
		}
		foreach ($models as $modelClassName => $form) {
			$fullTableName = $this->resolveTableName($modelClassName);
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
		$labelFields = $form->getOption(Form::OPTION_TCA_LABELS);
		$enableColumns = array();
		foreach ($form->getFields() as $field) {
			$name = $field->getName();
			// note: extracts the TCEforms sub-array from the configuration, as required in TCA.
			$fields[$name] = array_pop($field->build());
		}
		if (TRUE === $form->getOption(Form::OPTION_TCA_HIDE)) {
			$enableColumns['disabled'] = 'hidden';
		}
		if (TRUE === $form->getOption(Form::OPTION_TCA_START)) {
			$enableColumns['start'] = 'starttime';
		}
		if (TRUE === $form->getOption(Form::OPTION_TCA_END)) {
			$enableColumns['end'] = 'endtime';
		}
		if (TRUE === $form->getOption(Form::OPTION_TCA_FEGROUP)) {
			$enableColumns['fe_group'] = 'fe_group';
		}
		$tableConfiguration['iconfile'] = ExtensionManagementUtility::extRelPath($extensionKey) . $form->getOption(Form::OPTION_ICON);
		$tableConfiguration['enablecolumns'] = $enableColumns;
		$tableConfiguration['title'] = $form->getLabel();
		$tableConfiguration['languageField'] = 'sys_language_uid';
		$showRecordsFieldList = $this->buildShowItemList($form);
		$GLOBALS['TCA'][$table] = array(
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
		if (TRUE === $form->getOption(Form::OPTION_TCA_DELETE)) {
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
		$labelFields = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Label', FALSE);
		$iconAnnotation = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Icon');
		$extensionName = $this->getExtensionNameFromModelClassName($class);
		$values = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Form\Field', FALSE);
		$sheets = AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Form\Sheet', FALSE);
		$labels = TRUE === is_array($labelFields) ? array_keys($labelFields) : array(key($values));
		foreach ($labels as $index => $labelField) {
			$labels[$index] = GeneralUtility::camelCaseToLowerCaseUnderscored($labelField);
		}
		$icon = TRUE === isset($iconAnnotation['config']['path']) ? $iconAnnotation['config']['path'] : 'ext_icon.png';
		$hasVisibilityToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\Hide');
		$hasDeleteToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\Delete');
		$hasStartTimeToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\StartTime');
		$hasEndTimeToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\EndTime');
		$hasFrontendGroupToggle = (boolean) AnnotationUtility::getAnnotationValueFromClass($class, 'Flux\Control\FrontendUserGroup');
		$form = Form::create();
		$form->setName($table);
		$form->setExtensionName($extensionName);
		$form->setOption('labels', $labels);
		$form->setOption('delete', $hasDeleteToggle);
		$form->setOption('hide', $hasVisibilityToggle);
		$form->setOption('start', $hasStartTimeToggle);
		$form->setOption('end', $hasEndTimeToggle);
		$form->setOption(Form::OPTION_ICON, $icon);
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
			$sheet = $form->createContainer('Sheet', $sheetName);
			foreach ($propertyNames as $propertyName) {
				$settings = $values[$propertyName];
				$propertyName = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
				if (TRUE === isset($settings['type'])) {
					$fieldType = ucfirst($settings['type']);
					$field = $sheet->createField($fieldType, $propertyName);
					foreach ($settings['config'] as $settingName => $settingValue) {
						ObjectAccess::setProperty($field, $settingName, $settingValue);
					}
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

	/**
	 * Resolve the table name for the given class name
	 *
	 * @param string $className
	 * @return string The table name
	 */
	protected function resolveTableName($className) {
		$className = ltrim($className, '\\');
		if (strpos($className, '\\') !== FALSE) {
			$classNameParts = explode('\\', $className, 6);
			// Skip vendor and product name for core classes
			if (strpos($className, 'TYPO3\\CMS\\') === 0) {
				$classPartsToSkip = 2;
			} else {
				$classPartsToSkip = 1;
			}
			$tableName = 'tx_' . strtolower(implode('_', array_slice($classNameParts, $classPartsToSkip)));
		} else {
			$tableName = strtolower($className);
		}
		return $tableName;
	}

}
