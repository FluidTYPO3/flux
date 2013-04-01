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
 * Content Element Service
 *
 * Service to interact with content elements
 *
 * @package Flux
 * @subpackage Service
 */
class Tx_Flux_Service_ContentService implements t3lib_Singleton {

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param Tx_Flux_Service_FluxService $configurationService
	 */
	public function injectFlexFormService(Tx_Flux_Service_FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Gets child content from $record's $area
	 *
	 * @param integer $localizedUid The UID (localized through _LOCALIZED_UID substitution if localization is wanted) of the parent element
	 * @param string $area The area of the parent element from which to render child content
	 * @param integer $limit
	 * @param string $order
	 * @param string $sortDirection
	 * @return array
	 */
	public function getChildContent($localizedUid, $area, $limit=99999, $order='sorting', $sortDirection='ASC') {
		$order .= ' ' . $sortDirection;
		$conditions = "((tx_flux_column = '" . $area . ':' . $localizedUid . "')
			OR (tx_flux_parent = '" . $localizedUid . "' AND (tx_flux_column = '" . $area . "' OR tx_flux_column = '" . $area . ':' . $localizedUid . "')))
			AND deleted = 0 AND hidden = 0";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_content', $conditions, 'uid', $order, $limit);
		$elements = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$conf = array(
				'tables' => 'tt_content',
				'source' => $row['uid'],
				'dontCheckPid' => 1
			);
			array_push($elements, $GLOBALS['TSFE']->cObj->RECORDS($conf));
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $elements;
	}

	/**
	 * Renders child content from $record's $area
	 *
	 * @param integer $localizedUid The UID (localized through _LOCALIZED_UID substitution if localization is wanted) of the parent element
	 * @param string $area The area of the parent element from which to render child content
	 * @param integer $limit
	 * @param string $order
	 * @param string $sortDirection
	 * @return string
	 */
	public function renderChildContent($localizedUid, $area, $limit=99999, $order='sorting', $sortDirection='ASC') {
		$elements = $this->getChildContent($localizedUid, $area, $limit, $order, $sortDirection);
		$html = implode(LF, $elements);
		return $html;
	}

	/**
	 * Get an array of child element records from a parent FCE
	 *
	 * @param integer $id
	 * @return array
	 * @api
	 */
	public function getChildContentElementUids($id) {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,pid,sys_language_uid,tx_flux_column,tx_flux_parent', 'tt_content', "(tx_flux_column LIKE '%:" . $id . "' || tx_flux_parent = '" . $id . "') AND deleted = 0 AND hidden = 0");
		return (array) $rows;
	}

	/**
	 * Detects the desired content area name for the element currently being
	 * edited. This should only be executed from within TCEmain hooks as it
	 * partially depends on URL parameters!
	 *
	 * @return string
	 */
	public function detectParentElementAreaFromUrl() {
		$url = t3lib_div::_GET('returnUrl');
		$urlHashCutoffPoint = strrpos($url, '#');
		$area = NULL;
		if ($urlHashCutoffPoint > 0) {
			$area = substr($url, 1 - (strlen($url) - $urlHashCutoffPoint));
			if (strpos($area, ':') === FALSE) {
				return NULL;
			}
		}
		return array_shift(explode(':', $area));
	}

	/**
	 * Detects the desired parent element for the element currently being
	 * edited. This should only be executed from within TCEmain hooks as it
	 * partially depends on URL parameters!
	 *
	 * @return integer
	 */
	public function detectParentUidFromUrl() {
		$url = t3lib_div::_GET('returnUrl');
		$urlHashCutoffPoint = strrpos($url, '#');
		$area = NULL;
		if ($urlHashCutoffPoint > 0) {
			$area = substr($url, 1 - (strlen($url) - $urlHashCutoffPoint));
			if (strpos($area, ':') === FALSE) {
				return NULL;
			}
		}
		return array_pop(explode(':', $area));
	}

	/**
	 * @param integer $uid
	 * @return string
	 */
	public function detectParentElementAreaFromRecord($uid) {
		$uid = abs($uid);
		$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', "uid = '" . $uid . "'"));
		return $record['tx_flux_column'];
	}

	/**
	 * @param integer $uid
	 * @return integer
	 */
	public function detectParentUidFromRecord($uid) {
		$uid = abs($uid);
		$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', "uid = '" . $uid . "'"));
		return intval($record['tx_flux_parent']);
	}

	/**
	 * @param integer $uid
	 * @return array
	 */
	public function getContentAreasDefinedInContentElement($uid) {
		$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', "uid = '" . $uid . "'"));
		list ($extensionKey, $fileName) = explode(':', $record['tx_fed_fcefile']);
		$typoScript = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$templatePaths = $typoScript['plugin.']['tx_fed.']['fce.'][$extensionKey . '.'];
		$values = $this->configurationService->convertFlexFormContentToArray($record['pi_flexform']);
		$extensionName = t3lib_div::underscoredToUpperCamelCase($extensionKey);
		$configuration = $this->configurationService->getFlexFormConfigurationFromFile($templatePaths['templateRootPath'] . $fileName, $values, 'Configuration', $extensionName);
		$columns = array();
		foreach ($configuration['grid'] as $row) {
			foreach ($row as $column) {
				foreach ($column['areas'] as $area) {
					array_push($columns, array($area['label'], $area['name']));

				}
			}
		}
		return $columns;
	}

	/**
	 * Gets a value for the field tx_flux_column based on $record and $id
	 *
	 * @param array $record
	 * @param integer $id
	 * @return string
	 * @api
	 */
	public function getFlexibleContentElementArea($record, $id = NULL) {
		$url = t3lib_div::_GET('returnUrl');
		$urlHashCutoffPoint = strrpos($url, '#');
		if ($urlHashCutoffPoint > 0) {
			$area = substr($url, 1 - (strlen($url)-$urlHashCutoffPoint));
		} elseif ($record['pid'] < 0) {
			$afterContentElementUid = abs($record['pid']);
			$afterRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('pid,tx_flux_column', 'tt_content', "uid = '" . $afterContentElementUid . "'");
			$area = $afterRecord['tx_flux_column'];
		} elseif ($id > 0) {
			if ($record['tx_flux_column']) {
				$area = $record['tx_flux_column'];
			} elseif ($id !== 'NEW') {
					// We need the field's contents from DB since it is not provided in $record
				$existingRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('pid,tx_flux_column', 'tt_content', "uid = '" . $id . "'");
				$area = $existingRecord['tx_flux_column'];
			} else {
				$area = $record['tx_flux_column'];
			}
		} else {
			$area = $record['tx_flux_column'];
		}
		return $area;
	}

}
