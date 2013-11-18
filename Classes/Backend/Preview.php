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
 * Fluid Template preview renderer
 *
 * @package Flux
 * @subpackage Backend
 */
class Tx_Flux_Backend_Preview extends Tx_Flux_Backend_AbstractPreview {

	/**
	 *
	 * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject
	 * @param boolean $drawItem
	 * @param string $headerContent
	 * @param string $itemContent
	 * @param array $row
	 * @return void
	 */
	public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
		$this->renderPreview($headerContent, $itemContent, $row, $drawItem);
		unset($parentObject);
	}

}
