<?php
namespace FluidTYPO3\Flux\Utility;
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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * VersionUtility utilities
 *
 * @package Flux
 * @subpackage Utility
 */
class VersionUtility {

	/**
	 * @return boolean
	 */
	public static function assertCoreVersionIsBelowSixPointZero() {
		$version = explode('.', TYPO3_version);
		return ($version[0] < 6);
	}

	/**
	 * @return boolean
	 */
	public static function assertCoreVersionIsAtLeastSixPointZero() {
		$version = explode('.', TYPO3_version);
		return ($version[0] >= 6);
	}

	/**
	 * @param string $extensionKey
	 * @param integer $majorVersion
	 * @param integer $minorVersion
	 * @param integer $bugfixVersion
	 * @return boolean
	 */
	public static function assertExtensionVersionIsAtLeastVersion($extensionKey, $majorVersion, $minorVersion = 0, $bugfixVersion = 0) {
		if (FALSE === ExtensionManagementUtility::isLoaded($extensionKey)) {
			return FALSE;
		}
		$extensionVersion = ExtensionManagementUtility::getExtensionVersion($extensionKey);
		list ($major, $minor, $bugfix) = explode('.', $extensionVersion);
		return ($majorVersion <= $major && $minorVersion <= $minor && $bugfixVersion <= $bugfix);
	}

}
