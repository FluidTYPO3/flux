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
 * DataHandler Override
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Override\Core\DataHandling
 */
class Tx_Flux_Override_Core_DataHandling_DataHandler extends \TYPO3\CMS\Core\DataHandling\DataHandler {

	/**
	 * Evaluates 'flex' type values.
	 *
	 * @param array $res The result array. The processed value (if any!) is set in the 'value' key.
	 * @param string $value The value to set.
	 * @param array $tcaFieldConf Field configuration from TCA
	 * @param array $PP Additional parameters in a numeric array: $table,$id,$curValue,$status,$realPid,$recFID
	 * @param array $uploadedFiles Uploaded files for the field
	 * @param string $field Field name
	 * @return array Modified $res array
	 * @todo Define visibility
	 */
	public function checkValue_flex($res, $value, $tcaFieldConf, $PP, $uploadedFiles, $field) {
		list($table, $id, $curValue, $status, $realPid, $recFID) = $PP;

		if (is_array($value)) {
			// This value is necessary for flex form processing to happen on flexform fields in page records when they are copied.
			// The problem is, that when copying a page, flexfrom XML comes along in the array for the new record - but since $this->checkValue_currentRecord does not have a uid or pid for that sake, the t3lib_BEfunc::getFlexFormDS() function returns no good DS. For new records we do know the expected PID so therefore we send that with this special parameter. Only active when larger than zero.
			$newRecordPidValue = $status == 'new' ? $realPid : 0;
			// Get current value array:
			$dataStructArray = \TYPO3\CMS\Backend\Utility\BackendUtility::getFlexFormDS($tcaFieldConf, $this->checkValue_currentRecord, $table, $field, TRUE, $newRecordPidValue);
			$currentValueArray = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($curValue);
			if (!is_array($currentValueArray)) {
				$currentValueArray = array();
			}
			if (is_array($currentValueArray['meta']['currentLangId'])) {
				unset($currentValueArray['meta']['currentLangId']);
			}
			// Remove all old meta for languages...
			// Evaluation of input values:
			$value['data'] = $this->checkValue_flex_procInData($value['data'], $currentValueArray['data'], $uploadedFiles['data'], $dataStructArray, $PP);
			// Create XML and convert charsets from input value:
			$xmlValue = $this->checkValue_flexArray2Xml($value, TRUE);
			// If we wanted to set UTF fixed:
			// $storeInCharset='utf-8';
			// $currentCharset=$GLOBALS['LANG']->charSet;
			// $xmlValue = $GLOBALS['LANG']->csConvObj->conv($xmlValue,$currentCharset,$storeInCharset,1);
			$storeInCharset = $GLOBALS['LANG']->charSet;
			// Merge them together IF they are both arrays:
			// Here we convert the currently submitted values BACK to an array, then merge the two and then BACK to XML again. This is needed to ensure the charsets are the same (provided that the current value was already stored IN the charset that the new value is converted to).
			if (is_array($currentValueArray)) {
				$arrValue = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($xmlValue);
				$arrValue = \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule($currentValueArray, $arrValue);
				$xmlValue = $this->checkValue_flexArray2Xml($arrValue, TRUE);
			}
			// Action commands (sorting order and removals of elements)
			$actionCMDs = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('_ACTION_FLEX_FORMdata');
			if (is_array($actionCMDs[$table][$id][$field]['data'])) {
				$arrValue = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($xmlValue);
				$this->_ACTION_FLEX_FORMdata($arrValue['data'], $actionCMDs[$table][$id][$field]['data']);
				$xmlValue = $this->checkValue_flexArray2Xml($arrValue, TRUE);
			}
			// Create the value XML:
			$res['value'] = '';
			$res['value'] .= $xmlValue;
		} else {
			// Passthrough...:
			$res['value'] = $value;
		}

		return $res;
	}

}