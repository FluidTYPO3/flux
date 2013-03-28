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
	 * @var Tx_Flux_Service_Debug
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
	 * @param Tx_Flux_Service_Debug $debugService
	 * @return void
	 */
	public function injectDebugService(Tx_Flux_Service_Debug $debugService) {
		$this->debugService = $debugService;
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
		$contentObject = $this->configurationManager->getContentObject();
		if ($contentObject) {
			$pid = $this->configurationManager->getContentObject()->data['pid'];
		} else {
			$pid = t3lib_div::_GET('id');
		}
		$cachedConfigurationPathAndFilename = PATH_site . 'typo3temp/flux-configuration-' . $containerExtensionScope . '-' .
			$extensionName . '-' . $memberName . '-' . $pid . '.ts';
		if (TRUE === file_exists($cachedConfigurationPathAndFilename)) {
			self::$cache[$extensionName.$memberName] = unserialize(file_get_contents($cachedConfigurationPathAndFilename));
		}
		if (TRUE === isset(self::$cache[$extensionName.$memberName])) {
			return self::$cache[$extensionName.$memberName];
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
		foreach ($config as $k => $v) {
			if ($extensionName) {
				if (in_array($k, $dontTranslateMembers) === FALSE) {
					$config[$k] = Tx_Flux_Utility_Path::translatePath($v);
				}
			} else {
				foreach ($v as $subkey=>$paths) {
					if (in_array($subkey, $dontTranslateMembers) === FALSE) {
						$config[$k][$subkey] = Tx_Flux_Utility_Path::translatePath($paths);
					}
				}
			}
		}
		self::$cache[$extensionName.$memberName] = $config;
		t3lib_div::writeFile($cachedConfigurationPathAndFilename, serialize($config));
		return self::$cache[$extensionName.$memberName];
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