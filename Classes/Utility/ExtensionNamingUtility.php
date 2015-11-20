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
 * Extension Utility
 */
class ExtensionNamingUtility {

	/**
	 * @param string $qualifiedExtensionName
	 * @return boolean
	 */
	public static function hasVendorName($qualifiedExtensionName) {
		return FALSE !== strpos($qualifiedExtensionName, '.');
	}

	/**
	 * @param string $qualifiedExtensionName
	 * @return string
	 */
	public static function getVendorName($qualifiedExtensionName) {
		list($vendorName, ) = self::getVendorNameAndExtensionKey($qualifiedExtensionName);
		return $vendorName;
	}

	/**
	 * @param string $qualifiedExtensionName
	 * @return string
	 */
	public static function getExtensionKey($qualifiedExtensionName) {
		list(, $extensionKey) = self::getVendorNameAndExtensionKey($qualifiedExtensionName);
		return $extensionKey;
	}

	/**
	 * @param string $qualifiedExtensionName
	 * @return string
	 */
	public static function getExtensionName($qualifiedExtensionName) {
		list(, $extensionName) = self::getVendorNameAndExtensionName($qualifiedExtensionName);
		return $extensionName;
	}

	/**
	 * @param string $qualifiedExtensionName
	 * @return string
	 */
	public static function getExtensionSignature($qualifiedExtensionName) {
		$extensionKey = self::getExtensionKey($qualifiedExtensionName);
		return str_replace('_', '', $extensionKey);
	}

	/**
	 * @param string $qualifiedExtensionName
	 * @return array
	 */
	public static function getVendorNameAndExtensionKey($qualifiedExtensionName) {
		if (TRUE === self::hasVendorName($qualifiedExtensionName)) {
			list($vendorName, $extensionKey) = GeneralUtility::trimExplode('.', $qualifiedExtensionName);
		} else {
			$vendorName = NULL;
			$extensionKey = $qualifiedExtensionName;
		}
		$extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($extensionKey);
		return array($vendorName, $extensionKey);
	}

	/**
	 * @param string $qualifiedExtensionName
	 * @return array
	 */
	public static function getVendorNameAndExtensionName($qualifiedExtensionName) {
		if (TRUE === self::hasVendorName($qualifiedExtensionName)) {
			list($vendorName, $extensionName) = GeneralUtility::trimExplode('.', $qualifiedExtensionName);
		} else {
			$vendorName = NULL;
			$extensionName = $qualifiedExtensionName;
		}
		if (FALSE !== strpos($extensionName, '_')) {
			$extensionName = GeneralUtility::underscoredToUpperCamelCase($extensionName);
		} else {
			$extensionName = ucfirst($extensionName);
		}
		return array($vendorName, $extensionName);
	}

}
