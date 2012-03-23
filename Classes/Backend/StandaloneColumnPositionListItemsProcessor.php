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
*  the Free Software Foundation; either version 3 of the License, or
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
 * Column position (colPos) list item processing function for tt_content records
 *
 * @package Flux
 * @subpackage Backend
 */
class Tx_Flux_Backend_StandaloneColumnPositionListItemsProcessor {

	/**
	 * ItemsProcFunc - adds items to tt_content.colPos selector
	 *
	 * @param array $params
	 */
	public function itemsProcFunc(&$params) {
		$this->addFluxContentElmementAreas($params);
		$params['items'][] = array(
			$GLOBALS['LANG']->sL('LLL:EXT:flux/Resources/Private/Language/locallang_db.xml:tt_content.tx_flux_container'),
			'-42'
		);
	}

	/**
	 * Adds all content element areas from defined Flux content areas to the colPos selector's items
	 *
	 * @param array $params
	 */
	protected function addFluxContentElmementAreas(&$params) {

	}

}
?>