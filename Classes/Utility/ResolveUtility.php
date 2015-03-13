<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ResolveUtility utility
 *
 * @package Flux
 * @subpackage Utility
 */
class ResolveUtility {

	/**
	 * Resolves a list (array) of class names (not instances) of
	 * all classes in files in the specified sub-namespace of the
	 * specified package name. Does not attempt to load the class.
	 * Does not work recursively.
	 *
	 * @param string $packageName
	 * @param string $subNamespace
	 * @return array
	 */
	public static function resolveClassNamesInPackageSubNamespace($packageName, $subNamespace) {
		$classNames = array();
		$extensionKey = ExtensionNamingUtility::getExtensionKey($packageName);
		$prefix = str_replace('.', '\\', $packageName);
		$suffix = TRUE === empty($subNamespace) ? '' : str_replace('/', '\\', $subNamespace) . '\\';
		$folder = ExtensionManagementUtility::extPath($extensionKey, 'Classes/' . $subNamespace);
		$files = GeneralUtility::getFilesInDir($folder, 'php');
		if (TRUE === is_array($files)) {
			foreach ($files as $file) {
				$classNames[] = $prefix . '\\' . $suffix . pathinfo($file, PATHINFO_FILENAME);
			}
		}
		return $classNames;
	}

	/**
	 * Resolves a list (array) of instances of Form implementations
	 * from the provided package names, or all package names if empty.
	 *
	 * @param array $packageNames
	 * @return Form[]
	 */
	public static function resolveDomainFormClassInstancesFromPackages(array $packageNames = NULL) {
		$packageNames = NULL === $packageNames ? Core::getRegisteredPackagesForAutoForms() : $packageNames;
		$models = (array) Core::getRegisteredFormsForModelObjectClasses();
		foreach ($packageNames as $packageName) {
			$classNames = self::resolveClassNamesInPackageSubNamespace($packageName, 'Domain/Form');
			foreach ($classNames as $formClassName) {
				$fullTableName = self::resolveDatabaseTableName($formClassName);
				$models[$formClassName] = $formClassName::create();
				$models[$formClassName]->setName($fullTableName);
				$models[$formClassName]->setExtensionName($packageName);
			}
		}
		return $models;
	}

	/**
	 * @param string $extensionKey
	 * @param string $action
	 * @param string $controllerObjectShortName
	 * @param boolean $failHardClass
	 * @param boolean $failHardAction
	 * @throws \RuntimeException
	 * @return string|NULL
	 */
	public static function resolveFluxControllerClassNameByExtensionKeyAndAction($extensionKey, $action, $controllerObjectShortName, $failHardClass = FALSE, $failHardAction = FALSE) {
		$potentialControllerClassName = self::buildControllerClassNameFromExtensionKeyAndControllerType($extensionKey, $controllerObjectShortName);
		if (FALSE === class_exists($potentialControllerClassName)) {
			if (TRUE === $failHardClass) {
				throw new \RuntimeException('Class ' . $potentialControllerClassName . ' does not exist. It was build from: ' . var_export($extensionKey, TRUE) .
				' but the resulting class name was not found.', 1364498093);
			}
			return NULL;
		}
		if (FALSE === method_exists($potentialControllerClassName, $action . 'Action')) {
			if (TRUE === $failHardAction) {
				throw new \RuntimeException('Class ' . $potentialControllerClassName . ' does not contain a method named ' . $action . 'Action', 1364498223);
			}
			return NULL;
		}
		return $potentialControllerClassName;
	}

	/**
	 * @param string $pluginSignature
	 * @return string|NULL
	 */
	public static function resolveOverriddenFluxControllerActionNameFromRequestParameters($pluginSignature) {
		$requestParameters = (array) GeneralUtility::_GET($pluginSignature);
		$overriddenControllerActionName = TRUE === isset($requestParameters['action']) ? $requestParameters['action'] : NULL;
		return $overriddenControllerActionName;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public static function convertAllPathSegmentsToUpperCamelCase($path) {
		$pathSegments = explode('/', $path);
		$pathSegments = array_map('ucfirst', $pathSegments);
		$path = implode('/', $pathSegments);
		return $path;
	}

	/**
	 * Resolve the table name for the given class name
	 *
	 * @param string $className
	 * @return string The table name
	 */
	public static function resolveDatabaseTableName($className) {
		$className = ltrim($className, '\\');
		if (strpos($className, '\\') !== FALSE) {
			$classNameParts = explode('\\', $className, 6);
			// Skip vendor and product name for core classes
			if (strpos($className, 'TYPO3\\CMS\\') === 0) {
				$classPartsToSkip = 2;
			} else {
				$classPartsToSkip = 1;
			}
			$tableName = 'tx_' . strtolower(implode('_', array_slice($classNameParts, $classPartsToSkip)));
		} else {
			$tableName = strtolower($className);
		}
		return $tableName;
	}

	/**
	 * @param string $extensionKey
	 * @param string $controllerName
	 * @return boolean|string
	 */
	private static function buildControllerClassNameFromExtensionKeyAndControllerType($extensionKey, $controllerName) {
		if (TRUE === ExtensionNamingUtility::hasVendorName($extensionKey)) {
			list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName($extensionKey);
			$potentialClassName = $vendorName . '\\' . $extensionName . '\\Controller\\' . $controllerName . 'Controller';
		} else {
			$extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
			$potentialClassName = $extensionName . '\\Controller\\' . $controllerName . 'Controller';
			if (FALSE === class_exists($potentialClassName)) {
				$potentialClassName = 'Tx_' . $extensionName . '_Controller_' . $controllerName . 'Controller';
			}
		}
		return $potentialClassName;
	}

}
