<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\View\TemplatePaths;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 * @package Flux
 * @subpackage Utility
 */
class PathUtility {

	/**
	 * @var array
	 */
	private static $knownPathNames = array(
		TemplatePaths::CONFIG_OVERLAYS,
		TemplatePaths::CONFIG_TEMPLATEROOTPATH,
		TemplatePaths::CONFIG_TEMPLATEROOTPATHS,
		TemplatePaths::CONFIG_LAYOUTROOTPATH,
		TemplatePaths::CONFIG_LAYOUTROOTPATHS,
		TemplatePaths::CONFIG_PARTIALROOTPATH,
		TemplatePaths::CONFIG_PARTIALROOTPATHS
	);

	/**
	 * Translates an array of paths or single path into absolute paths/path
	 *
	 * @param mixed $path
	 * @return mixed
	 */
	public static function translatePath($path) {
		if (is_string($path)) {
			$path = GeneralUtility::isAbsPath($path) ? $path : GeneralUtility::getFileAbsFileName($path);
			if (is_dir($path)) {
				$path = realpath($path) . '/';
			}
			$path = GeneralUtility::fixWindowsFilePath($path);
		} elseif (is_array($path)) {
			$templatePaths = self::getTemplatePathFields($path);
			foreach ($templatePaths as $field => $subpath) {
				if (is_array($subpath)) {
					$path[$field] = array_map(array(__CLASS__, __METHOD__), $subpath);
				} else {
					$path[$field] = self::translatePath($subpath);
				}
			}
		}
		return $path;
	}

	/**
	 * Get fields from view configuration that are template paths
	 *
	 * @param array $viewConfiguration
	 * @return array
	 */
	public static function getTemplatePathFields(array $viewConfiguration) {
		$knownPathKeys = array_fill_keys(self::$knownPathNames, NULL);
		return array_intersect_key($viewConfiguration, $knownPathKeys);
	}
}
