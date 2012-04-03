<?php

/* * *************************************************************
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
 * ************************************************************* */

/**
 * @package Flux
 * @subpackage Provider
 */
class Tx_Flux_Provider_AbstractConfigurationProvider {

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
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexFormService;

	/**
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_Flux_Service_FlexForm $flexFormService
	 */
	public function injectFlexFormService(Tx_Flux_Service_FlexForm $flexFormService) {
		$this->flexFormService = $flexFormService;
	}

	/**
	 * @param array $row The record row which triggered processing
	 * @return string|NULL
	 */
	public function getFieldName(array $row) {
		return $this->fieldName;
	}

	/**
	 * @param array $row The record row which triggered processing
	 * @return string|NULL
	 */
	public function getTableName(array $row) {
		return $this->tableName;
	}

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getTemplatePathAndFilename(array $row) {
		return $this->templatePathAndFilename;
	}

	/**
	 * @param array $row
	 * @return array|NULL
	 */
	public function getTemplateVariables(array $row) {
		return $this->templateVariables;
	}

	/**
	 * @param array $row
	 * @return array|NULL
	 */
	public function getTemplatePaths(array $row) {
		return $this->templatePaths;
	}

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getConfigurationSectionName(array $row) {
		return $this->configurationSectionName;
	}

	/**
	 * @param array $row
	 * @return string|NULL
	 */
	public function getExtensionKey(array $row) {
		return $this->extensionKey;
	}

	/**
	 * @param array $row
	 * @return integer
	 */
	public function getPriority(array $row) {
		return $this->priority;
	}

}

?>