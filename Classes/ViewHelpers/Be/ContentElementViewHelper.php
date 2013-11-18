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
 * @subpackage ViewHelpers\Be
 */
class Tx_Flux_ViewHelpers_Be_ContentElementViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('row', 'array', 'Record row', TRUE);
		$this->registerArgument('area', 'string', 'If placed inside Fluid FCE, use this to indicate which area to insert into');
	}

	/**
	 * @param mixed $dblist
	 * @return string
	 */
	public function render($dblist) {
		$record = $this->arguments['row'];
		$rendered = $dblist->tt_content_drawHeader($record);
		$rendered .= '<div class="t3-page-ce-body-inner">' . $dblist->tt_content_drawItem($record) . '</div>';
		$rendered .= '</div>';
		return $rendered;
	}

}
