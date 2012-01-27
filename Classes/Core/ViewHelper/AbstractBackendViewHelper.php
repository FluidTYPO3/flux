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
 * Backend ViewHelper base class
 *
 * @package Flux
 * @subpackage Core\ViewHelper
 */
abstract class Tx_Flux_Core_ViewHelper_AbstractBackendViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Initialize
	 */
	public function initializeArguments() {
		$this->registerArgument('row', 'array', 'Record row', TRUE);
		$this->registerArgument('area', 'string', 'If placed inside Fluid FCE, use this to indicate which area to insert into');
	}

	/**
	 * @param string $icon
	 * @param string $title
	 * @return string
	 */
	protected function getIcon($icon, $title=NULL) {
		return t3lib_iconWorks::getSpriteIcon($icon, array('title' => $title));
	}

	/**
	 * @param string $inner
	 * @param string $href
	 * @return string
	 */
	protected function wrapLink($inner, $uri) {
		return '<a href="' . $uri . '">' . $inner . '</a>' . LF;
	}

	/**
	 * @return string
	 */
	protected function getFormToken() {
		$token = '1b5927464308daff2e18a2fab5b2e5a2c6f53565';
		return $token;
	}

	/**
	 * @return string
	 */
	protected function getLinkChecksum() {
		return '7a7abb3c9d';
	}

	/**
	 * @return string
	 */
	protected function getReturnUri($pid) {
		return '%2Ftypo3%2Fsysext%2Fcms%2Flayout%2Fdb_layout.php%3Fid%3D' . $pid;
	}

}
?>