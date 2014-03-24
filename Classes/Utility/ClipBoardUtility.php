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

use FluidTYPO3\Flux\Utility\MiscellaneousUtility;

/**
 * ClipBoard Utility
 *
 * @package Flux
 * @subpackage Utility
 */
class ClipBoardUtility {

	/**
	 * @var array
	 */
	private static $cache = NULL;

	/**
	 * @param array $data
	 * @return void
	 */
	public static function setClipBoardData($data) {
		self::$cache = $data;
	}

	/**
	 * @return void
	 */
	public static function clearClipBoardData() {
		self::$cache = NULL;
	}

	/**
	 * @param boolean $reference
	 * @return array|NULL
	 */
	public static function getClipBoardData($reference = FALSE) {
		$reference = (boolean) $reference;
		if (TRUE === is_array(self::$cache)) {
			$clipData = self::$cache;
		} else {
			$clipData = $GLOBALS['BE_USER']->getModuleData('clipboard', $GLOBALS['BE_USER']->getTSConfigVal('options.saveClipboard') ? '' : 'ses');
		}
		$mode = TRUE === isset($clipData['current']) ? $clipData['current'] : 'normal';
		$hasClip = TRUE === isset($clipData[$mode]['el']) && 0 < count($clipData[$mode]['el']);
		if (FALSE === $hasClip) {
			return NULL;
		}
		if (FALSE === isset($clipData[$mode]['mode']) && TRUE === $reference) {
			return NULL;
		}
		return $clipData;
	}

	/**
	 * @param string $relativeTo
	 * @param boolean $reference
	 * @return string
	 */
	public static function createIconWithUrl($relativeTo, $reference = FALSE) {
		$data = self::getClipBoardData($reference);
		if (NULL === $data) {
			return '';
		}
		$reference = (boolean) $reference;
		$clipBoard = new \TYPO3\CMS\Backend\Clipboard\Clipboard();
		if (TRUE === $reference) {
			$label = 'Paste as reference in this position';
			$icon = 'actions-insert-reference';
		} else {
			$label = 'Paste in this position';
			$icon = 'actions-document-paste-after';
		}
		$icon = MiscellaneousUtility::getIcon($icon, $label);
		$uri = "javascript:top.content.list_frame.location.href=top.TS.PATH_typo3+'";
		$uri .= $clipBoard->pasteUrl('tt_content', $relativeTo);
		$uri .= "';";
		return MiscellaneousUtility::wrapLink($icon, $uri);
	}

}
