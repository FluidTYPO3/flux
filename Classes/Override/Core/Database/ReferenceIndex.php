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
 * ReferenceIndex Override
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Override\Core\Database
 */
class Tx_Flux_Override_Core_Database_ReferenceIndex extends \TYPO3\CMS\Core\Database\ReferenceIndex {

	/**
	 * Returns relation information for a $table/$row-array
	 * Traverses all fields in input row which are configured in TCA/columns
	 * It looks for hard relations to files and records in the TCA types "select" and "group"
	 *
	 * @param string $table Table name
	 * @param array $row Row from table
	 * @param string $onlyField Specific field to fetch for.
	 * @return array Array with information about relations
	 * @see export_addRecord()
	 * @todo Define visibility
	 */
	public function getRelations($table, $row, $onlyField = '') {
		// Load full table description
		\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA($table);
		// Initialize:
		$uid = $row['uid'];
		$nonFields = explode(',', 'uid,perms_userid,perms_groupid,perms_user,perms_group,perms_everybody,pid');
		$outRow = array();
		foreach ($row as $field => $value) {
			if (!in_array($field, $nonFields) && is_array($GLOBALS['TCA'][$table]['columns'][$field]) && (!$onlyField || $onlyField === $field)) {
				$conf = $GLOBALS['TCA'][$table]['columns'][$field]['config'];
				// Add files
				if ($result = $this->getRelations_procFiles($value, $conf, $uid)) {
					// Creates an entry for the field with all the files:
					$outRow[$field] = array(
						'type' => 'db',
						'itemArray' => $result
					);
				}
				// Add DB:
				if ($result = $this->getRelations_procDB($value, $conf, $uid, $table, $field)) {
					// Create an entry for the field with all DB relations:
					$outRow[$field] = array(
						'type' => 'db',
						'itemArray' => $result
					);
				}
				// For "flex" fieldtypes we need to traverse the structure looking for file and db references of course!
				if ($conf['type'] == 'flex') {
					// Get current value array:
					// NOTICE: failure to resolve Data Structures can lead to integrity problems with the reference index. Please look up the note in the JavaDoc documentation for the function t3lib_BEfunc::getFlexFormDS()
					$dataStructArray = \TYPO3\CMS\Backend\Utility\BackendUtility::getFlexFormDS($conf, $row, $table, $field, $this->WSOL);
					$currentValueArray = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($value);
					// Traversing the XML structure, processing files:
					if (is_array($currentValueArray)) {
						$this->temp_flexRelations = array(
							'db' => array(),
							'file' => array(),
							'softrefs' => array()
						);
						// Create and call iterator object:
						$flexObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Configuration\\FlexForm\\FlexFormTools');
						$flexObj->traverseFlexFormXMLData($table, $field, $row, $this, 'getRelations_flexFormCallBack');
						// Create an entry for the field:
						$outRow[$field] = array(
							'type' => 'flex',
							'flexFormRels' => $this->temp_flexRelations
						);
					}
				}
				// Soft References:
				if (strlen($value) && ($softRefs = \TYPO3\CMS\Backend\Utility\BackendUtility::explodeSoftRefParserList($conf['softref']))) {
					$softRefValue = $value;
					foreach ($softRefs as $spKey => $spParams) {
						$softRefObj = \TYPO3\CMS\Backend\Utility\BackendUtility::softRefParserObj($spKey);
						if (is_object($softRefObj)) {
							$resultArray = $softRefObj->findRef($table, $field, $uid, $softRefValue, $spKey, $spParams);
							if (is_array($resultArray)) {
								$outRow[$field]['softrefs']['keys'][$spKey] = $resultArray['elements'];
								if (strlen($resultArray['content'])) {
									$softRefValue = $resultArray['content'];
								}
							}
						}
					}
					if (is_array($outRow[$field]['softrefs']) && count($outRow[$field]['softrefs']) && strcmp($value, $softRefValue) && strstr($softRefValue, '{softref:')) {
						$outRow[$field]['softrefs']['tokenizedContent'] = $softRefValue;
					}
				}
			}
		}
		return $outRow;
	}

}