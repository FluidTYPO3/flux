<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Björn Fromme <fromme@dreipunktnull.com>, dreipunktnull
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
 * Converts raw flexform xml into an associative array
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Flexform_DataViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var array
	 */
	private static $dataCache = array();

	/**
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexFormService;

	/**
	 * Inject FlexForm service
	 * @param Tx_Flux_Service_FlexForm $flexFormService
	 * @return void
	 */
	public function injectFlexFormService(Tx_Flux_Service_FlexForm $flexformService) {
		$this->flexFormService = $flexformService;
	}

	/**
	 * Render method
	 * @param int $uid
	 * @param string $table
	 * @param string $field
	 * @return array
	 */
	public function render($uid = NULL, $table = 'pages', $field = 'tx_fed_page_flexform') {

		if (NULL === $uid) {
			$uid = $GLOBALS["TSFE"]->id;
		}

		if (TRUE === isset(self::$dataCache[$uid.$table.$field])) {
		    return self::$dataCache[$uid.$table.$field];
		}

		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow($field, $table, sprintf('uid=%d', $uid));

		if (NULL === $row) {
			$dataArray = array();
		} else {
			$dataArray = $this->flexFormService->convertFlexFormContentToArray($row[$field]);
		}

		self::$dataCache[$uid.$table.$field] = $dataArray;

		return $dataArray;
	}
}