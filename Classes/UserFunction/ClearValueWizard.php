<?php
namespace FluidTYPO3\Flux\UserFunction;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Renders a checkbox which, when checked, clears a flexform field value.
 *
 * @package	Flux
 * @subpackage UserFunction
 */
class ClearValueWizard {

	/**
	 * @param array $parameters
	 * @param object $pObj Not used
	 * @return string
	 */
	public function renderField(&$parameters, &$pObj) {
		$nameSegments = explode('][', $parameters['itemName']);
		$nameSegments[6] .= '_clear';
		$fieldName = implode('][', $nameSegments);
		$html = '<label style="opacity: 0.65; padding-left: 2em"><input type="checkbox" name="' . $fieldName . '_clear" ';
		$html .= ' value="1" /> ' . LocalizationUtility::translate('flux.clearValue', 'Flux') . '</label>';
		return $html;
	}
}
