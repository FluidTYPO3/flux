<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * ************************************************************* */

/**
 * Hook class utilized to select tt_content (and other) records. Patched to
 * prevent content elements nested in FCEs to appear twice.
 *
 * @package Fed
 * @subpackage Backend
 */
class Tx_Flux_Backend_MakeQueryArray {

	/**
	 * Hook methods: This method is called when querying for tt_content records
	 *
	 * @param string $queryParts
	 * @param mixed $reference
	 * @param string $table
	 * @param integer $id
	 * @param string $addWhere
	 * @param array $fieldList
	 * @param array $_params
	 */
	public function makeQueryArray_post(&$queryParts, &$reference, $table, $id, &$addWhere, &$fieldList, &$_params) {
		if (get_class($reference) === 'tx_cms_layout' && $table === 'tt_content') {
			$queryParts['WHERE'] .= " AND tt_content.tx_flux_column = ''";
		}

	}

}
?>