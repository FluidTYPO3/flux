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
 * Backend Preview renderer - TemplaVoila Proxy
 *
 * Detects wether we should render a preview for this
 *
 * @package Flux
 * @subpackage Backend
 */

class Tx_Flux_Backend_TemplaVoilaPreview extends tx_templavoila_preview_default {

	/**
	 * Render an FCE preview for TemplaVoila BE module
	 *
	 * @param array $row
	 * @param string $table
	 * @param string $output
	 * @param boolean $alreadyRendered
	 * @param object $ref
	 * @return string
	 */
	public function render_previewContent ($row, $table, $output, $alreadyRendered, &$ref) {
		$headerContent = '';
		$itemContent = '';
		$realPreviewer = t3lib_div::makeInstance('Tx_Flux_Backend_Preview');
		$realPreviewer->renderPreview($headerContent, $itemContent, $row);
		return $itemContent;
	}
}

?>
