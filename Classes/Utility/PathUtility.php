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
		if (is_array($path) == FALSE) {
			return 0 === strpos($path, '/') ? $path : GeneralUtility::getFileAbsFileName($path);
		} else {
			foreach ($path as $key => $subPath) {
				if (TRUE === in_array($key, self::$knownPathNames)) {
					$path[$key] = self::translatePath($subPath);
				}
			}
		}
		return $path;
	}
}
