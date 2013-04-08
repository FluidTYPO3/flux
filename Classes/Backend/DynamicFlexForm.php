<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * Dynamic FlexForm insertion hook class
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @package Flux
 * @subpackage Backend
 */
class Tx_Flux_Backend_DynamicFlexForm {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->configurationManager = $this->objectManager->get('Tx_Extbase_Configuration_ConfigurationManagerInterface');
		$this->configurationService = $this->objectManager->get('Tx_Flux_Service_FluxService');
	}

	/**
	 * Hook for generating dynamic FlexForm source code
	 *
	 * @param array $dataStructArray
	 * @param array $conf
	 * @param array $row
	 * @param string $table
	 * @param string $fieldName
	 * @return void
	 * @throws Exception
	 */
	public function getFlexFormDS_postProcessDS(&$dataStructArray, $conf, &$row, $table, $fieldName) {
		if (Tx_Flux_Utility_Version::assertHasFixedFlexFormFieldNamePassing() === FALSE) {
			$fieldName = NULL;
		}
		if (empty($fieldName) === TRUE) {
				// forcibly assert type NULL if an empty field name was passed. There are
				// no empty fields in a database, it's a plain mystery why TYPO3 may pass ''
			$fieldName = NULL;
		}
		$providers = $this->configurationService->resolveConfigurationProviders($table, $fieldName, $row);
		foreach ($providers as $provider) {
			try {
				/** @var Tx_Flux_Provider_ConfigurationProviderInterface $provider */
				$provider->postProcessDataStructure($row, $dataStructArray, $conf);
			} catch (Exception $e) {
				$this->configurationService->debug($e);
			}
		}
	}

}
