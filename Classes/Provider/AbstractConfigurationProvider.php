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
	 * @var string
	 */
	protected $fieldName = NULL;

	/**
	 * @var string
	 */
	protected $tableName = NULL;

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
	protected $configurationSectionName = NULL;

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
		isset($row);
		return $this->fieldName;
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
		unset($row, $id, $reference);
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
				// skip processing; posting what is most likely an empty string
			return;
		}
		$fieldName = $this->getFieldName($row);
		$paths = $this->getTemplatePaths($row);
		$values = $this->flexFormService->convertFlexFormContentToArray($row[$fieldName ? $fieldName : 'pi_flexform']);
		$values = array_merge((array) $this->getTemplateVariables($row), $values);
		$section = $this->getConfigurationSectionName($row);
		if (strpos($section, 'variable:') !== FALSE) {
			$section = $values[array_pop(explode(':', $section))];
		}
		$templatePathAndFilename = $this->getTemplatePathAndFilename($row);
		if (is_file($templatePathAndFilename) === TRUE) {
			$this->flexFormService->convertFlexFormContentToDataStructure($templatePathAndFilename, $values, $paths, $dataStructure, $section);
		}
		unset($conf);
	}

}
