<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * Content / NewViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers\Be\Uri\Content
 */
class Tx_Flux_ViewHelpers_Be_Link_Content_PasteViewHelper extends Tx_Flux_Core_ViewHelper_AbstractBackendViewHelper {

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('reference', 'boolean', 'If TRUE, pastes as reference', FALSE, FALSE);
		$this->registerArgument('relativeTo', 'array', 'If filled with an array, assumes clicable icon is placed below this content record', FALSE, array());
	}

	/**
	 * Render uri
	 *
	 * @return string
	 */
	public function render() {
		$data = $this->getClipBoardData();
		if (NULL === $data) {
			return '';
		}
		return $this->createIconWithUrl();
	}

	/**
	 * @return string
	 */
	protected function createIconWithUrl() {
		$reference = (boolean) $this->arguments['reference'];
		$clipBoard = new t3lib_clipboard();
		if (TRUE === $reference) {
			$label = 'Paste as reference in this position';
			$icon = 'actions-insert-reference';
		} else {
			$label = 'Paste in this position';
			$icon = 'actions-document-paste-after';
		}
		$relativeTo = $this->getRelativeToValue();
		$icon = $this->getIcon($icon, $label);
		$uri = "javascript:top.content.list_frame.location.href=top.TS.PATH_typo3+'";
		$uri .= $clipBoard->pasteUrl('tt_content', $relativeTo);
		$uri .= "';";
		return $this->wrapLink($icon, $uri);
	}

	/**
	 * @return string
	 */
	protected function getRelativeToValue() {
		$reference = (boolean) $this->arguments['reference'];
		if (TRUE === $reference) {
			$command = 'reference';
		} else {
			$command = 'paste';
		}
		$row = $this->arguments['row'];
		$area = $this->arguments['area'];
		$pid = $row['pid'];
		$uid = $row['uid'];
		$relativeUid = TRUE === isset($this->arguments['relativeTo']['uid']) ? $this->arguments['relativeTo']['uid'] : 0;
		$relativeTo = $pid . '-' . $command . '-' . $relativeUid . '-' . $uid;
		if (FALSE === empty($area)) {
			$relativeTo .= '-' . $area;
		}
		return $relativeTo;
	}

	/**
	 * @return array|NULL
	 */
	protected function getClipBoardData() {
		$reference = (boolean) $this->arguments['reference'];
		$clipData = $GLOBALS['BE_USER']->getModuleData('clipboard', $GLOBALS['BE_USER']->getTSConfigVal('options.saveClipboard') ? '' : 'ses');
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

}
