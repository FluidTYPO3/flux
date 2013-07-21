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
 * @package Flux
 * @subpackage Provider
 */
class Tx_Flux_Provider_AbstractConfigurationProvider implements Tx_Flux_Provider_ConfigurationProviderInterface {

	/**
	 * @var array
	 */
	private static $cacheTree = array();

	/**
	 * @var array
	 */
	private static $cacheMergedConfigurations = array();

	/**
	 * @var string
	 */
	protected $fieldName = NULL;

	/**
	 * @var string
	 */
	protected $tableName = NULL;

	/**
	 * @var string
	 */
	protected $parentFieldName = NULL;

	/**
	 * @var array|NULL
	 */
	protected $row = NULL;

	/**
	 * @var array
	 */
	protected $dataStructArray;

	/**
	 * @var string|NULL
	 */
	protected $templatePathAndFilename = NULL;

	/**
	 * @var array
	 */
	protected $templateVariables = array();

	/**
	 * @var array|NULL
	 */
	protected $templatePaths = NULL;

	/**
	 * @var string|NULL
	 */
	protected $configurationSectionName = 'Configuration';

	/**
	 * @var string|NULL
	 */
	protected $extensionKey = NULL;

	/**
	 * @var integer
	 */
	protected $priority = 50;

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param Tx_Flux_Service_FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Flux_Service_FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param array $row
	 * @return Tx_Flux_Form
	 */
	public function getForm(array $row) {
		$templatePathAndFilename = $this->getTemplatePathAndFilename($row);
		$section = $this->getConfigurationSectionName($row);
		$formName = 'form';
		$paths = $this->getTemplatePaths($row);
		$extensionKey = $this->getExtensionKey($row);
		$extensionName = t3lib_div::underscoredToUpperCamelCase($extensionKey);
		$variables = $this->getFlexFormValues($row);
		$form = $this->configurationService->getFormFromTemplateFile($templatePathAndFilename, $section, $formName, $paths, $extensionName, $variables);
		return $form;
	}

	/**
	 * @param array $row
	 * @return Tx_Flux_Form_Container_Grid
	 */
	public function getGrid(array $row) {
		$templatePathAndFilename = $this->getTemplatePathAndFilename($row);
		$section = $this->getConfigurationSectionName($row);
		$gridName = 'grid';
		$paths = $this->getTemplatePaths($row);
		$extensionKey = $this->getExtensionKey($row);
		$extensionName = t3lib_div::underscoredToUpperCamelCase($extensionKey);
		$variables = $this->getFlexFormValues($row);
		$grid = $this->configurationService->getGridFromTemplateFile($templatePathAndFilename, $section, $gridName, $paths, $extensionName, $variables);
		return $grid;
	}

	/**
	 * @param array $row The record row which triggered processing
	 * @return string|NULL
	 */
	public function getFieldName(array $row) {
		return $this->fieldName;
	}

	/**
	 * @param array $row
	 * @return string
	 */
	public function getParentFieldName(array $row) {
		unset($row);
		return $this->parentFieldName;
	}

	/**
	 * @param array $row The record row which triggered processing
	 * @return string|NULL
	 */
	public function getTableName(array $row) {
		unset($row);
		return $this->tableName;
	}

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getTemplatePathAndFilename(array $row) {
		unset($row);
		if (0 === strpos($this->templatePathAndFilename, 'EXT:') || 0 !== strpos($this->templatePathAndFilename, '/')) {
			return t3lib_div::getFileAbsFileName($this->templatePathAndFilename);
		}
		return $this->templatePathAndFilename;
	}

	/**
	 * @param array $row
	 * @return array
	 */
	public function getTemplateVariables(array $row) {
		$file = $this->getTemplatePathAndFilename($row);
		if (NULL === $this->fieldName || FALSE === file_exists($file)) {
			return $this->templateVariables;
		} else {
			$values = $this->configurationService->convertFlexFormContentToArray($row[$this->fieldName]);
			$values['row'] = $row;
			$values['grid'] = $this->getGrid($row);
			$values['form'] = $this->getForm($row);
		}
		return $values;
	}

	/**
	 * @param array $row
	 * @return array|NULL
	 */
	public function getTemplatePaths(array $row) {
		unset($row);
		if (TRUE === is_array($this->templatePaths)) {
			return Tx_Flux_Utility_Path::translatePath($this->templatePaths);
		}
		return array();
	}

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getConfigurationSectionName(array $row) {
		unset($row);
		return $this->configurationSectionName;
	}

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getExtensionKey(array $row) {
		unset($row);
		return $this->extensionKey;
	}

	/**
	 * @param array $row
	 * @return integer
	 */
	public function getPriority(array $row) {
		unset($row);
		return $this->priority;
	}

