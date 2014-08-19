<?php
namespace FluidTYPO3\Flux\ViewHelpers\Be;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @package Flux
 * @subpackage ViewHelpers\Be
 */
class ContentElementViewHelper extends AbstractViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('row', 'array', 'Record row', TRUE);
		$this->registerArgument('area', 'string', 'If placed inside Fluid FCE, use this to indicate which area to insert into');
		$this->registerArgument('dblist', 'TYPO3\CMS\Backend\View\PageLayoutView', 'Instance of PageLayoutView preconfigured to render each record', TRUE);
	}

	/**
	 * @return string
	 */
	public function render() {
		$dblist = $this->arguments['dblist'];
		$record = $this->arguments['row'];
		if (!$dblist->tt_contentConfig['single']) {
				// MULTIPLE column display mode, side by side:
			$rendered = $dblist->tt_content_drawHeader(
				$record,
				$dblist->tt_contentConfig['showInfo'] ? 15 : 5,
				FALSE,
				TRUE,
				!$dblist->tt_contentConfig['languageMode']
			);
		} else {
				// SINGLE column mode (columns shown beneath each other):
			$rendered = $dblist->tt_content_drawHeader($record);
		}
		$rendered .= '<div class="t3-page-ce-body-inner">' . $dblist->tt_content_drawItem($record) . '</div>';
		$rendered .= '</div>';
		return $rendered;
	}
}
