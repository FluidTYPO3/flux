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
class Tx_Flux_Override_Backend_Form_FormEngine extends t3lib_TCEforms {

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
				$sheetContent = '';
				foreach ($tabsToTraverse as $sheet) {
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
		} catch (\TYPO3\CMS\Core\Resource\Exception $error) {
			$message = new t3lib_FlashMessage('WARNING! FAL resource problem detected. The field "' . $field . '" has been reset to ' .
				'an empty value in order to prevent fatal, unrecoverable errors. The actual message is a ' . get_class($error) .
				' which states: (' . $error->getCode() . ') ' . $error->getMessage(), 'WARNING', t3lib_div::SYSLOG_SEVERITY_FATAL);
			t3lib_FlashMessageQueue::addMessage($message);
			$PA['itemFormElValue'] = '';
			$field = parent::getSingleField_SW($table, $field, $row, $PA);
		} catch (Exception $error) {
			t3lib_div::sysLog($error->getMessage(), 'cms', t3lib_div::SYSLOG_SEVERITY_FATAL);
		}
		return $field;
	}

	/************************************************************
	 *
	 * Form element helper functions
	 *
	 ************************************************************/
	/**
	 * Prints the selector box form-field for the db/file/select elements (multiple)
	 *
	 * @param string $fName Form element name
	 * @param string $mode Mode "db", "file" (internal_type for the "group" type) OR blank (then for the "select" type)
	 * @param string $allowed Commalist of "allowed
	 * @param array $itemArray The array of items. For "select" and "group"/"file" this is just a set of value. For "db" its an array of arrays with table/uid pairs.
	 * @param string $selector Alternative selector box.
	 * @param array $params An array of additional parameters, eg: "size", "info", "headers" (array with "selector" and "items"), "noBrowser", "thumbnails
	 * @param string $onFocus On focus attribute string
	 * @param string $table (optional) Table name processing for
	 * @param string $field (optional) Field of table name processing for
	 * @param string $uid (optional) uid of table record processing for
	 * @param array $config (optional) The TCA field config
	 * @return string The form fields for the selection.
	 * @todo Define visibility
	 */
	public function dbFileIcons($fName, $mode, $allowed, $itemArray, $selector = '', $params = array(), $onFocus = '', $table = '', $field = '', $uid = '', $config = array()) {
		$title = '';
		$disabled = '';
		if ($this->renderReadonly || $params['readOnly']) {
			$disabled = ' disabled="disabled"';
		}
		// Sets a flag which means some JavaScript is included on the page to support this element.
		$this->printNeededJS['dbFileIcons'] = 1;
		// INIT
		$uidList = array();
		$opt = array();
		$itemArrayC = 0;
		// Creating <option> elements:
		if (is_array($itemArray)) {
			$itemArrayC = count($itemArray);
			switch ($mode) {
				case 'db':
					foreach ($itemArray as $pp) {
						$pRec = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordWSOL($pp['table'], $pp['id']);
						if (is_array($pRec)) {
							$pTitle = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordTitle($pp['table'], $pRec, FALSE, TRUE);
							$pUid = $pp['table'] . '_' . $pp['id'];
							$uidList[] = $pUid;
							$title = htmlspecialchars($pTitle);
							$opt[] = '<option value="' . htmlspecialchars($pUid) . '" title="' . $title . '">' . $title . '</option>';
						}
					}
					break;
				case 'file_reference':

				case 'file':
					foreach ($itemArray as $item) {
						$itemParts = explode('|', $item);
						if (TRUE === empty($itemParts[1])) {
							$itemParts[1] = $itemParts[0];
						}
						$uidList[] = ($pUid = ($pTitle = $itemParts[0]));
						$title = htmlspecialchars(basename(rawurldecode($itemParts[1])));
						$opt[] = '<option value="' . htmlspecialchars(rawurldecode($itemParts[0])) . '" title="' . $title . '">' . $title . '</option>';
					}
					break;
				case 'folder':
					foreach ($itemArray as $pp) {
						$pParts = explode('|', $pp);
						$uidList[] = ($pUid = ($pTitle = $pParts[0]));
						$title = htmlspecialchars(rawurldecode($pParts[0]));
						$opt[] = '<option value="' . htmlspecialchars(rawurldecode($pParts[0])) . '" title="' . $title . '">' . $title . '</option>';
					}
					break;
				default:
					foreach ($itemArray as $pp) {
						$pParts = explode('|', $pp, 2);
						$uidList[] = ($pUid = $pParts[0]);
						$pTitle = $pParts[1];
						$title = htmlspecialchars(rawurldecode($pTitle));
						$opt[] = '<option value="' . htmlspecialchars(rawurldecode($pUid)) . '" title="' . $title . '">' . $title . '</option>';
					}
					break;
			}
		}
		// Create selector box of the options
		$sSize = $params['autoSizeMax'] ? \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($itemArrayC + 1, \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($params['size'], 1), $params['autoSizeMax']) : $params['size'];
		if (!$selector) {
			$isMultiple = $params['size'] != 1;
			$selector = '<select id="' . uniqid('tceforms-multiselect-') . '" ' . ($params['noList'] ? 'style="display: none"' : 'size="' . $sSize . '"' . $this->insertDefStyle('group', 'tceforms-multiselect')) . ($isMultiple ? ' multiple="multiple"' : '') . ' name="' . $fName . '_list" ' . $onFocus . $params['style'] . $disabled . '>' . implode('', $opt) . '</select>';
		}
		$icons = array(
			'L' => array(),
			'R' => array()
		);
		if (!$params['readOnly'] && !$params['noList']) {
			if (!$params['noBrowser']) {
				// Check against inline uniqueness
				$inlineParent = $this->inline->getStructureLevel(-1);
				if (is_array($inlineParent) && $inlineParent['uid']) {
					if ($inlineParent['config']['foreign_table'] == $table && $inlineParent['config']['foreign_unique'] == $field) {
						$objectPrefix = $this->inline->inlineNames['object'] . \TYPO3\CMS\Backend\Form\Element\InlineElement::Structure_Separator . $table;
						$aOnClickInline = $objectPrefix . '|inline.checkUniqueElement|inline.setUniqueElement';
						$rOnClickInline = 'inline.revertUnique(\'' . $objectPrefix . '\',null,\'' . $uid . '\');';
					}
				}
				if (is_array($config['appearance']) && isset($config['appearance']['elementBrowserType'])) {
					$elementBrowserType = $config['appearance']['elementBrowserType'];
				} else {
					$elementBrowserType = $mode;
				}
				if (is_array($config['appearance']) && isset($config['appearance']['elementBrowserAllowed'])) {
					$elementBrowserAllowed = $config['appearance']['elementBrowserAllowed'];
				} else {
					$elementBrowserAllowed = $allowed;
				}
				$aOnClick = 'setFormValueOpenBrowser(\'' . $elementBrowserType . '\',\'' . ($fName . '|||' . $elementBrowserAllowed . '|' . $aOnClickInline) . '\'); return false;';
				$icons['R'][] = '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '">' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-insert-record', array('title' => htmlspecialchars($this->getLL(('l_browse_' . ($mode == 'db' ? 'db' : 'file')))))) . '</a>';
			}
			if (!$params['dontShowMoveIcons']) {
				if ($sSize >= 5) {
					$icons['L'][] = '<a href="#" onclick="setFormValueManipulate(\'' . $fName . '\',\'Top\'); return false;">' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-move-to-top', array('title' => htmlspecialchars($this->getLL('l_move_to_top')))) . '</a>';
				}
				$icons['L'][] = '<a href="#" onclick="setFormValueManipulate(\'' . $fName . '\',\'Up\'); return false;">' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-move-up', array('title' => htmlspecialchars($this->getLL('l_move_up')))) . '</a>';
				$icons['L'][] = '<a href="#" onclick="setFormValueManipulate(\'' . $fName . '\',\'Down\'); return false;">' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-move-down', array('title' => htmlspecialchars($this->getLL('l_move_down')))) . '</a>';
				if ($sSize >= 5) {
					$icons['L'][] = '<a href="#" onclick="setFormValueManipulate(\'' . $fName . '\',\'Bottom\'); return false;">' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-move-to-bottom', array('title' => htmlspecialchars($this->getLL('l_move_to_bottom')))) . '</a>';
				}
			}
			$clipElements = $this->getClipboardElements($allowed, $mode);
			if (count($clipElements)) {
				$aOnClick = '';
				foreach ($clipElements as $elValue) {
					if ($mode == 'db') {
						list($itemTable, $itemUid) = explode('|', $elValue);
						$itemTitle = $GLOBALS['LANG']->JScharCode(\TYPO3\CMS\Backend\Utility\BackendUtility::getRecordTitle($itemTable, \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordWSOL($itemTable, $itemUid)));
						$elValue = $itemTable . '_' . $itemUid;
					} else {
						// 'file', 'file_reference' and 'folder' mode
						$itemTitle = 'unescape(\'' . rawurlencode(basename($elValue)) . '\')';
					}
					$aOnClick .= 'setFormValueFromBrowseWin(\'' . $fName . '\',unescape(\'' . rawurlencode(str_replace('%20', ' ', $elValue)) . '\'),' . $itemTitle . ',' . $itemTitle . ');';
				}
				$aOnClick .= 'return false;';
				$icons['R'][] = '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '">' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-document-paste-into', array('title' => htmlspecialchars(sprintf($this->getLL(('l_clipInsert_' . ($mode == 'db' ? 'db' : 'file'))), count($clipElements))))) . '</a>';
			}
		}
		if (!$params['readOnly'] && !$params['noDelete']) {
			$rOnClick = $rOnClickInline . 'setFormValueManipulate(\'' . $fName . '\',\'Remove\'); return false';
			$icons['L'][] = '<a href="#" onclick="' . htmlspecialchars($rOnClick) . '">' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-selection-delete', array('title' => htmlspecialchars($this->getLL('l_remove_selected')))) . '</a>';
		}
		$imagesOnly = FALSE;
		if ($params['thumbnails'] && $params['info']) {
			// In case we have thumbnails, check if only images are allowed.
			// In this case, render them below the field, instead of to the right
			$allowedExtensionList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(' ', strtolower($params['info']), TRUE);
			$imageExtensionList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', strtolower($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']), TRUE);
			$imagesOnly = TRUE;
			foreach ($allowedExtensionList as $allowedExtension) {
				if (!\TYPO3\CMS\Core\Utility\GeneralUtility::inArray($imageExtensionList, $allowedExtension)) {
					$imagesOnly = FALSE;
					break;
				}
			}
		}
		if ($imagesOnly) {
			$rightbox = '';
			$thumbnails = '<div class="imagethumbs">' . $this->wrapLabels($params['thumbnails']) . '</div>';
		} else {
			$rightbox = $this->wrapLabels($params['thumbnails']);
			$thumbnails = '';
		}
		// Hook: dbFileIcons_postProcess (requested by FAL-team for use with the "fal" extension)
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['dbFileIcons'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['dbFileIcons'] as $classRef) {
				$hookObject = \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($classRef);
				if (!$hookObject instanceof \TYPO3\CMS\Backend\Form\DatabaseFileIconsHookInterface) {
					throw new \UnexpectedValueException('$hookObject must implement interface TYPO3\\CMS\\Backend\\Form\\DatabaseFileIconsHookInterface', 1290167704);
				}
				$additionalParams = array(
					'mode' => $mode,
					'allowed' => $allowed,
					'itemArray' => $itemArray,
					'onFocus' => $onFocus,
					'table' => $table,
					'field' => $field,
					'uid' => $uid,
					'config' => $GLOBALS['TCA'][$table]['columns'][$field]
				);
				$hookObject->dbFileIcons_postProcess($params, $selector, $thumbnails, $icons, $rightbox, $fName, $uidList, $additionalParams, $this);
			}
		}
		$str = '<table border="0" cellpadding="0" cellspacing="0" width="1">
			' . ($params['headers'] ? '
				<tr>
					<td>' . $this->wrapLabels($params['headers']['selector']) . '</td>
					<td></td>
					<td></td>
					<td>' . ($params['thumbnails'] ? $this->wrapLabels($params['headers']['items']) : '') . '</td>
				</tr>' : '') . '
			<tr>
				<td valign="top">' . $selector . $thumbnails . ($params['noList'] ? '' : '<span class="filetypes">' . $this->wrapLabels($params['info'])) . '</span></td>
					<td valign="top" class="icons">' . implode('<br />', $icons['L']) . '</td>
					<td valign="top" class="icons">' . implode('<br />', $icons['R']) . '</td>
					<td valign="top" class="thumbnails">' . $rightbox . '</td>
			</tr>
		</table>';
		// Creating the hidden field which contains the actual value as a comma list.
		$str .= '<input type="hidden" name="' . $fName . '" value="' . htmlspecialchars(implode(',', $uidList)) . '" />';
		return $str;
	}

}