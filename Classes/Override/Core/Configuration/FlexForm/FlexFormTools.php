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
 * FlexFormTools Override
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Override\Core\Configuration\FlexForm
 */
class Tx_Flux_Override_Core_Configuration_Flexform_FlexFormTools extends \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools {

	/**
	 * Handler for Flex Forms
	 *
	 * @param string $table The table name of the record
	 * @param string $field The field name of the flexform field to work on
	 * @param array $row The record data array
	 * @param object $callBackObj Object (passed by reference) in which the call back function is located
	 * @param string $callBackMethod_value Method name of call back function in object for values
	 * @return boolean If TRUE, error happened (error string returned)
	 * @todo Define visibility
	 */
	public function traverseFlexFormXMLData($table, $field, $row, $callBackObj, $callBackMethod_value) {
		if (!is_array($GLOBALS['TCA'][$table]) || !is_array($GLOBALS['TCA'][$table]['columns'][$field])) {
			return 'TCA table/field was not defined.';
		}
		$this->callBackObj = $callBackObj;
		// Get Data Structure:
		$dataStructArray = \TYPO3\CMS\Backend\Utility\BackendUtility::getFlexFormDS($GLOBALS['TCA'][$table]['columns'][$field]['config'], $row, $table, $field);
		// If data structure was ok, proceed:
		if (is_array($dataStructArray)) {
			// Get flexform XML data:
			$xmlData = $row[$field];
			// Convert charset:
			if ($this->convertCharset) {
				$xmlHeaderAttributes = \TYPO3\CMS\Core\Utility\GeneralUtility::xmlGetHeaderAttribs($xmlData);
				$storeInCharset = strtolower($xmlHeaderAttributes['encoding']);
				if ($storeInCharset) {
					$currentCharset = $GLOBALS['LANG']->charSet;
					$xmlData = $GLOBALS['LANG']->csConvObj->conv($xmlData, $storeInCharset, $currentCharset, 1);
				}
			}
			$editData = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($xmlData);
			if (!is_array($editData)) {
				return 'Parsing error: ' . $editData;
			}
			// Language settings:
			$langChildren = $dataStructArray['meta']['langChildren'] ? 1 : 0;
			$langDisabled = $dataStructArray['meta']['langDisable'] ? 1 : 0;
			// Empty or invalid <meta>
			if (!is_array($editData['meta'])) {
				$editData['meta'] = array();
			}
			$editData['meta']['currentLangId'] = array();
			$languages = $this->getAvailableLanguages();
			foreach ($languages as $lInfo) {
				$editData['meta']['currentLangId'][] = $lInfo['ISOcode'];
			}
			if (!count($editData['meta']['currentLangId'])) {
				$editData['meta']['currentLangId'] = array('DEF');
			}
			$editData['meta']['currentLangId'] = array_unique($editData['meta']['currentLangId']);
			if ($langChildren || $langDisabled) {
				$lKeys = array('DEF');
			} else {
				$lKeys = $editData['meta']['currentLangId'];
			}
			// Tabs sheets
			if (is_array($dataStructArray['sheets'])) {
				$sKeys = array_keys($dataStructArray['sheets']);
			} else {
				$sKeys = array('sDEF');
			}
			// Traverse languages:
			foreach ($lKeys as $lKey) {
				foreach ($sKeys as $sheet) {
					$sheetCfg = $dataStructArray['sheets'][$sheet];
					list($dataStruct, $sheet) = \TYPO3\CMS\Core\Utility\GeneralUtility::resolveSheetDefInDS($dataStructArray, $sheet);
					// Render sheet:
					if (is_array($dataStruct['ROOT']) && is_array($dataStruct['ROOT']['el'])) {
						// Separate language key
						$lang = 'l' . $lKey;
						$PA['vKeys'] = $langChildren && !$langDisabled ? $editData['meta']['currentLangId'] : array('DEF');
						$PA['lKey'] = $lang;
						$PA['callBackMethod_value'] = $callBackMethod_value;
						$PA['table'] = $table;
						$PA['field'] = $field;
						$PA['uid'] = $row['uid'];
						$this->traverseFlexFormXMLData_DS = &$dataStruct;
						$this->traverseFlexFormXMLData_Data = &$editData;
						// Render flexform:
						$this->traverseFlexFormXMLData_recurse($dataStruct['ROOT']['el'], $editData['data'][$sheet][$lang], $PA, 'data/' . $sheet . '/' . $lang);
					} else {
						return 'Data Structure ERROR: No ROOT element found for sheet "' . $sheet . '".';
					}
				}
			}
		} else {
			return 'Data Structure ERROR: ' . $dataStructArray;
		}
	}

}