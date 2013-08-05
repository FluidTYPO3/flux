<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * FLUX CORE
 *
 * Quick-access API methods to easily integrate with Flux
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Core
 */
class Tx_Flux_Core {

	/**
	 * Contains all ConfigurationProviders registered with Flux
	 * @var array
	 */
	private static $providers = array();

	/**
	 * Contains ConfigurationProviders which have been unregistered
	 * @var array
	 */
	private static $unregisteredProviders = array();

	/**
	 * Contains all extensions registered with Flux
	 * @var array
	 */
	private static $extensions = array();

	/**
	 * Contains all programatically added TypoScript configuration files for auto-inclusion
	 * @var array
	 */
	private static $staticTypoScriptFiles = array();

	/**
	 * @return array
	 */
	public static function getStaticTypoScriptLocations() {
		return self::$staticTypoScriptFiles;
	}

	/**
	 * @param mixed $locationOrLocations
	 * @return void
	 */
	public static function addGlobalTypoScript($locationOrLocations) {
		if (TRUE === is_array($locationOrLocations) || TRUE === $locationOrLocations instanceof Traversable) {
			foreach ($locationOrLocations as $location) {
				self::addGlobalTypoScript($location);
			}
			return;
		} else {
			if (FALSE === in_array($locationOrLocations, self::$staticTypoScriptFiles)) {
				array_push(self::$staticTypoScriptFiles, $locationOrLocations);
			}
		}
	}

	/**
	 * @param string $extensionKey
	 * @param string $providesControllerName
	 * @return void
	 */
	public static function registerProviderExtensionKey($extensionKey, $providesControllerName) {
		if (FALSE === isset(self::$extensions[$providesControllerName])) {
			self::$extensions[$providesControllerName] = array();
		}

		if (FALSE === in_array($extensionKey, self::$extensions[$providesControllerName])) {
			array_push(self::$extensions[$providesControllerName], $extensionKey);
		}
	}

	/**
	 * @param string $forControllerName
	 * @return array
	 */
	public static function getRegisteredProviderExtensionKeys($forControllerName) {
		if (TRUE === isset(self::$extensions[$forControllerName])) {
			return self::$extensions[$forControllerName];
		}
		return array();
	}


	/**
	 * Registers a class implementing one of the Flux ConfigurationProvider
	 * interfaces.
	 *
	 * @param string|object $classNameOrInstance
	 * @return void
	 * @throws Exception
	 */
	public static function registerConfigurationProvider($classNameOrInstance) {
		if (is_object($classNameOrInstance) === FALSE) {
			if (class_exists($classNameOrInstance) === FALSE) {
				throw new Exception('Provider class ' . $classNameOrInstance . ' does not exists', 1327173514);
			}
		}
		if (in_array('Tx_Flux_Provider_ProviderInterface', class_implements($classNameOrInstance)) === FALSE) {
			throw new Exception(is_object($classNameOrInstance) ? get_class($classNameOrInstance) : $classNameOrInstance . ' must implement ProviderInterfaces from Flux/Provider', 1327173536);
		}
		if (in_array($classNameOrInstance, self::$unregisteredProviders) === FALSE && in_array($classNameOrInstance, self::$providers) === FALSE) {
			array_push(self::$providers, $classNameOrInstance);
		}
	}

	/**
	 * Registers a Fluid template for use as a Dynamic Flex Form template in the
	 * style of Flux's Fluid Content Element and Fluid Page configurations. See
	 * documentation web site for more detailed information about how to
	 * configure such a FlexForm template.
	 *
	 * Note: you can point to your Model Object templates and place the
	 * configuration in these templates - and get automatically transformed
	 * values from your FlexForms, i.e. a Domain Object instance from a "group"
	 * type select box or an ObjectStorage from a list of records. Usual output
	 * is completely ignored, only the "Configuration" section is considered.
	 *
	 * @param mixed $extensionKey The extension key which registered this FlexForm
	 * @param mixed $pluginSignature The plugin signature this FlexForm belongs to
	 * @param mixed $templateFilename Location of the Fluid template containing field definitions
	 * @param mixed $variables Optional array of variables to pass to Fluid template
	 * @param mixed|NULL Optional section name containing the configuration
	 * @param mixed|NULL Optional paths array / Closure to return paths
	 * @return void
	 */
	public static function registerFluidFlexFormPlugin($extensionKey, $pluginSignature, $templateFilename, $variables=array(), $section=NULL, $paths=NULL) {
		/** @var Tx_Extbase_Object_ObjectManagerInterface $objectManager */
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		/** @var $provider Tx_Flux_Provider_ProviderInterface */
		$provider = $objectManager->get('Tx_Flux_Provider_ContentProvider');
		$provider->setTableName('tt_content');
		$provider->setFieldName('');
		$provider->setExtensionKey($extensionKey);
		$provider->setListType($pluginSignature);
		$provider->setTemplatePathAndFilename($templateFilename);
		$provider->setTemplateVariables($variables);
		$provider->setTemplatePaths($paths);
		$provider->setConfigurationSectionName($section);
		self::registerConfigurationProvider($provider);
	}

