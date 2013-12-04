<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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

/**
 * Autoload handler. Replacement for static ext_autoload.php,
 * capable of
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Utility
 */
class Tx_Flux_Utility_Autoload {

	/**
	 * Manually clear autoload registry for $extensionName
	 *
	 * If for some reason manual cache clearing fails to
	 * remove files this can be used to force rebuilding.
	 *
	 * @static
	 * @param string $extensionName
	 * @return void
	 */
	public static function resetAutoloadingForExtension($extensionName) {
		$manifestPathAndFilename = PATH_site . 'typo3temp/' . $extensionName . '-manifest.cache';
		if (file_exists($manifestPathAndFilename) === TRUE) {
			unlink($manifestPathAndFilename);
		}
			// Force immediate rebuild, return value unused.
		self::getAutoloadRegistryForExtension($extensionName);
	}

	/**
	 * Gets an autoload registry array for $extensionName
	 *
	 * @static
	 * @param string $extensionName
	 * @return array
	 */
	public static function getAutoloadRegistryForExtension($extensionName) {
		$extensionKey = \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($extensionName);
		$manifestPathAndFilename = PATH_site . 'typo3temp/' . $extensionName . '-manifest.cache';
		if (file_exists($manifestPathAndFilename) === TRUE) {
			return unserialize(file_get_contents($manifestPathAndFilename));
		}
		$classPrefix = 'Tx_' . $extensionName . '_';
		$classPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey, 'Classes/');
		$files = \TYPO3\CMS\Core\Utility\GeneralUtility::getAllFilesAndFoldersInPath(array(), $classPath);
		$autoloadRegistry = array();
		foreach ($files as $filename) {
			$relativeName = substr($filename, strlen($classPath));
			$relativeName = substr($relativeName, 0, -4);
			$className = $classPrefix . str_replace('/', '_', $relativeName);
			$key = strtolower($className);
			$autoloadRegistry[$key] = $filename;
		}
		$encoded = serialize($autoloadRegistry);
		\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($manifestPathAndFilename, $encoded);
		return $autoloadRegistry;
	}

}