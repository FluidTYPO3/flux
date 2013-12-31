<?php
namespace FluidTYPO3\Flux;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * FLUX CORE
 *
 * Quick-access API methods to easily integrate with Flux
 *
 * @package Flux
 * @subpackage Core
 */
class Core {

	/**
	 * Contains all ConfigurationProviders registered with Flux
	 * @var array
	 */
	private static $providers = array();

	/**
	 * @var array
	 */
	protected static $pipes = array();

	/**
	 * @var array
	 */
	protected static $outlets = array();

	/**
	 * Contains all Forms for tables registered with Flux
	 * @var array
	 */
	private static $forms = array(
		'models' => array(),
		'tables' => array()
	);

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
	private static $staticTypoScript = array();

	/**
	 * @return array
	 */
	public static function getStaticTypoScript() {
		return self::$staticTypoScript;
	}

	/**
	 * @param mixed $locationOrLocations
	 * @return void
	 */
	public static function addStaticTypoScript($locationOrLocations) {
		if (TRUE === is_array($locationOrLocations) || TRUE === $locationOrLocations instanceof Traversable) {
			foreach ($locationOrLocations as $location) {
				self::addStaticTypoScript($location);
			}
		} else {
			if (FALSE === in_array($locationOrLocations, self::$staticTypoScript)) {
				array_push(self::$staticTypoScript, $locationOrLocations);
			}
		}
	}

	/**
	 * @param string $table
	 * @param Form $form
	 * @return void
	 */
	public static function registerFormForTable($table, Form $form) {
		if (NULL === $form->getName()) {
			$form->setName($table);
		}
		if (NULL === $form->getExtensionName() && TRUE === isset($GLOBALS['_EXTKEY'])) {
			$form->setExtensionName(GeneralUtility::underscoredToUpperCamelCase($GLOBALS['_EXTKEY']));
		}
		self::$forms['tables'][$table] = $form;
	}

	/**
	 * Registers automatic Form instance building and use as TCA for a model object class/table.
	 *
	 * @param string $className
	 * @return void
	 */
	public static function registerAutoFormForModelObjectClassName($className) {
		self::registerFormForModelObjectClassName($className);
	}

	/**
	 * Registers a Form instance to use when TCA for a model object class/table is requested.
	 *
	 * @param string $className
	 * @param Form $form
	 * @return void
	 */
	public static function registerFormForModelObjectClassName($className, Form $form = NULL) {
		if (NULL !== $form && TRUE === isset($GLOBALS['_EXTKEY']) && NULL === $form->getExtensionName()) {
			$form->setExtensionName(GeneralUtility::underscoredToUpperCamelCase($GLOBALS['_EXTKEY']));
		}
		self::$forms['models'][$className] = $form;
	}

	/**
	 * @param string $className
	 * @return void
	 */
	public static function unregisterFormForModelObjectClassName($className) {
		if (TRUE === isset(self::$forms['models'][$className])) {
			unset(self::$forms['models'][$className]);
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
	 * @throws \RuntimeException
	 */
	public static function registerConfigurationProvider($classNameOrInstance) {
		if (FALSE === in_array($classNameOrInstance, self::$unregisteredProviders) && FALSE === in_array($classNameOrInstance, self::$providers)) {
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
	 * @param string $fieldName Optional fieldname if not from pi_flexform
	 * @return void
	 */
	public static function registerFluidFlexFormPlugin($extensionKey, $pluginSignature, $templateFilename, $variables=array(), $section=NULL, $paths=NULL, $fieldName='pi_flexform') {
		/** @var ObjectManagerInterface $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var $provider ProviderInterface */
		$provider = $objectManager->get('FluidTYPO3\Flux\Provider\ContentProvider');
		$provider->setTableName('tt_content');
		$provider->setFieldName($fieldName);
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
	 * @param string $fieldName Optional fieldname if not from pi_flexform
	 * @return void
	 */
	public static function registerFluidFlexFormContentObject($extensionKey, $contentObjectType, $templateFilename, $variables = array(), $section = NULL, $paths = NULL, $fieldName = 'pi_flexform') {
		/** @var $objectManager ObjectManagerInterface */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var $provider ProviderInterface */
		$provider = $objectManager->get('FluidTYPO3\Flux\Provider\ContentProvider');
		$provider->setTableName('tt_content');
		$provider->setFieldName($fieldName);
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
	public static function registerFluidFlexFormTable($table, $fieldName, $templateFilename, $variables = array(), $section = NULL, $paths = NULL) {
		/** @var $objectManager ObjectManagerInterface */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var $provider ProviderInterface */
		$provider = $objectManager->get('FluidTYPO3\Flux\Provider\Provider');
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
		if (TRUE === in_array($providerClassName, self::$providers)) {
			$index = array_search($providerClassName, self::$providers);
			unset(self::$providers[$index]);
		} elseif (FALSE === in_array($providerClassName, self::$unregisteredProviders)) {
			array_push(self::$unregisteredProviders, $providerClassName);
		}
	}

	/**
	 * @param string $typeOrClassName
	 * @param string $insteadOfNativeType
	 * @return void
	 */
	public static function registerPipe($typeOrClassName, $insteadOfNativeType = NULL) {
		$key = NULL === $insteadOfNativeType ? $typeOrClassName : $insteadOfNativeType;
		self::$pipes[$key] = $typeOrClassName;
	}

	/**
	 * @param string $typeOrClassName
	 */
	public static function unregisterPipe($typeOrClassName) {
		if (TRUE === in_array($typeOrClassName, self::$pipes)) {
			$index = array_search($typeOrClassName, self::$pipes);
			unset(self::$pipes[$index]);
		}
	}

	/**
	 * @param string $typeOrClassName
	 * @param string $insteadOfNativeType
	 * @return void
	 */
	public static function registerOutlet($typeOrClassName, $insteadOfNativeType = NULL) {
		$key = NULL === $insteadOfNativeType ? $typeOrClassName : $insteadOfNativeType;
		self::$outlets[$key] = $typeOrClassName;
	}

	/**
	 * @param string $typeOrClassName
	 */
	public static function unregisterOutlet($typeOrClassName) {
		if (TRUE === in_array($typeOrClassName, self::$outlets)) {
			$index = array_search($typeOrClassName, self::$outlets);
			unset(self::$outlets[$index]);
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

	/**
	 * @return Form[]
	 */
	public static function getRegisteredFormsForTables() {
		return self::$forms['tables'];
	}

	/**
	 * @param string $table
	 * @return Form|NULL
	 */
	public static function getRegisteredFormForTable($table) {
		if (TRUE === isset(self::$forms['tables'][$table])) {
			return self::$forms['tables'][$table];
		}
		return NULL;
	}

	/**
	 * @return Form[]
	 */
	public static function getRegisteredFormsForModelObjectClasses() {
		return self::$forms['models'];
	}

	/**
	 * @param string $class
	 * @return Form|NULL
	 */
	public static function getRegisteredFormForModelObjectClass($class) {
		if (TRUE === isset(self::$forms['models'][$class])) {
			return self::$forms['models'][$class];
		}
		return NULL;
	}

	/**
	 * @return array
	 */
	public static function getPipes() {
		return array_values(self::$pipes);
	}

	/**
	 * @return array
	 */
	public static function getOutlets() {
		return array_values(self::$outlets);
	}

}