	/**
	 * Same as registerFluidFlexFormPlugin, but uses a content object type for
	 * resolution - use this if you registered your Extbase plugin as a content
	 * object in your localconf.
	 *
	 * @param mixed $extensionKey The extension key which registered this FlexForm
	 * @param mixed $contentObjectType The cType of the object you registered
	 * @param mixed $templateFilename Location of the Fluid template containing field definitions
	 * @param mixed $variables Optional array of variables to pass to Fluid template
	 * @param mixed|NULL Optional section name containing the configuration
	 * @param mixed|NULL Optional paths array / Closure to return paths
	 * @return void
	 */
	public static function registerFluidFlexFormContentObject($extensionKey, $contentObjectType, $templateFilename, $variables=array(), $section=NULL, $paths=NULL) {
		/** @var $objectManager Tx_Extbase_Object_ObjectManagerInterface */
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		/** @var $provider Tx_Flux_Provider_ProviderInterface */
		$provider = $objectManager->get('Tx_Flux_Provider_ContentProvider');
		$provider->setTableName('tt_content');
		$provider->setFieldName('');
		$provider->setExtensionKey($extensionKey);
		$provider->setTemplatePathAndFilename($templateFilename);
		$provider->setTemplateVariables($variables);
		$provider->setTemplatePaths($paths);
		$provider->setConfigurationSectionName($section);
		$provider->setContentObjectType($contentObjectType);
		self::registerConfigurationProvider($provider);
	}

	/**
	 * Same as registerFluidFlexFormPlugin, but enables registering FlexForms
	 * for any TCA field (type "flex") or field whose TCA you have overridden
	 * to display as a FlexForm.
	 *
	 * @param mixed $table The SQL table this FlexForm is bound to
	 * @param mixed $fieldName The SQL field this FlexForm is bound to
	 * @param mixed $templateFilename Location of the Fluid template containing field definitions
	 * @param mixed $variables Optional array of variables to pass to Fluid template
	 * @param mixed|NULL Optional section name containing the configuration
	 * @param mixed|NULL Optional paths array / Closure to return paths
	 * @return void
	 */
	public static function registerFluidFlexFormTable($table, $fieldName, $templateFilename, $variables=array(), $section=NULL, $paths=NULL) {
		/** @var $objectManager Tx_Extbase_Object_ObjectManagerInterface */
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		/** @var $provider Tx_Flux_Provider_ProviderInterface */
		$provider = $objectManager->get('Tx_Flux_Provider_Provider');
		$provider->setTableName($table);
		$provider->setFieldName($fieldName);
		$provider->setTemplatePathAndFilename($templateFilename);
		$provider->setTemplateVariables($variables);
		$provider->setTemplatePaths($paths);
		$provider->setConfigurationSectionName($section);
		self::registerConfigurationProvider($provider);
	}

	/**
	 * @param string $providerClassName
	 * @return void
	 */
	public static function unregisterConfigurationProvider($providerClassName) {
		if (in_array($providerClassName, self::$providers) === TRUE) {
			$index = array_search($providerClassName, self::$providers);
			unset(self::$providers[$index]);
		}
		if (in_array($providerClassName, self::$unregisteredProviders) === FALSE) {
			array_push(self::$unregisteredProviders, $providerClassName);
		}
	}

	/**
	 * Gets the defined FlexForms configuration providers based on parameters
	 * @return array
	 */
	public static function getRegisteredFlexFormProviders() {
		reset(self::$providers);
		return self::$providers;
	}

}
