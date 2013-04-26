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
 * Flux Main Controller
 *
 * @package Flux
 * @subpackage Controller
 * @route off
 */
class Tx_Flux_Controller_FluxController extends Tx_Flux_Controller_AbstractFluxController {

	/**
	 * Renders child content from $record's $area
	 *
	 * @param integer $localizedUid The UID (localized through _LOCALIZED_UID substitution if localization is wanted) of the parent element
	 * @param string $area The area of the parent element from which to render child content
	 * @param integer $limit
	 * @param string $order
	 * @param string $sortDirection
	 * @return string
	 */
	public function renderChildContentAction($localizedUid, $area, $limit=99999, $order='sorting', $sortDirection='ASC') {
		$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', "uid = '" . $localizedUid . "'"));
		$id = $record['uid'];
		$localizedUid = $record['_LOCALIZED_UID'] > 0 ? $record['_LOCALIZED_UID'] : $id;
		return $this->configurationService->renderChildContent($localizedUid, $area, $limit, $order, $sortDirection);
	}

}
