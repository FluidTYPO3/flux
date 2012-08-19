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
class Tx_Flux_Provider_ConfigurationService implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Resolves a ConfigurationProvider which can provide a working FlexForm
	 * configuration based on the given parameters.
	 *
	 * @param string $table
	 * @param string $fieldName
	 * @param array $row
	 * @param string $extensionKey
	 * @return array<Tx_Flux_Provider_ConfigurationProviderInterface>|NULL
	 */
	public function resolveConfigurationProviders($table, $fieldName, array $row=NULL, $extensionKey=NULL) {
		$providers = Tx_Flux_Core::getRegisteredFlexFormProviders();
		$prioritizedProviders = array();
		foreach ($providers as $providerClassNameOrInstance) {
			if (is_object($providerClassNameOrInstance)) {
				$provider = &$providerClassNameOrInstance;
			} else {
				$provider = $this->objectManager->create($providerClassNameOrInstance);
			}
			$priority = $provider->getPriority($row);
			$providerFieldName = $provider->getFieldName($row);
			$providerExtensionKey = $provider->getExtensionKey($row);
			$providerTableName = $provider->getTableName($row);
			if (is_array($prioritizedProviders[$priority]) === FALSE) {
				$prioritizedProviders[$priority] = array();
			}
			/** @var Tx_Flux_Provider_ConfigurationProviderInterface $provider */
			if ($providerTableName === $table && $providerFieldName == $fieldName && ($extensionKey === NULL || $providerExtensionKey === $extensionKey)) {
				if ($provider instanceof Tx_Flux_Provider_ContentObjectConfigurationProviderInterface) {
					/** @var Tx_Flux_Provider_ContentObjectConfigurationProviderInterface $provider */
					if ($provider->getContentObjectType($row) === $row['CType']) {
						$prioritizedProviders[$priority][] = $provider;
					}
				} elseif ($provider instanceof Tx_Flux_Provider_PluginConfigurationProviderInterface) {
					/** @var Tx_Flux_Provider_PluginConfigurationProviderInterface $provider */
					if ($provider->getListType($row) === $row['list_type']) {
						$prioritizedProviders[$priority][] = $provider;
					}
				} else {
					$prioritizedProviders[$priority][] = $provider;
				}
			}
		}
		ksort($prioritizedProviders);
		$providersToReturn = array();
		foreach ($prioritizedProviders as $providerSet) {
			foreach ($providerSet as $provider) {
				array_push($providersToReturn, $provider);
			}
		}
		return $providersToReturn;
	}

}

?>