	/**
	 * Pre-process record data for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @param array $row The record data, by reference. Changing fields' values changes the record's values before display
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param t3lib_TCEmain $reference A reference to the t3lib_TCEmain object that is currently displaying the record
	 * @return void
	 */
	public function preProcessRecord(array &$row, $id, t3lib_TCEmain $reference) {
	}

	/**
	 * Post-process record data for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $operation TYPO3 operation identifier, i.e. "update", "new" etc.
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param array $row the record data, by reference. Changing fields' values changes the record's values just before saving
	 * @param t3lib_TCEmain $reference A reference to the t3lib_TCEmain object that is currently saving the record
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, t3lib_TCEmain $reference) {
		if ('update' === $operation) {
			$fieldName = $this->getFieldName($reference->datamap[$this->tableName][$id]);
			if (NULL === $fieldName) {
				return;
			}
			if (FALSE === isset($row[$fieldName])) {
				return;
			}
			$data = $reference->datamap[$this->tableName][$id][$fieldName]['data'];
			if (FALSE === is_array($data)) {
				return;
			}
			$removals = array();
			foreach ($data as $sheetName => $sheetFields) {
				foreach ($sheetFields['lDEF'] as $sheetFieldName => $fieldDefinition) {
					if ('_clear' === substr($sheetFieldName, -6)) {
						array_push($removals, $sheetFieldName);
					} else {
						$clearFieldName = $sheetFieldName . '_clear';
						$inheritedValue = $this->getInheritedPropertyValueByDottedPath($row, $sheetFieldName);
						if (TRUE === isset($data[$sheetName]['lDEF'][$clearFieldName]['vDEF']) && 0 < $data[$sheetName]['lDEF'][$clearFieldName]['vDEF']) {
							array_push($removals, $sheetFieldName);
						} elseif (NULL !== $inheritedValue && $inheritedValue == $fieldDefinition['vDEF']) {
							array_push($removals, $sheetFieldName);
						}
					}
				}
			}
			$dom = new DOMDocument();
			$dom->loadXML($row[$fieldName]);
			$dom->preserveWhiteSpace = FALSE;
			$dom->formatOutput = TRUE;
			foreach ($dom->getElementsByTagName('field') as $fieldNode) {
				if (TRUE === in_array($fieldNode->getAttribute('index'), $removals)) {
					$fieldNode->parentNode->removeChild($fieldNode);
				}
			}
			$row[$fieldName] = $dom->saveXML();
		}
	}

	/**
	 * Post-process database operation for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $status TYPO3 operation identifier, i.e. "new" etc.
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param array $row The record's data, by reference. Changing fields' values changes the record's values just before saving after operation
	 * @param t3lib_TCEmain $reference A reference to the t3lib_TCEmain object that is currently performing the database operation
	 * @return void
	 */
	public function postProcessDatabaseOperation($status, $id, &$row, t3lib_TCEmain $reference) {
		unset($status, $id, $row, $reference);
	}

	/**
	 * Pre-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function preProcessCommand($command, $id, array &$row, &$relativeTo, t3lib_TCEmain $reference) {
		unset($command, $id, $row, $relativeTo, $reference);
	}

	/**
	 * Post-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, t3lib_TCEmain $reference) {
		unset($command, $id, $row, $relativeTo, $reference);
	}

	/**
	 * Post-process the TCEforms DataStructure for a record associated
	 * with this ConfigurationProvider
	 *
	 * @param array $row
	 * @param mixed $dataStructure
	 * @param array $conf
	 * @return void
	 */
	public function postProcessDataStructure(array &$row, &$dataStructure, array $conf) {
		$form = $this->getForm($row);
		$dataStructure = $form->build();
	}

	/**
	 * Perform various cleanup operations upon clearing cache
	 *
	 * @param array $command
	 * @return void
	 */
	public function clearCacheCommand($command = array()) {
		return;
	}

	/**
	 * Converts the contents of the provided row's Flux-enabled field,
	 * at the same time running through the inheritance tree generated
	 * by getInheritanceTree() in order to apply inherited values.
	 *
	 * @param array $row
	 * @return array
	 */
	public function getFlexFormValues(array $row) {
		$fieldName = $this->getFieldName($row);
		$immediateConfiguration = $this->configurationService->convertFlexFormContentToArray($row[$fieldName]);
		$tree = $this->getInheritanceTree($row);
		if (0 === count($tree)) {
			return (array) $immediateConfiguration;
		}
		$inheritedConfiguration = $this->getMergedConfiguration($tree);
		if (0 === count($immediateConfiguration)) {
			return (array) $inheritedConfiguration;
		}
		$merged = t3lib_div::array_merge_recursive_overrule($inheritedConfiguration, $immediateConfiguration);
		return $merged;
	}

