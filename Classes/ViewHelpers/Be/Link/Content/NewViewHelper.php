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
 * @package Flux
 * @subpackage ViewHelpers\Be\Uri\Content
 */
class Tx_Flux_ViewHelpers_Be_Link_Content_NewViewHelper extends Tx_Flux_Core_ViewHelper_AbstractBackendViewHelper {

	/**
	 * Render uri
	 *
	 * @return string
	 */
	public function render() {
		$pid = $this->arguments['row']['pid'];
		$uid = $this->arguments['row']['uid'];
		$area = $this->arguments['area'];
		$sysLang = $this->arguments['row']['sys_language_uid'];
		$colPos = $this->arguments['row']['colPos'];
		$returnUri = $this->getReturnUri($pid);
		if ($area) {
			$returnUri .= '%23' . $area . '%3A' . $uid;
		}
		$sign = $after ? '-' : '';
		$icon = $this->getIcon('actions-document-new', 'Insert new content element in this position');
		$uri = 'db_new_content_el.php?id=' . $pid
			. '&returnUrl=' . $returnUri
			. '&uid_pid=' . $sign . $pid
			. '&colPos=' . $colPos
			. '&sys_language_uid=0'
			;
		return $this->wrapLink($icon, $uri);
	}
}

?>