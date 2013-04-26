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
 * FormEngine Override
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Override\Backend\Form
 */
class Tx_Flux_Override_Backend_Form_FormEngine extends \TYPO3\CMS\Backend\Form\FormEngine {

	/**
	 * Handler for Flex Forms
	 *
	 * @param string $table The table name of the record
	 * @param string $field The field name which this element is supposed to edit
	 * @param array $row The record data array where the value(s) for the field can be found
	 * @param array $PA An array with additional configuration options.
	 * @return string The HTML code for the TCEform field
	 * @todo Define visibility
	 */
	public function getSingleField_typeFlex($table, $field, $row, &$PA) {
		// Data Structure:
		$dataStructArray = \TYPO3\CMS\Backend\Utility\BackendUtility::getFlexFormDS($PA['fieldConf']['config'], $row, $table, $field);
		$item = '';
		// Manipulate Flexform DS via TSConfig and group access lists
		if (is_array($dataStructArray)) {
			$flexFormHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Form\\FlexFormsHelper');
			$dataStructArray = $flexFormHelper->modifyFlexFormDS($dataStructArray, $table, $field, $row, $PA['fieldConf']);
			unset($flexFormHelper);
		}
		// Get data structure:
		if (is_array($dataStructArray)) {
			// Get data:
			$xmlData = $PA['itemFormElValue'];
			$xmlHeaderAttributes = \TYPO3\CMS\Core\Utility\GeneralUtility::xmlGetHeaderAttribs($xmlData);
			$storeInCharset = strtolower($xmlHeaderAttributes['encoding']);
			if ($storeInCharset) {
				$currentCharset = $GLOBALS['LANG']->charSet;
				$xmlData = $GLOBALS['LANG']->csConvObj->conv($xmlData, $storeInCharset, $currentCharset, 1);
			}
			$editData = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($xmlData);
			// Must be XML parsing error...
			if (!is_array($editData)) {
				$editData = array();
			} elseif (!isset($editData['meta']) || !is_array($editData['meta'])) {
				$editData['meta'] = array();
			}
			// Find the data structure if sheets are found:
			$sheet = $editData['meta']['currentSheetId'] ? $editData['meta']['currentSheetId'] : 'sDEF';
			// Sheet to display
			// Create language menu:
			$langChildren = $dataStructArray['meta']['langChildren'] ? 1 : 0;
			$langDisabled = $dataStructArray['meta']['langDisable'] ? 1 : 0;
			$editData['meta']['currentLangId'] = array();
			// Look up page overlays:
			$checkPageLanguageOverlay = $GLOBALS['BE_USER']->getTSConfigVal('options.checkPageLanguageOverlay') ? TRUE : FALSE;
			if ($checkPageLanguageOverlay) {
				$pageOverlays = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages_language_overlay', 'pid=' . intval($row['pid']) . \TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause('pages_language_overlay') . \TYPO3\CMS\Backend\Utility\BackendUtility::versioningPlaceholderClause('pages_language_overlay'), '', '', '', 'sys_language_uid');
			}
			$languages = $this->getAvailableLanguages();
			foreach ($languages as $lInfo) {
				if ($GLOBALS['BE_USER']->checkLanguageAccess($lInfo['uid']) && (!$checkPageLanguageOverlay || $lInfo['uid'] <= 0 || is_array($pageOverlays[$lInfo['uid']]))) {
					$editData['meta']['currentLangId'][] = $lInfo['ISOcode'];
				}
			}
			if (!is_array($editData['meta']['currentLangId']) || !count($editData['meta']['currentLangId'])) {
				$editData['meta']['currentLangId'] = array('DEF');
			}
			$editData['meta']['currentLangId'] = array_unique($editData['meta']['currentLangId']);
			$PA['_noEditDEF'] = FALSE;
			if ($langChildren || $langDisabled) {
				$rotateLang = array('DEF');
			} else {
				if (!in_array('DEF', $editData['meta']['currentLangId'])) {
					array_unshift($editData['meta']['currentLangId'], 'DEF');
					$PA['_noEditDEF'] = TRUE;
				}
				$rotateLang = $editData['meta']['currentLangId'];
			}
			// Tabs sheets
			if (is_array($dataStructArray['sheets'])) {
				$tabsToTraverse = array_keys($dataStructArray['sheets']);
			} else {
				$tabsToTraverse = array($sheet);
			}
			foreach ($rotateLang as $lKey) {
				if (!$langChildren && !$langDisabled) {
					$item .= '<strong>' . $this->getLanguageIcon($table, $row, ('v' . $lKey)) . $lKey . ':</strong>';
				}
				// Default language, other options are "lUK" or whatever country code (independant of system!!!)
				$lang = 'l' . $lKey;
				$tabParts = array();
				foreach ($tabsToTraverse as $sheet) {
					$sheetContent = '';
					list($dataStruct, $sheet) = \TYPO3\CMS\Core\Utility\GeneralUtility::resolveSheetDefInDS($dataStructArray, $sheet);
					// If sheet has displayCond
					if ($dataStruct['ROOT']['TCEforms']['displayCond']) {
						$splittedCondition = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(':', $dataStruct['ROOT']['TCEforms']['displayCond']);
						$skipCondition = FALSE;
						$fakeRow = array();
						switch ($splittedCondition[0]) {
							case 'FIELD':
								list($sheetName, $fieldName) = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('.', $splittedCondition[1]);
								$fieldValue = $editData['data'][$sheetName][$lang][$fieldName];
								$splittedCondition[1] = $fieldName;
								$dataStruct['ROOT']['TCEforms']['displayCond'] = join(':', $splittedCondition);
								$fakeRow = array($fieldName => $fieldValue);
								break;
							case 'HIDE_FOR_NON_ADMINS':

							case 'VERSION':

							case 'HIDE_L10N_SIBLINGS':

							case 'EXT':
								break;
							case 'REC':
								$fakeRow = array('uid' => $row['uid']);
								break;
							default:
								$skipCondition = TRUE;
								break;
						}
						// If sheets displayCond leads to false
						if (!$skipCondition && !$this->isDisplayCondition($dataStruct['ROOT']['TCEforms']['displayCond'], $fakeRow, 'vDEF')) {
							// Don't create this sheet
							continue;
						}
					}
					// Render sheet:
					if (is_array($dataStruct['ROOT']) && is_array($dataStruct['ROOT']['el'])) {
						// Default language, other options are "lUK" or whatever country code (independant of system!!!)
						$PA['_valLang'] = $langChildren && !$langDisabled ? $editData['meta']['currentLangId'] : 'DEF';
						$PA['_lang'] = $lang;
						// Assemble key for loading the correct CSH file
						$dsPointerFields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $GLOBALS['TCA'][$table]['columns'][$field]['config']['ds_pointerField'], TRUE);
						$PA['_cshKey'] = $table . '.' . $field;
						foreach ($dsPointerFields as $key) {
							$PA['_cshKey'] .= '.' . $row[$key];
						}
						// Push the sheet level tab to DynNestedStack
						$tabIdentString = '';
						if (is_array($dataStructArray['sheets'])) {
							$tabIdentString = $GLOBALS['TBE_TEMPLATE']->getDynTabMenuId('TCEFORMS:flexform:' . $PA['itemFormElName'] . $PA['_lang']);
							$this->pushToDynNestedStack('tab', $tabIdentString . '-' . (count($tabParts) + 1));
						}
						// Render flexform:
						$tRows = $this->getSingleField_typeFlex_draw($dataStruct['ROOT']['el'], $editData['data'][$sheet][$lang], $table, $field, $row, $PA, '[data][' . $sheet . '][' . $lang . ']');
						$sheetContent = '<div class="typo3-TCEforms-flexForm">' . $tRows . '</div>';
						// Pop the sheet level tab from DynNestedStack
						if (is_array($dataStructArray['sheets'])) {
							$this->popFromDynNestedStack('tab', $tabIdentString . '-' . (count($tabParts) + 1));
						}
					} else {
						$sheetContent = 'Data Structure ERROR: No ROOT element found for sheet "' . $sheet . '".';
					}
					// Add to tab:
					$tabParts[] = array(
						'label' => $dataStruct['ROOT']['TCEforms']['sheetTitle'] ? $this->sL($dataStruct['ROOT']['TCEforms']['sheetTitle']) : $sheet,
						'description' => $dataStruct['ROOT']['TCEforms']['sheetDescription'] ? $this->sL($dataStruct['ROOT']['TCEforms']['sheetDescription']) : '',
						'linkTitle' => $dataStruct['ROOT']['TCEforms']['sheetShortDescr'] ? $this->sL($dataStruct['ROOT']['TCEforms']['sheetShortDescr']) : '',
						'content' => $sheetContent
					);
				}
				if (is_array($dataStructArray['sheets'])) {
					$dividersToTabsBehaviour = isset($GLOBALS['TCA'][$table]['ctrl']['dividers2tabs']) ? $GLOBALS['TCA'][$table]['ctrl']['dividers2tabs'] : 1;
					$item .= $this->getDynTabMenu($tabParts, 'TCEFORMS:flexform:' . $PA['itemFormElName'] . $PA['_lang'], $dividersToTabsBehaviour);
				} else {
					$item .= $sheetContent;
				}
			}
		} else {
			$item = 'Data Structure ERROR: ' . $dataStructArray;
		}
		return $item;
	}

	/**
	 * Rendering a single item for the form
	 *
	 * @param string $table Table name of record
	 * @param string $field Fieldname to render
	 * @param array $row The record
	 * @param array $PA Parameters array containing a lot of stuff. Value by Reference!
	 * @return string Returns the item as HTML code to insert
	 * @access private
	 * @see getSingleField(), getSingleField_typeFlex_draw()
	 * @todo Define visibility
	 */
	public function getSingleField_SW($table, $field, $row, &$PA) {
		try {
			$field = parent::getSingleField_SW($table, $field, $row, $PA);
		} catch (\TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException $error) {
			$message = new t3lib_FlashMessage('WARNING! Removed FAL resource detected. The field "' . $field . '" has been reset to ' .
				'an empty value in order to prevent fatal, unrecoverable errors', 'WARNING', t3lib_div::SYSLOG_SEVERITY_FATAL);
			t3lib_FlashMessageQueue::addMessage($message);
			$PA['itemFormElValue'] = '';
			$field = parent::getSingleField_SW($table, $field, $row, $PA);
		} catch (Exception $error) {
			t3lib_div::sysLog($error->getMessage(), 'cms', t3lib_div::SYSLOG_SEVERITY_FATAL);
		}
		return $field;
	}

}