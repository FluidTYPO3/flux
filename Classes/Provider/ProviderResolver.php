<?php
namespace FluidTYPO3\Flux\Provider;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Provider Resolver
 *
 * Returns one or more Provider instances based on parameters.
 *
 * @package FluidTYPO3\Flux
 */
class ProviderResolver {

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @var ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * ResolveUtility the top-priority ConfigurationPrivider which can provide
	 * a working FlexForm configuration baed on the given parameters.
	 *
	 * @param string $table
	 * @param string $fieldName
	 * @param array $row
	 * @param string $extensionKey
	 * @return ProviderInterface|NULL
	 */
	public function resolvePrimaryConfigurationProvider($table, $fieldName, array $row = NULL, $extensionKey = NULL) {
		if (is_array($row) === FALSE) {
			$row = array();
		}
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
	 * @throws \RuntimeException
	 * @return ProviderInterface[]
	 */
	public function resolveConfigurationProviders($table, $fieldName, array $row = NULL, $extensionKey = NULL) {
		if (FALSE === is_array($row)) {
			$row = array();
		}
		$providers = Core::getRegisteredFlexFormProviders();
		$typoScriptConfigurationProviders = $this->loadTypoScriptConfigurationProviderInstances();
		$providers = array_merge($providers, $typoScriptConfigurationProviders);
		$prioritizedProviders = array();
		foreach ($providers as $providerClassNameOrInstance) {
			if (TRUE === is_object($providerClassNameOrInstance)) {
				$provider = &$providerClassNameOrInstance;
			} else {
				$provider = $this->objectManager->get($providerClassNameOrInstance);
			}
			if (FALSE === in_array('FluidTYPO3\Flux\Provider\ProviderInterface', class_implements($providerClassNameOrInstance))) {
				throw new \RuntimeException(is_object($providerClassNameOrInstance) ? get_class($providerClassNameOrInstance) : $providerClassNameOrInstance . ' must implement ProviderInterfaces from Flux/Provider', 1327173536);
			}
			if (TRUE === $provider->trigger($row, $table, $fieldName, $extensionKey)) {
				$priority = $provider->getPriority($row);
				if (FALSE === is_array($prioritizedProviders[$priority])) {
					$prioritizedProviders[$priority] = array();
				}
				$prioritizedProviders[$priority][] = $provider;
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

	/**
	 * @return ProviderInterface[]
	 */
	public function loadTypoScriptConfigurationProviderInstances() {
		$typoScriptSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (FALSE === isset($typoScriptSettings['plugin.']['tx_flux.']['providers.'])) {
			return array();
		}
		$providerConfigurations = GeneralUtility::removeDotsFromTS($typoScriptSettings['plugin.']['tx_flux.']['providers.']);
		$providers = array();
		foreach ($providerConfigurations as $name => $providerSettings) {
			$className = 'FluidTYPO3\Flux\Provider\Provider';
			if (TRUE === isset($providerSettings['className']) && TRUE === class_exists($providerSettings['className'])) {
				$className = $providerSettings['className'];
			}
			/** @var ProviderInterface $provider */
			$provider = $this->objectManager->get($className);
			$provider->setName($name);
			$provider->loadSettings($providerSettings);
			$providers[$name] = $provider;
		}
		return $providers;
	}

}
