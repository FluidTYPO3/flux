<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Configuration Service
 *
 * Service to interact with site configuration
 *
 * @package Flux
 * @subpackage Service
 */
class Tx_Flux_Service_Configuration implements t3lib_Singleton {

	/**
	 * @var array
	 */
	private static $cache = array();

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Flux_Service_DebugService
	 */
	protected $debugService;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param Tx_Flux_Service_DebugService $debugService
	 * @return void
	 */
	public function injectDebugService(Tx_Flux_Service_DebugService $debugService) {
		$this->debugService = $debugService;
	}

	/**
	 * @param string $reference
	 * @param string $controllerObjectShortName
	 * @param boolean $failHardClass
	 * @param boolean $failHardAction
	 * @return string|NULL
	 */
	public function resolveFluxControllerClassName($reference, $controllerObjectShortName, $failHardClass = FALSE, $failHardAction = FALSE) {
		list ($extensionKey, $action) = explode('->', $reference);
		$action{0} = strtolower($action{0});
		$extensionName = ucfirst(t3lib_div::underscoredToLowerCamelCase($extensionKey));
		$potentialControllerClassName = 'Tx_' . $extensionName . '_Controller_' . $controllerObjectShortName . 'Controller';
		if (FALSE === class_exists($potentialControllerClassName)) {
			if (TRUE === $failHardClass) {
				throw new Exception('Class ' . $potentialControllerClassName . ' does not exist. It was build from: ' . var_export($reference, TRUE) .
					' but the resulting class name was not found.', 1364498093);
			}
			return NULL;
		}
		if (FALSE === method_exists($potentialControllerClassName, $action . 'Action')) {
			if (TRUE === $failHardAction) {
				throw new Exception('Class ' . $potentialControllerClassName . ' does not contain a method named ' . $action . 'Action', 1364498223);
			}
			return NULL;
		}
		return $potentialControllerClassName;
	}

	/**
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getViewConfiguration($extensionName) {
		$configuration = $this->getTypoScriptSubConfiguration(NULL, 'view', array(), $extensionName);
		return $configuration;
	}

	/**
	 * Gets an array of TypoScript configuration from below plugin.tx_fed -
	 * if $extensionName is set in parameters it is used to indicate which sub-
	 * section of the result to return.
	 *
	 * @param string $extensionName
	 * @param string $memberName
	 * @param array $dontTranslateMembers Array of members not to be translated by path
	 * @param string $containerExtensionScope If TypoScript is not located under plugin.tx_fed, change the tx_<scope> part by specifying this argument
	 * @return array
	 */
	public function getTypoScriptSubConfiguration($extensionName, $memberName, $dontTranslateMembers = array(), $containerExtensionScope = 'fed') {
		$containerExtensionScope = str_replace('_', '', $containerExtensionScope);
		$cacheKey = $extensionName . $memberName . $containerExtensionScope;
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$config = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$config = $config['plugin.']['tx_' . $containerExtensionScope . '.'][$memberName . '.'];
		if (is_array($config) === FALSE) {
			return array();
		}
		if ($this->checkDependenciesForConfiguration($config) === FALSE) {
			return array();
		}
		$config = Tx_Flux_Utility_Array::convertTypoScriptArrayToPlainArray($config);
		if ($extensionName) {
			$config = $config[$extensionName];
		}
		if (is_array($config) === FALSE) {
			return array();
		}
		$config = Tx_Flux_Utility_Path::translatePath($config);
		self::$cache[$cacheKey] = $config;
		return $config;
	}

	/**
	 * @param array $configuration
	 * @return boolean
	 */
	public function checkDependenciesForConfiguration($configuration) {
		if (isset($configuration['dependencies']) === TRUE) {
			$dependencies = t3lib_div::trimExplode(',', $configuration['dependencies']);
			foreach ($dependencies as $dependency) {
				if (!t3lib_extMgm::isLoaded($dependency)) {
					$messageText = 'The Fluid configuration set named ' .
						$configuration['label'] . ' depends on extension ' . $dependency . ' (all dependencies: ' .
						implode(',', $dependencies) . ') but ' . $dependency . ' was not loaded';
					$this->debugService->message($messageText, t3lib_div::SYSLOG_SEVERITY_WARNING);
					return FALSE;
				}
			}
		}
		return TRUE;
	}

}