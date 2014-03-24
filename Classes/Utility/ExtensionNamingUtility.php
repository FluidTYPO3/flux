<?php
namespace FluidTYPO3\Flux\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extension Utility
 *
 * @author Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
 * @package Flux
 * @subpackage Utility
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
		list($vendorName, $extensionKey) = self::getVendorNameAndExtensionKey($qualifiedExtensionName);
		return $vendorName;
	}

	/**
	 * @param string $qualifiedExtensionName
	 * @return string
	 */
	public static function getExtensionKey($qualifiedExtensionName) {
		list($vendorName, $extensionKey) = self::getVendorNameAndExtensionKey($qualifiedExtensionName);
		return $extensionKey;
	}

	/**
	 * @param string $qualifiedExtensionName
	 * @return string
	 */
	public static function getExtensionName($qualifiedExtensionName) {
		list($vendorName, $extensionName) = self::getVendorNameAndExtensionName($qualifiedExtensionName);
		return $extensionName;
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
