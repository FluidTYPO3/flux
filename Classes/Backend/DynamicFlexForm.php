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
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @package Fed
 * @subpackage Backend
 */
class Tx_Flux_Backend_DynamicFlexForm {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 *
	 * @var Tx_Flux_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexformService;

	/**
	 * @var Tx_Flux_Provider_ConfigurationService
	 */
	protected $configurationService;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->configurationManager = $this->objectManager->get('Tx_Flux_Configuration_ConfigurationManager');
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
	 */
	public function getFlexFormDS_postProcessDS(&$dataStructArray, $conf, &$row, $table, $fieldName) {
		$provider = $this->configurationService->resolveConfigurationProvider($table, $fieldName, $row, $dataStructArray);
		if ($provider) {
			try {
				$typoScript = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
				$paths = Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray((array) $typoScript['plugin.']['tx_flux.']['view.']);
				$paths = Tx_Fed_Utility_Path::translatePath($paths);
				$values = $this->flexformService->convertFlexFormContentToArray($row[$fieldName ? $fieldName : 'pi_flexform']);
				$values = array_merge((array) $provider->getTemplateVariables($row), $values);
				$section = $provider->getConfigurationSectionName($row);
				if (strpos($section, 'variable:') !== FALSE) {
					$section = $values[array_pop(explode(':', $section))];
				}
				$this->flexformService->convertFlexFormContentToDataStructure($provider->getTemplatePathAndFilename($row), $values, $paths, $dataStructArray, $section);
			} catch (Exception $e) {
				t3lib_div::sysLog($e->getMessage(), 'flux');
				if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] > 0) {
					throw $e;
				} else {
					$dataStructArray = $this->flexformService->getFallbackDataStructure('Error', 'Tx_Flux_UserFunction_ErrorReporter->renderField', array('exception' => $e));
				}
			}
		}
	}

}

?>