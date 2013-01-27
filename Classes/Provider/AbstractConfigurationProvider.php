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
	 * @var array|NULL
	 */
	protected $templateVariables = NULL;

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
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexFormService;

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
	 * @param Tx_Flux_Service_FlexForm $flexFormService
	 * @return void
	 */
	public function injectFlexFormService(Tx_Flux_Service_FlexForm $flexFormService) {
		$this->flexFormService = $flexFormService;
	}

	/**
	 * @param array $row The record row which triggered processing
	 * @return string|NULL
	 */
	public function getFieldName(array $row) {
		if (Tx_Flux_Utility_Version::assertHasFixedFlexFormFieldNamePassing() === TRUE) {
				// NOTE: only allow returning the real fieldname for Providers which do NOT
				// override the getFieldName method if the version of TYPO3 is recent enough
				// for the FlexForm Hook to include the actual field name when calling the
				// hook that in turn calls this method when resolving Providers. In other words:
				// becuase of a bug in older TYPO3 versions the field name must be NULL if
				// TYPO3 version is too old or no FlexForm is rendered.
			return $this->fieldName;
		}
		return NULL;
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
		return $this->templatePathAndFilename;
	}

	/**
	 * @param array $row
	 * @return array|NULL
	 */
	public function getTemplateVariables(array $row) {
		unset($row);
		return $this->templateVariables;
	}

	/**
	 * @param array $row
	 * @return array|NULL
	 */
	public function getTemplatePaths(array $row) {
		unset($row);
		return $this->templatePaths;
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
		$parentFieldName = $this->getParentFieldName($row);
		$fieldName = $this->getFieldName($row);
		if (NULL === $fieldName || NULL === $parentFieldName) {
			return;
		}
		if (FALSE === isset($row[$fieldName]['data'])) {
			return;
		}
		$data = $row[$fieldName]['data'];
		if (FALSE === is_array($data)) {
			return;
		}
		if (FALSE === isset($row['uid']) && intval($id) > 0) {
			$row['uid'] = $id;
		}
		foreach ($data as $sheetName => $sheetFields) {
			foreach ($sheetFields['lDEF'] as $sheetFieldName => $fieldDefinition) {
				$inheritedValue = $this->getInheritedPropertyValueByDottedPath($row, $sheetFieldName);
				if ($inheritedValue == $fieldDefinition['vDEF']) {
					unset($data[$sheetName]['lDEF'][$sheetFieldName]);
				}
			}
			if (0 === count($data[$sheetName]['lDEF'])) {
				$data[$sheetName]['lDEF'] = array('flux.placeholder' => array('vDEF' => 0));
			}
		}
		$row[$fieldName]['data'] = $data;
		$_POST['data'][$this->tableName][$id] = $row;
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
		unset($operation, $id, $row, $reference);
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
		if (is_array($dataStructure) === FALSE) {
			$dataStructure = array();
		}
		$fieldName = $this->getFieldName($row);
		$paths = $this->getTemplatePaths($row);
		$values = $this->getFlexFormValues($row);
		$values = array_merge((array) $this->getTemplateVariables($row), $values);
		$section = $this->getConfigurationSectionName($row);
		if (strpos($section, 'variable:') !== FALSE) {
			$section = $values[array_pop(explode(':', $section))];
		}
		$templatePathAndFilename = $this->getTemplatePathAndFilename($row);
		$this->flexFormService->convertFlexFormContentToDataStructure($templatePathAndFilename, $values, $paths, $dataStructure, $section);
		unset($conf);
	}

	/**
	 * Perform various cleanup operations upon clearing cache
	 *
	 * @return void
	 */
	public function clearCacheCommand() {
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
		if (NULL === $fieldName) {
			return array();
		}
		$immediateConfiguration = $this->flexFormService->convertFlexFormContentToArray($row[$fieldName]);
		$tree = $this->getInheritanceTree($row);
		if (0 === count($tree)) {
			return $immediateConfiguration;
		}
		$inheritedConfiguration = $this->getMergedConfiguration($tree);
		if (0 === count($immediateConfiguration)) {
			return $inheritedConfiguration;
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
			$fieldName = $this->getFieldName($branch);
			if (NULL === $fieldName) {
				return $data;
			}
			$currentData = $this->flexFormService->convertFlexFormContentToArray($branch[$fieldName]);
			$data = t3lib_div::array_merge_recursive_overrule($data, $currentData, FALSE, FALSE, TRUE);
		}
		self::$cacheMergedConfigurations[$key] = $data;
		return $data;
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
		if (NULL !== $parentFIeldName && FALSE === isset($row[$parentFieldName])) {
			$row = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows($parentFieldName, $tableName, "uid = '" . $row['uid'] . "'"));
		}
		return $row[$parentFieldName];
	}

}
