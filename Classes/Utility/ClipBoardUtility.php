<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Clipboard\Clipboard;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ClipBoard Utility
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
			$clipData = self::getBackendUser()->getModuleData('clipboard', self::getBackendUser()->getTSConfigVal('options.saveClipboard') ? '' : 'ses');
		}
		$mode = TRUE === isset($clipData['current']) ? $clipData['current'] : 'normal';
		$hasClip = TRUE === isset($clipData[$mode]['el']) && 0 < count($clipData[$mode]['el']);
		if (FALSE === $hasClip || (FALSE === isset($clipData[$mode]['mode']) && TRUE === $reference)) {
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
		$reference = (boolean) $reference;
		$data = self::getClipBoardData($reference);
		if (NULL === $data) {
			return '';
		}

		if (TRUE === $reference) {
			$icon = MiscellaneousUtility::getIcon('actions-insert-reference');
			$title = LocalizationUtility::translate('paste_reference', 'Flux');
		} else {
			$icon = MiscellaneousUtility::getIcon('actions-document-paste-after');
			$title = LocalizationUtility::translate('paste', 'Flux');
		}

		$clipBoard = new Clipboard();
		$clipBoard->initializeClipboard();
		$uri = $clipBoard->pasteUrl('tt_content', $relativeTo);

		return MiscellaneousUtility::wrapLink($icon, $uri, $title);
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected static function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

}
