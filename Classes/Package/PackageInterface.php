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

use FluidTYPO3\Flux\Collection\CollectableInterface;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\View\TemplatePathsDefinition;
use FluidTYPO3\Flux\Collection\Collection;

/**
 * Interface PackageInterface
 *
 * Implemented by classes in EXT:key/Classes/FluxPackage.php to
 * provide various setup information about each package/extension.
 */
interface PackageInterface extends CollectableInterface {

	const COLLECTION_PROVIDERS = 'providers';
	const COLLECTION_PLUGINS = 'plugins';
	const COLLECTION_MODULES = 'modules';
	const COLLECTION_PACKAGESETTINGS = 'packageSettings';

	/**
	 * Initialize settings - much like initializing arguments
	 * on a ViewHelper class. Fills settings with PackageSetting
	 * instances reflecting package-level configuration options.
	 *
	 * STUB METHOD - OVERRIDE THIS IN YOUR IMPLEMENTATION.
	 *
	 * Example usage inside method body:
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
	 * );
	 * $this->settings->get('enableFeature');
	 * $this->settings->remove('enableFeature');
	 *
	 * @return void
	 */
	public function initializeSettings();

	/**
	 * Initialize plugins. Create the necessary plugin definitions
	 * to run controllers from your package in the frontend.
	 *
	 * AUTOMATED METHOD - IF OVERRIDDEN, MUST SPECIFY EVERY PLUGIN.
	 *
	 * Example usage inside method body:
	 *
	 * $this->plugins->add(
	 *     new PluginDefinition(
	 *         'plugin_signature',
	 *         'My plugin, or LLL: reference',
	 *         GeneralUtility::getFileAbsFilename('EXT:myext/icon.gif'),
	 *         array('Controller' => 'cachedaction1,cachedaction2','uncachedaction3'),
	 *         array('Controller' => 'uncachedaction3')
	 *     )
	 * );
	 * $this->plugins->get('plugin_signature');
	 * $this->plugins->remove('plugin_signature');
	 *
	 * @return void
	 */
	public function initializePlugins();

	/**
	 * Initialize modules. Create the necessary module definitions
	 * to run (backend) controllers from your package as modules.
	 *
	 * AUTOMATED METHOD - IF OVERRIDDEN, MUST SPECIFY EVERY MODULE.
	 *
	 * Example usage inside method body:
	 *
	 * $this->modules->add(
	 *     new ModuleDefinition(
	 *         'module_signature',
	 *         'My plugin, or LLL: reference',
	 *         GeneralUtility::getFileAbsFilename('EXT:myext/icon.gif'),
	 *         array('Controller' => 'cachedaction1,cachedaction2','uncachedaction3'),
	 *         array('Controller' => 'uncachedaction3'),
	 *         'web'
	 *     )
	 * );
	 *
	 * @return void
	 */
	public function initializeModules();

	/**
	 * Get the simple "extension_key" format name of package
	 * to which this descriptor belongs.
	 *
	 * @return string
	 */
	public function getExtensionKey();

	/**
	 * Get the "ExtensionKey" format name of package
	 * to which this descriptor belongs.
	 *
	 * @return string
	 */
	public function getExtensionName();

	/**
	 * Get the "Vendor.ExtensionKey" format name of package
	 * to which this descriptor belongs.
	 *
	 * @return string
	 */
	public function getName();

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
	public function initializeProviders();

	/**
	 * Get instances of all Providers associated with
	 * this package.
	 *
	 * @return Collection
	 */
	public function getProviders();

	/**
	 * Get class names of all Outlets associated with
	 * this package.
	 *
	 * @return array
	 */
	public function getOutletClassNames();

	/**
	 * Get class names of all Pipes associated with
	 * this package.
	 *
	 * @return array
	 */
	public function getPipeClassNames();

	/**
	 * Get definitions of all plugins associated with
	 * this package.
	 *
	 * @return Collection
	 */
	public function getPlugins();

	/**
	 * Get definitions of all modules associated with
	 * this package.
	 *
	 * @return Collection
	 */
	public function getModules();

	/**
	 * Get an array of controller names - not full class
	 * names, rather "Content", "Page" etc. - which are
	 * provided by this package.
	 *
	 * @return array
	 */
	public function getControllerNames();

	/**
	 * Get an instance of TemplatePathsDefinition with default
	 * paths for this package. Paths may be overruled by TS.
	 *
	 * @return TemplatePathsDefinition
	 */
	public function getTemplatePathsDefinition();

	/**
	 * Get a Collection instance containg PackageSetting
	 * instances reflecting package-level configuration options.
	 *
	 * @return Collection
	 */
	public function getPackageSettings();

}
