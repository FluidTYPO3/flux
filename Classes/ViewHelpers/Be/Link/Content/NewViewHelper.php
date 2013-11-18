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
 * Content / NewViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers\Be\Uri\Content
 */
class Tx_Flux_ViewHelpers_Be_Link_Content_NewViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('row', 'array', 'Record row', TRUE);
		$this->registerArgument('area', 'string', 'If placed inside Fluid FCE, use this to indicate which area to insert into');
	}

	/**
	 * Render uri
	 *
	 * @param integer $after
	 * @return string
	 */
	public function render($after = 0) {
		$pid = $this->arguments['row']['pid'];
		$uid = $this->arguments['row']['uid'];
		$area = $this->arguments['area'];
		$sysLang = $this->arguments['row']['sys_language_uid'];
		$returnUri = urlencode($_SERVER['REQUEST_URI']);
		if ($area) {
			$returnUri .= '%23' . $area . '%3A' . $uid;
			if (0 < $after) {
				$returnUri .= '%3A-' . $after;
			}
		}
		$icon = Tx_Flux_Utility_Miscellaneous::getIcon('actions-document-new', 'Insert new content element in this position');
		$uri = 'db_new_content_el.php?id=' . $pid .
			'&uid_pid=' . $pid .
			'&colPos=-42' .
			'&sys_language_uid=' . $sysLang .
			'&defVals[tt_content][tx_flux_parent]=' . $uid .
			'&defVals[tt_content][tx_flux_column]=' . $area .
			'&returnUrl=' . $returnUri;
		return Tx_Flux_Utility_Miscellaneous::wrapLink($icon, htmlspecialchars($uri));
	}

}
