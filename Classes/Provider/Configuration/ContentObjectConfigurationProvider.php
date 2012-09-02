<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * ConfigurationProvider for records in tt_content
 *
 * This Configuration Provider has the lowest possible priority
 * and is only used to execute a set of hook-style methods for
 * processing records. This processing ensures that relationships
 * between content elements get stored correctly -
 *
 * @package Flux
 * @subpackage Provider
 */
class Tx_Flux_Provider_Configuration_ContentObjectConfigurationProvider extends Tx_Flux_Provider_AbstractContentObjectConfigurationProvider implements Tx_Flux_Provider_ConfigurationProviderInterface {

	/**
	 * @var Tx_Flux_Service_Content
	 */
	protected $contentService;

	/**
	 * @param Tx_Flux_Service_Content $contentService
	 */
	public function injectContentService(Tx_Flux_Service_Content $contentService) {
		$this->contentService = $contentService;
	}

	/**
	 * @var string
	 */
	protected $tableName = 'tt_content';

	/**
	 * @var string
	 */
	protected $fieldName = NULL;

	/**
	 * @var string
	 */
	protected $extensionKey = 'flux';

	/**
	 * @var integer
	 */
	protected $priority = 0;

	/**
	 * @param array $row
	 * @param integer $id
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function preProcessRecord(array &$row, $id, t3lib_TCEmain $reference) {
		if (is_array($row['pi_flexform']['data'])) {
			foreach ((array) $row['pi_flexform']['data']['options']['lDEF'] as $key=>$value) {
				if (strpos($key, 'tt_content') === 0) {
					$realKey = array_pop(explode('.', $key));
					if (isset($row[$realKey])) {
						$row[$realKey] = $value['vDEF'];
					}
				}
			}
		}
		return;
	}

	/**
	 * @param string $operation
	 * @param integer $id
	 * @param array $row
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, t3lib_TCEmain $reference) {
		$contentAreaFromUrl = $this->contentService->detectParentElementAreaFromUrl();
		$parentUidFromUrl = $this->contentService->detectParentUidFromUrl();
		if ($contentAreaFromUrl) {
			$row['tx_flux_column'] = $contentAreaFromUrl;
		}
		if ($parentUidFromUrl > 0) {
			$row['tx_flux_parent'] = $parentUidFromUrl;
		}
		if (strpos($row['tx_flux_column'], ':') !== FALSE) {
			// TODO: after migration to "parent" usage, remember to change this next line
			list ($row['tx_flux_column'], $row['tx_flux_parent']) = explode(':', $row['tx_flux_column']);
		}
		if ($row['tx_flux_parent'] > 0) {
			$row['colPos'] = -42;
		}
		return;
	}

	/**
	 * @param string $status
	 * @param integer $id
	 * @param array $row
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function postProcessDatabaseOperation($status, $id, &$row, t3lib_TCEmain $reference) {
		if ($status === 'new') {
			$newUid = $reference->substNEWwithIDs[$id];
			$oldUid = $row['t3_origuid'];
			$languageFieldName = $GLOBALS['TCA'][$this->tableName]['ctrl']['languageField'];
			$newLanguageUid = NULL;
			if ($oldUid) {
				$oldRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid,' . $languageFieldName, $this->tableName, "uid = '" . $oldUid . "'");
				if (empty($row[$languageFieldName]) === FALSE) {
					$newLanguageUid = $row[$languageFieldName];
				} elseif (empty($oldRecord[$languageFieldName]) === FALSE) {
					$newLanguageUid = $oldRecord[$languageFieldName];
				} else {
					$newLanguageUid = 1; // TODO: resolve config.sys_language_uid but WITHOUT using Extbase TS resolution, consider pid of new record
				}
				$children = $this->contentService->getChildContentElementUids($oldUid);
				if (count($children) < 1) {
					return;
				}
					// Perform localization on all children, since this is not handled by the TCA field which otherwise cascades changes
				foreach ($children as $child) {
					if (strpos($child['tx_flux_column'], ':') !== FALSE) {
						$area = $child['tx_flux_column'];
					} else {
						$areaAndUid = explode(':', $child['tx_flux_column']);
						$area = $areaAndUid[0];
					}
					$overrideValues = array(
						'tx_flux_column' => $area,
						'tx_flux_parent' => $newUid,
						$languageFieldName => $newLanguageUid
					);
					if ($oldRecord[$languageFieldName] !== $newLanguageUid && $oldRecord['pid'] === $row['pid']) {
						$childUid = $reference->localize($this->tableName, $child['uid'], $newLanguageUid);
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tableName, "uid = '" . $childUid . "'", $overrideValues);
					} elseif ($child['tx_flux_parent'] < 1) {
							// patch; copying of elements which previously had no parent entered needs to be done
							// manually in this case because the TCA cascading that happens on "inline" type fields
							// does not trigger because the child element uses the old way of storing relationships.
							// The new copies will use the new way of storing relationships.
						$childUid = $reference->copyRecord($this->tableName, $child['uid'], $row['pid']);
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tableName, "uid = '" . $childUid . "'", $overrideValues);
					}
				}
			}
		}
		return;
	}

	/**
	 * Pre-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function preProcessCommand($command, $id, array &$row, &$relativeTo, t3lib_TCEmain $reference) {
		if ($command === 'move') {
			if (strpos($relativeTo, 'FLUX') !== FALSE) {
				// Triggers when CE is dropped on a nested content area's header dropzone (EXT:gridelements)
				list ($areaName, $parentElementUid, $pid) = explode('-', trim($relativeTo, '-'));
				$row['tx_flux_column'] = $areaName;
				$row['tx_flux_parent'] = $parentElementUid;
				$row['pid'] = $pid;
				$row['sorting'] = -1;
				$relativeTo = $pid;
			} elseif (strpos($relativeTo, 'x') > 0) {
				// Triggers when CE is dropped on a root (not CE) column header's dropzone (EXT:gridelements)
				// set colPos and remove FCE relation
				list ($relativeTo, $colPos) = explode('x', $relativeTo);
				$row['tx_flux_column'] = $row['tx_flux_parent'] = NULL;
				$row['colPos'] = $colPos;
				$row['sorting'] = -1;
			} elseif ($relativeTo < 0) {
				// Triggers when sorting a CE after another CE, $relativeTo is negative value of CE's UID
				$row['tx_flux_column'] = $this->contentService->detectParentElementAreaFromRecord($relativeTo);
				$row['tx_flux_parent'] = $this->contentService->detectParentUidFromRecord($relativeTo);
			}
			if (strpos($row['tx_flux_column'], ':') !== FALSE) {
				// TODO: after migration to "parent" usage, remember to change this next line
				list ($row['tx_flux_column'], $row['tx_flux_parent']) = explode(':', $row['tx_flux_column']);
			}
			if ($row['tx_flux_parent'] > 0) {
				$row['colPos'] = -42;
			}
		}
		return;
	}

	/**
	 * Post-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, t3lib_TCEmain $reference) {
		return;
	}
}
