<?php
namespace FluidTYPO3\Flux\Package;
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
 *****************************************************************/

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\View\TemplatePath;
use FluidTYPO3\Flux\View\TemplatePathsDefinition;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use FluidTYPO3\Flux\Collection\Collection;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use FluidTYPO3\Flux\Provider\ProviderInterface;

/**
 * Class StandardPackage
 *
 * Base implementation of a Flux package which implements the
 * required interface and can be subclassed to create new
 * packages without needing to define all methods of the interface.
 */
class StandardPackage implements PackageInterface {

	/**
	 * @var TemplatePathsDefinition
	 */
	protected $templatePaths;

	/**
	 * @var string
	 */
	protected $extensionKey;

	/**
	 * @var string
	 */
	protected $extensionName;

	/**
	 * @var string
	 */
	protected $packageName;

	/**
	 * @var Collection
	 */
	protected $settings;

	/**
	 * @var Collection
	 */
	protected $plugins;

	/**
	 * @var Collection
	 */
	protected $modules;

	/**
	 * @var Collection
	 */
	protected $providers;

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Constructor
	 */
	public function __construct() {
		$packageExtensionName = implode('.', array_slice(explode('\\', get_called_class()), 0, 2));
		$this->settings = new Collection();
		$this->providers = new Collection();
		$this->plugins = new Collection();
		$this->modules = new Collection();
		$this->packageName = $packageExtensionName;
		$this->extensionKey = ExtensionNamingUtility::getExtensionKey($packageExtensionName);
		$this->extensionName = ExtensionNamingUtility::getExtensionName($packageExtensionName);
		$this->templatePaths = new TemplatePathsDefinition($this->extensionKey);
	}

	/**
	 * @return void
	 */
	public function initializeObject() {
		$this->initializeSettings();
		$this->initializeProviders();
		$this->initializePlugins();
		$this->initializeModules();
	}

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Initializes Provider instances stored in $this->providers.
	 *
	 * Usage example inside method body:
	 *
	 * $provider = $this->objectManager->get($providerClassName);
	 * $this->providers->add($contentProvider);
	 * $provider = $this->providers->get('valueofnamepropertyofprovider');
	 * $this->providers->remove('valueofnamepropertyofprovider');
	 * $this->providers->remove($provider);
	 *
	 * @return void
	 */
	public function initializeProviders() {
	}

	/**
	 * Initialize settings - much like initializing arguments
	 * on a ViewHelper class. Fills settings with PackageSetting
	 * instances reflecting package-level configuration options.
	 *
	 * STUB METHOD - OVERRIDE THIS IN YOUR IMPLEMENTATION.
	 *
	 * Example:
	 *
	 * $currentValue = FALSE; // retrieve this from EXTCONF.
	 * $this->settings->add(
	 *     new PackageSetting(
	 *         'enableFeature',
	 *         PackageSetting::TYPE_BOOLEAN,
	 *         'Enable feature X?',
	 *         'togglesgroup',
	 *         TRUE,
	 *         $currentValue)
	 *     );
	 *
	 * @return void
	 */
	public function initializeSettings() {
	}

	/**
	 * Initialize plugins. Create the necessary plugin definitions
	 * to run controllers from your package in the frontend.
	 *
	 * AUTOMATED METHOD - IF OVERRIDDEN, MUST SPECIFY EVERY PLUGIN.
	 *
	 * Example usage inside method body:
	 *
	 * $icon = new \SplFileObject(GeneralUtility::getFileAbsFilename('EXT:myext/icon.gif'));
	 * $this->plugins->add(
	 *     new PluginDefinition(
	 *         'plugin_signature',
	 *         'My plugin, or LLL: reference',
	 *         $icon,
	 *         array('Controller' => 'cachedaction1,cachedaction2','uncachedaction3'),
	 *         array('Controller' => 'uncachedaction3')
	 *     )
	 * );
	 * $this->plugins->get('plugin_signature');
	 * $this->plugins->remove('plugin_signature');
	 *
	 * @return void
	 */
	public function initializePlugins() {
	}

	/**
	 * Initialize modules. Create the necessary module definitions
	 * to run (backend) controllers from your package as modules.
	 *
	 * AUTOMATED METHOD - IF OVERRIDDEN, MUST SPECIFY EVERY MODULE.
	 *
	 * Example usage inside method body:
	 *
	 * $icon = new \SplFileObject(GeneralUtility::getFileAbsFilename('EXT:myext/icon.gif'));
	 * $this->modules->add(
	 *     new ModuleDefinition(
	 *         'module_signature',
	 *         'My plugin, or LLL: reference',
	 *         $icon,
	 *         array('Controller' => 'cachedaction1,cachedaction2','uncachedaction3'),
	 *         array('Controller' => 'uncachedaction3'),
	 *         'web'
	 *     )
	 * );
	 *
	 * @return void
	 */
	public function initializeModules() {
	}

	/**
	 * Get instances of all Providers associated with
	 * this package.
	 *
	 * @return Collection
	 */
	public function getProviders() {
		return $this->providers;
	}

	/**
	 * Get class names of all Outlets associated with
	 * this package.
	 *
	 * @return array
	 */
	public function getOutletClassNames() {
		return array();
	}

	/**
	 * Get class names of all Pipes associated with
	 * this package.
	 *
	 * @return array
	 */
	public function getPipeClassNames() {
		return array();
	}

	/**
	 * Get definitions of all plugins associated with
	 * this package.
	 *
	 * @return PluginDefinition[]
	 */
	public function getPlugins() {
		return $this->plugins->getAll();
	}

	/**
	 * Get definitions of all modules associated with
	 * this package.
	 *
	 * @return ModuleDefinition[]
	 */
	public function getModules() {
		return $this->modules->getAll();
	}

	/**
	 * Get an array of controller names - not full class
	 * names, rather "Content", "Page" etc. - which are
	 * provided by this package.
	 *
	 * @return array
	 */
	public function getControllerNames() {
		return array();
	}

	/**
	 * Get an instance of TemplatePathsDefinition with default
	 * paths for this package. Paths may be overruled by TS.
	 *
	 * @return TemplatePathsDefinition
	 */
	public function getTemplatePathsDefinition() {
		return $this->templatePaths;
	}

	/**
	 * Get a Collection instance containg PackageSetting
	 * instances reflecting package-level configuration options.
	 *
	 * @return PackageSetting[]
	 */
	public function getPackageSettings() {
		return $this->settings->getAll();
	}

	/**
	 * Get the simple "extension_key" format name of package
	 * to which this descriptor belongs.
	 *
	 * @return string
	 */
	public function getExtensionKey() {
		return $this->extensionKey;
	}

	/**
	 * Get the "ExtensionKey" format name of package
	 * to which this descriptor belongs.
	 *
	 * @return string
	 */
	public function getExtensionName() {
		return $this->extensionName;
	}

	/**
	 * Get the "Vendor.ExtensionKey" format name of package
	 * to which this descriptor belongs.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->packageName;
	}

}
