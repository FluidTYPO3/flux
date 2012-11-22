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
 * Provides methods to read various configuration related
 * to Fluid Content Elements.
 *
 * @author Claus Due, Wildside A/S
 * @package Fluidcontent
 * @subpackage Service
 */
class Tx_Fluidcontent_Service_ConfigurationService implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Get definitions of paths for FCEs defined in TypoScript
	 *
	 * @param string $extensionName Optional extension name to get only that extension
	 * @return array
	 * @api
	 */
	public function getContentConfiguration($extensionName = NULL) {
		return $this->getTypoScriptSubConfiguration($extensionName, 'fce', array('label', 'dependencies'));
	}

	/**
	 * Gets an array of TypoScript configuration from below plugin.tx_fed -
	 * if $extensionName is set in parameters it is used to indicate which sub-
	 * section of the result to return.
	 *
	 * @param string $extensionName
	 * @param string $memberName
	 * @param array $dontTranslateMembers Array of members not to be translated by path
	 * @return array
	 */
	protected function getTypoScriptSubConfiguration($extensionName, $memberName, $dontTranslateMembers = array()) {
		$config = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$config = $config['plugin.']['tx_fed.'][$memberName . '.'];
		if (is_array($config) === FALSE) {
			return array();
		}
		if ($this->checkDependenciesForContentElementConfiguration($config) === FALSE) {
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
		return $config;
	}

	/**
	 * @param array $configuration
	 * @return boolean
	 */
	public function checkDependenciesForContentElementConfiguration($configuration) {
		if (isset($configuration['dependencies']) === TRUE) {
			$dependencies = t3lib_div::trimExplode(',', $configuration['dependencies']);
			foreach ($dependencies as $dependency) {
				if (!t3lib_extMgm::isLoaded($dependency)) {
					$messageText = 'The Fluid Content Element configuration set named ' .
						$configuration['label'] . ' depends on extension ' . $dependency . ' (all dependencies: ' .
						implode(',', $dependencies) . ') but ' . $dependency . ' was not loaded';
					$flashMessage = new \TYPO3\CMS\Core\Messaging\FlashMessage($messageText, 'Fluid Content Elements', t3lib_FlashMessage::WARNING);
					t3lib_FlashMessageQueue::addMessage($flashMessage);
					return FALSE;
				}
			}
		}
		return TRUE;
	}

}
