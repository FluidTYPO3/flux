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
 *****************************************************************/

/**
 * Fetches a single variable from the template variables
 *
 * @package Flux
 * @subpackage ViewHelpers
 */
class Tx_Flux_ViewHelpers_VariableViewHelper extends Tx_Flux_ViewHelpers_AbstractFlexformViewHelper {

	/**
	 * @param string $name
	 * @return string
	 */
	public function render($name) {
		if (strpos($name, '.') === FALSE) {
			return $this->templateVariableContainer->get($name);
		} else {
			$parts = explode('.', $name);
			return \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($this->templateVariableContainer->get(array_shift($parts)), implode('.', $parts));
		}
	}

}
