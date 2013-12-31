<?php
namespace FluidTYPO3\Flux\ViewHelpers;
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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * ### Condition: Are content areas of this content reord collapsed?
 *
 * A condition ViewHelper which renders the `then` child if
 * the current content element's content areas are collapsed.
 *
 * @package Flux
 * @subpackage ViewHelpers
 */
class IsCollapsedViewHelper extends AbstractConditionViewHelper {

	/**
	 * Render method
	 *
	 * @param array $record
	 * @return string
	 */
	public function render($record) {
		$cookie = array();
		if (TRUE === isset($_COOKIE['fluxCollapseStates'])) {
			$cookie = $_COOKIE['fluxCollapseStates'];
			$cookie = urldecode($cookie);
			$cookie = json_decode($cookie);
		}
		return TRUE === in_array($record['uid'], $cookie) ? $this->renderThenChild() : $this->renderElseChild();
	}

}
