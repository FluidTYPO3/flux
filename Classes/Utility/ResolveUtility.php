<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ResolveUtility utility
 *
 * @package Flux
 * @subpackage Utility
 */
class ResolveUtility {

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
