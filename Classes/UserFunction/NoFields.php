<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * Renders nothing in case no fields are defined, label is used for feedback
 *
 * @package	Flux
 * @subpackage UserFunction
 */
class Tx_Flux_UserFunction_NoFields {

	/**
	 * @param array $parameters Not used
	 * @param object $pObj Not used
	 * @return string
	 */
	public function renderField(&$parameters, &$pObj) {
		unset($pObj, $parameters);
		return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('user.no_fields', 'Flux');
	}
}
