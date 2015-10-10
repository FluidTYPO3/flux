<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Helper\Resolver;
use FluidTYPO3\Flux\Utility\AnnotationUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Table Configuration (TCA) post processor
 *
 * Simply loads the Flux service and lets methods
 * on this Service load necessary configuration.
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
		if (TYPO3_REQUESTTYPE_INSTALL !== (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
			$this->generateTableConfigurationForProviderForms();
		}
	}

	/**
	 * @return void
	 */
	protected function generateTableConfigurationForProviderForms() {
		$resolver = new Resolver();
		$forms = Core::getRegisteredFormsForTables();
		$packages = $this->getInstalledFluxPackages();
		$models = $resolver->resolveDomainFormClassInstancesFromPackages($packages);
		foreach ($forms as $fullTableName => $form) {
			$this->processFormForTable($fullTableName, $form);
		}
		foreach ($models as $modelClassName => $form) {
			$fullTableName = $resolver->resolveDatabaseTableName($modelClassName);
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
	 * @return array
	 */
	protected function getInstalledFluxPackages() {
		return array_keys(Core::getRegisteredPackagesForAutoForms());
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
					$fieldType = implode('/', array_map('ucfirst', explode('.', $settings['type'])));
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

}
