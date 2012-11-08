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
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Flux_Provider_ConfigurationService
	 */
	protected $configurationService;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->configurationManager = $this->objectManager->get('Tx_Extbase_Configuration_ConfigurationManagerInterface');
		$this->flexformService = $this->objectManager->get('Tx_Flux_Service_FlexForm');
		$this->configurationService = $this->objectManager->get('Tx_Flux_Provider_ConfigurationService');
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
		if (empty($fieldName) === TRUE) {
			$fieldName = NULL;
		}
			// check for versions of TYPO3 which do not consistently pass $fieldName
		$version = explode('.', TYPO3_version);
		$isRecent4x5 = ($version[0] == 4 && $version[1] == 5 && $version[2] >= 22);
		$isRecent4x6 = ($version[0] == 4 && $version[1] == 6 && $version[2] >= 15);
		$isRecent4x7 = ($version[0] == 4 && $version[1] == 7 && $version[2] >= 7);
		$isAbove4 = ($version[0] > 4);
		if ($isRecent4x5 === FALSE || $isRecent4x6 === FALSE || $isRecent4x7 === FALSE || $isAbove4 === FALSE) {
			$fieldName = NULL;
		}
		$providers = $this->configurationService->resolveConfigurationProviders($table, $fieldName, $row);
		foreach ($providers as $provider) {
			try {
				/** @var Tx_Flux_Provider_ConfigurationProviderInterface $provider */
				$provider->postProcessDataStructure($row, $dataStructArray, $conf);
			} catch (Exception $e) {
				if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] > 0) {
					throw $e;
				} else {
					t3lib_div::sysLog($e->getMessage(), 'flux');
					t3lib_FlashMessageQueue::addMessage(new t3lib_FlashMessage($e->getMessage() . ' (code ' . $e->getCode() . ')', t3lib_FlashMessage::ERROR, TRUE));
				}
			}
		}
	}

}
