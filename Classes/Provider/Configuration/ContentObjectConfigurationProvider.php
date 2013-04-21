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
	 * @var string
	 */
	protected $extensionKey = 'flux';

	/**
	 * @var integer
	 */
	protected $priority = 0;

	/**
	 * @var string
	 */
	protected $fieldName = NULL;

	/**
	 * @param array $row
	 * @return array|mixed|NULL
	 */
	public function getTemplatePaths(array $row) {
		$typoScript = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$extensionIdentity = str_replace('_', '', $this->getExtensionKey($row));
		$paths = Tx_Flux_Utility_Array::convertTypoScriptArrayToPlainArray((array) $typoScript['plugin.']['tx_' . $extensionIdentity . '.']['view.']);
		$paths = Tx_Flux_Utility_Path::translatePath($paths);
		return $paths;
	}

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
		if (count($row) === 1 && isset($row['colPos'])) {
				// dropping an element in a column header dropzone in 6.0 only sends the "colPos"
				// and this colPos may contain nothing but positive integers. Bring the severe hacking.
			$backtrace = debug_backtrace();
			$retrievedArgument = NULL;
			foreach (array_reverse($backtrace) as $stackItem) {
				if ($stackItem['class'] === 'TYPO3\\CMS\\Backend\\View\\PageLayout\\ExtDirect\\ExtdirectPageCommands') {
					if ($stackItem['function'] === 'moveContentElement') {
						$retrievedArgument = $stackItem['args'][1];
						$segments = explode('-', $retrievedArgument);
						$slice = array_slice($segments, count($segments) - 3);
						if ($slice[0] === 'top') {
							$row['tx_flux_parent'] = $slice[1];
							$row['tx_flux_column'] = $slice[2];
							$row['colPos'] = -42;
						} elseif ($slice[0] === 'after') {
							$row['pid'] = 0 - $slice[1];
							$row['tx_flux_column'] = $slice[2];
						} else {
							$row['tx_flux_parent'] = $row['tx_flux_column'] = '';
						}
						break;
					}
				}
			}
		}
		if ($row['pid'] < 0) {
				// inserting a new element after another element. Check column position of that element.
			$relativeTo = abs($row['pid']);
			$relativeToRecord = t3lib_BEfunc::getRecord($this->tableName, $relativeTo);
			$row['tx_flux_parent'] = $relativeToRecord['tx_flux_parent'];
			$row['tx_flux_column'] = $relativeToRecord['tx_flux_column'];
		}
		unset($id, $reference);
	}

	/**
	 * @param string $operation
	 * @param integer $id
	 * @param array $row
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, t3lib_TCEmain $reference) {
		$url = t3lib_div::_GET('returnUrl');
		$urlHashCutoffPoint = strrpos($url, '#');
		$area = NULL;
		if ($urlHashCutoffPoint > 0) {
			$area = substr($url, 1 - (strlen($url) - $urlHashCutoffPoint));
			if (strpos($area, ':') === FALSE) {
				return;
			}
		}
		list ($contentAreaFromUrl, $parentUidFromUrl) = explode(':', $area);
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
			// note; hack-like pruning of an empty node that is inserted. Language handling in FlexForms combined with section usage suspected as cause
		if (empty($row['pi_flexform']) === FALSE && is_string($row['pi_flexform']) === TRUE) {
			$row['pi_flexform'] = str_replace('<field index=""></field>', '', $row['pi_flexform']);
		}
		unset($id, $operation, $reference);
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
			$this->adjustColumnPositionBasedOnCommandUrl($newUid);
			$oldUid = $row['t3_origuid'];
			$languageFieldName = $GLOBALS['TCA'][$this->tableName]['ctrl']['languageField'];
			$newLanguageUid = NULL;
			if ($oldUid) {
				$oldRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid,pid,' . $languageFieldName, $this->tableName, "uid = '" . $oldUid . "'");
				if (empty($row[$languageFieldName]) === FALSE) {
					$newLanguageUid = $row[$languageFieldName];
				} elseif (empty($oldRecord[$languageFieldName]) === FALSE) {
					$newLanguageUid = $oldRecord[$languageFieldName];
				} else {
					$newLanguageUid = 1; // TODO: resolve config.sys_language_uid but WITHOUT using Extbase TS resolution, consider pid of new record
				}
				$clause = "(tx_flux_column LIKE '%:" . $oldUid . "' || tx_flux_parent = '" . $oldUid . "') AND deleted = 0 AND hidden = 0";
				$children = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,pid,sys_language_uid,tx_flux_column,tx_flux_parent', 'tt_content', $clause);
				if (count($children) < 1) {
					return;
				}
				// Perform localization on all children, since this is not handled by the TCA field which otherwise cascades changes
				foreach ($children as $child) {
					if (strpos($child['tx_flux_column'], ':') === FALSE) {
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
			$this->adjustColumnPositionBasedOnCommandUrl($id);
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
				$row['tx_flux_column'] = $this->detectParentElementAreaFromRecord($relativeTo);
				$row['tx_flux_parent'] = $this->detectParentUidFromRecord($relativeTo);
			}
			if (strpos($row['tx_flux_column'], ':') !== FALSE) {
				// TODO: after migration to "parent" usage, remember to change this next line
				list ($row['tx_flux_column'], $row['tx_flux_parent']) = explode(':', $row['tx_flux_column']);
			}
			if ($row['tx_flux_parent'] > 0) {
				$row['colPos'] = -42;
			}
		}
		unset($id, $reference);
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
		$pasteCommands = array('copy', 'move');
		if (TRUE === in_array($command, $pasteCommands)) {
			$callback = t3lib_div::_GET('CB');
			$pasteCommand = $callback['paste'];
			$parameters = explode('|', $pasteCommand);
			list ($pid, $subCommand, $relativeUid, $uid, $possibleArea, $possibleColPos) = explode('-', $parameters[1]);
			$clipData = $GLOBALS['BE_USER']->getModuleData('clipboard', $GLOBALS['BE_USER']->getTSConfigVal('options.saveClipboard') ? '' : 'ses');
			if ($command === 'copy') {
				$copiedUid = $reference->copyMappingArray[$this->tableName][$id];
				$condition = "uid = '" . $copiedUid . "'";
				$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $this->tableName, $condition));
				if ('reference' === $subCommand) {
					$record['CType'] = 'shortcut';
					$record['records'] = $id;
				}
			} else {
				$condition = "uid = '" . $id . "'";
				$record = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $this->tableName, $condition));
			}
			if (0 < $relativeUid) {
				$relativeRecord = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $this->tableName, "uid = '" . $relativeUid . "'"));
				$record['sorting'] = $relativeRecord['sorting'] + 1;
				$relativeTo = 0 - $relativeUid;
			} else {
				$record['sorting'] = 0;
			}
			$record['pid'] = $pid;
			$record['tx_flux_column'] = $possibleArea;
			$record['tx_flux_parent'] = $uid;
			if (FALSE === empty($possibleArea)) {
				$record['colPos'] = -42;
			}
			if (FALSE === empty($possibleColPos) || $possibleColPos === 0 || $possibleColPos === '0') {
				$record['colPos'] = $possibleColPos;
			}
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tableName, $condition, $record);
		}
	}

	/**
	 * @param array $row
	 * @param mixed $dataStructure
	 * @param array $conf
	 * @return void
	 */
	public function postProcessDataStructure(array &$row, &$dataStructure, array $conf) {
		unset($row, $dataStructure, $conf);
	}

	/**
	 * @return void
	 */
	public function clearCacheCommand() {
		$files = glob(PATH_site . 'typo3temp/flux-*');
		if (TRUE === is_array($files)) {
			foreach ($files as $fileName) {
				unlink($fileName);
			}
		}
	}

	/**
	 * @param integer $id
	 * @return void
	 */
	protected function adjustColumnPositionBasedOnCommandUrl($id) {
		$commandUrl = t3lib_div::_GET('cmd');
		if (empty($commandUrl)) {
			return;
		}
		$instruction = array_pop(array_pop($commandUrl));
		$command = key($instruction);
		$relativeTo = $instruction[$command];
		if ($command === 'copy' || $command === 'move') {
			if (strpos($relativeTo, 'x') !== FALSE) {
				// Triggers when an URL-based copy/paste or cut/paste action is performed and
				// the target is a column directly in the page (i.e. not nested content column).
				// The implication: content-to-parent relationship should be nullified
				$row = array();
				$row['tx_flux_parent'] = $row['tx_flux_column'] = NULL;
				$row['colPos'] = array_pop(explode('x', $relativeTo));
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', "uid = '" . $id . "'", $row);
			}
		}
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

}
