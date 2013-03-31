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
 * ConfigurationService
 *
 * @package Flux
 * @subpackage Provider
 */
class Tx_Flux_Provider_ConfigurationService implements t3lib_Singleton {

	/**
	 * @var array
	 */
	private static $cache = array();

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
	 * Resolve the top-priority ConfigurationPrivider which can provide
	 * a working FlexForm configuration baed on the given parameters.
	 *
	 * @param string $table
	 * @param string $fieldName
	 * @param array $row
	 * @param string $extensionKey
	 * @return Tx_Flux_Provider_ConfigurationProviderInterface|NULL
	 */
	public function resolvePrimaryConfigurationProvider($table, $fieldName, array $row = NULL, $extensionKey = NULL) {
		$providers = $this->resolveConfigurationProviders($table, $fieldName, $row, $extensionKey);
		$priority = 0;
		$providerWithTopPriority = NULL;
		foreach ($providers as $provider) {
			if ($provider->getPriority($row) >= $priority) {
				$providerWithTopPriority = $provider;
			}
		}
		return $providerWithTopPriority;
	}

	/**
	 * Resolves a ConfigurationProvider which can provide a working FlexForm
	 * configuration based on the given parameters.
	 *
	 * @param string $table
	 * @param string $fieldName
	 * @param array $row
	 * @param string $extensionKey
	 * @return Tx_Flux_Provider_ConfigurationProviderInterface[]
	 */
	public function resolveConfigurationProviders($table, $fieldName, array $row=NULL, $extensionKey=NULL) {
		if (is_array($row) === FALSE) {
			$row = array();
		}
		$rowChecksum = md5(json_encode($row));
		$cacheKey = $table . $fieldName . $rowChecksum . $extensionKey;
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$bindToFieldName = Tx_Flux_Utility_Version::assertHasFixedFlexFormFieldNamePassing();
		$providers = Tx_Flux_Core::getRegisteredFlexFormProviders();
		$prioritizedProviders = array();
		foreach ($providers as $providerClassNameOrInstance) {
			if (is_object($providerClassNameOrInstance)) {
				$provider = &$providerClassNameOrInstance;
			} else {
				$providerCacheKey = $table . $fieldName . $rowChecksum . $extensionKey . $providerClassNameOrInstance;
				if (TRUE === isset(self::$cache[$providerCacheKey])) {
					$provider = &self::$cache[$providerCacheKey];
				} else {
					$provider = $this->objectManager->create($providerClassNameOrInstance);
				}
			}
			$priority = $provider->getPriority($row);
			$providerFieldName = $provider->getFieldName($row);
			$providerExtensionKey = $provider->getExtensionKey($row);
			$providerTableName = $provider->getTableName($row);
			if (is_array($prioritizedProviders[$priority]) === FALSE) {
				$prioritizedProviders[$priority] = array();
			}
			$matchesTableName = ($providerTableName === $table);
			$matchesFieldName = ($providerFieldName === $fieldName || $bindToFieldName === FALSE || $fieldName === NULL);
			$matchesExtensionKey = ($providerExtensionKey === $extensionKey || $extensionKey === NULL);
			/** @var Tx_Flux_Provider_ConfigurationProviderInterface $provider */
			if ($matchesExtensionKey && $matchesTableName && $matchesFieldName) {
				if ($provider instanceof Tx_Flux_Provider_ContentObjectConfigurationProviderInterface) {
					/** @var Tx_Flux_Provider_ContentObjectConfigurationProviderInterface $provider */
					if (isset($row['CType']) === FALSE || $provider->getContentObjectType($row) === $row['CType']) {
						$prioritizedProviders[$priority][] = $provider;
					}
				} elseif ($provider instanceof Tx_Flux_Provider_PluginConfigurationProviderInterface) {
					/** @var Tx_Flux_Provider_PluginConfigurationProviderInterface $provider */
					if (isset($row['list_type']) === FALSE || $provider->getListType($row) === $row['list_type']) {
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
		self::$cache[$cacheKey] = $providersToReturn;
		return $providersToReturn;
	}

}