	/**
	 * Gets an inheritance tree (ordered parent -> ... -> this record)
	 * of record arrays containing raw values.
	 *
	 * @param array $row
	 * @return array
	 */
	public function getInheritanceTree(array $row) {
		$recordUid = $row['uid'];
		if (TRUE === isset(self::$cacheTree[$recordUid])) {
			return self::$cacheTree[$recordUid];
		}
		$records = array();
		if (NULL === $this->getFieldName($row)) {
			return $records;
		}
		$parentFieldName = $this->getParentFieldName($row);
		$record = $row;
		if (FALSE === isset($record[$parentFieldName])) {
			$record[$parentFieldName] = $this->getParentFieldValue($record);
		}
		while ($record[$parentFieldName] > 0) {
			$tableName = $this->getTableName($row);
			$resource = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $tableName, "uid = '" . $record[$parentFieldName] . "'");
			$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource);
			$parentFieldName = $this->getParentFieldName($record);
			array_push($records, $record);
		}
		$records = array_reverse($records);
		self::$cacheTree[$recordUid] = $records;
		return $records;
	}

	/**
	 * @param array $row
	 * @param string $propertyPath
	 * @return mixed
	 */
	protected function getInheritedPropertyValueByDottedPath(array $row, $propertyPath) {
		$tree = $this->getInheritanceTree($row);
		$inheritedConfiguration = $this->getMergedConfiguration($tree);
		if (FALSE === strpos($propertyPath, '.')) {
			if (TRUE === isset($inheritedConfiguration[$propertyPath])) {
				return Tx_Extbase_Reflection_ObjectAccess::getProperty($inheritedConfiguration, $propertyPath);
			}
			return NULL;
		}
		return Tx_Extbase_Reflection_ObjectAccess::getPropertyPath($inheritedConfiguration, $propertyPath);
	}

	/**
	 * @param array $tree
	 * @return array
	 */
	protected function getMergedConfiguration(array $tree) {
		$key = md5(json_encode($tree));
		if (TRUE === isset(self::$cacheMergedConfigurations[$key])) {
			return self::$cacheMergedConfigurations[$key];
		}
		$data = array();
		foreach ($tree as $branch) {
			$fields = $this->getForm($branch)->getFields();
			$values = $this->getFlexFormValues($branch);
			foreach ($fields as $field) {
				$name = $field->getName();
				if (FALSE === $field instanceof Tx_Flux_Form_FieldInterface) {
					continue;
				}
				$stop = (TRUE === $field->getStopInheritance());
				$inherit = (TRUE === $field->getInheritEmpty());
				$empty = (TRUE === empty($values[$name]) && $values[$name] !== '0' && $values[$name] !== 0);
				if (TRUE === $stop) {
					unset($values[$name]);
				} elseif (FALSE === $inherit && TRUE === $empty) {
					unset($values[$name]);
				}
			}
			$data = $this->arrayMergeRecursive($data, $values);
		}
		self::$cacheMergedConfigurations[$key] = $data;
		return $data;
	}

	/**
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	protected function arrayMergeRecursive($array1, $array2) {
		foreach ($array2 as $key => $val) {
			if (is_array($array1[$key])) {
				if (is_array($array2[$key])) {
					$val = $this->arrayMergeRecursive($array1[$key], $array2[$key]);
				}
			}
			$array1[$key] = $val;
		}
		reset($array1);
		return $array1;
	}

	/**
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	protected function arrayDiffRecursive($array1, $array2) {
		foreach ($array1 as $key => $value) {
			if (TRUE === isset($array2[$key])) {
				if (TRUE === is_array($value)) {
					$diff = $this->arrayDiffRecursive($value, $array2[$key]);
					if (0 !== count($diff)) {
						$array1[$key] = $diff;
					}
				} elseif ($value != $array2[$key]) {
					$array1[$key] = $array2[$key];
				} else {
					unset($array1[$key]);
				}
			} else {
				unset($array1[$key]);
			}
		}
		return $array1;
	}

	/**
	 * @param array $row
	 * @return mixed
	 */
	protected function getParentFieldValue(array $row) {
		$parentFieldName = $this->getParentFieldName($row);
		$tableName = $this->getTableName($row);
		if (NULL !== $parentFieldName && FALSE === isset($row[$parentFieldName])) {
			$row = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows($parentFieldName, $tableName, "uid = '" . $row['uid'] . "'"));
		}
		return $row[$parentFieldName];
	}

	/**
	 * Stub: Override this when ConfigurationProvider is associated with a Controller
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerExtensionKeyFromRecord(array $row) {
		return NULL;
	}

	/**
	 * Stub: Override this when ConfigurationProvider is associated with a Controller
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerActionFromRecord(array $row) {
		return NULL;
	}

	/**
	 * Stub: implement this in Controllers which store the action in a record field.
	 *
	 * @param array $row
	 * @return string
	 */
	public function getControllerActionReferenceFromRecord(array $row) {
		return NULL;
	}

}
