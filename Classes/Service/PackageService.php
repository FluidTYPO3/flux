<?php
namespace FluidTYPO3\Flux\Service;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Collection\CollectableInterface;
use FluidTYPO3\Flux\Collection\Collection;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Package\PackageDetector;
use FluidTYPO3\Flux\Package\PackageInterface;
use FluidTYPO3\Flux\Package\StandardPackage;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Service\ExtensionService;

/**
 * Class PackageService
 */
class PackageService implements SingletonInterface {

	const CACHE_IDENTITY = 'packages';
	const LEGACY_PACKAGE_NAME = 'FluidTYPO3.Legacy';

	/**
	 * @var CacheManager
	 */
	protected $cacheManager;

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Collection
	 */
	protected $packages = NULL;

	/**
	 * @param CacheManager $cacheManager
	 * @return void
	 */
	public function injectCacheManager(CacheManager $cacheManager) {
		$this->cacheManager = $cacheManager;
	}

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Allows reading all providers, plugins, modules etc.
	 * which have been defined by all packages. To read
	 * the corresponding collection from one package only,
	 * use getPackage($name)->getProviders() etc.
	 *
	 * Usage example:
	 *
	 * ->getCombinedCollection(PackageInterface::COLLECTION_PROVIDERS);
	 *
	 * @param string $type
	 * @return Collection
	 */
	public function getCombinedCollection($type) {
		$collection = new Collection();
		foreach ($this->getAllInstalledPackages()->getAll() as $package) {
			$getter = 'get' . ucfirst($type);
			$other = $package->$getter();
			$collection->merge($other);
		}
		return $collection;
	}

	/**
	 * @param string $name
	 * @return CollectableInterface
	 */
	public function getPackage($name) {
		return $this->getAllInstalledPackages()->get($name);
	}

	/**
	 * @return Collection
	 */
	public function getAllInstalledPackages() {
		if (NULL === $this->packages) {
			if (TRUE === $this->cacheManager->getCache('flux')->has(self::CACHE_IDENTITY)) {
				$this->packages = $this->cacheManager->getCache('flux')->get(self::CACHE_IDENTITY);
			} else {
				$this->packages = new Collection();
				$packageClassNames = $this->detectAllInstalledPackageClassNames();
				foreach ($packageClassNames as $packageClassName) {
					/** @var PackageInterface $package */
					$package = $this->objectManager->get($packageClassName);
					$this->packages->add($package);
				}
				$this->cacheManager->getCache('flux')->set(self::CACHE_IDENTITY, $this->packages);
			}
		}
		// LEGACY SUPPORT: a "Legacy" package must be created to contain Providers
		// which were registered using legacy methods (Flux Core class API).
		$this->loadLegacyPackage();
		return $this->packages;
	}

	/**
	 * @return void
	 */
	protected function loadLegacyPackage() {
		// LEGACY SUPPORT: affix Flux Core class registered Providers
		$legacyPackage = $this->packages->get(self::LEGACY_PACKAGE_NAME);
		if (NULL === $legacyPackage) {
			$legacyPackage = new StandardPackage(self::LEGACY_PACKAGE_NAME);
		}
		foreach (Core::getRegisteredFlexFormProviders() as $providerClassName) {
			/** @var ProviderInterface $provider */
			$provider = $this->objectManager->get($providerClassName);
			$legacyPackage->getProviders()->add($provider);
		}
		$this->packages->add($legacyPackage);
	}

	/**
	 * @return array
	 */
	protected function detectAllInstalledPackageClassNames() {
		$detector = new PackageDetector();
		$keys = ExtensionManagementUtility::getLoadedExtensionListArray();
		return $detector->detectFromExtensionKeys($keys);
	}

}
