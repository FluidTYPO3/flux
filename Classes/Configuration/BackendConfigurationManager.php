<?php
namespace FluidTYPO3\Flux\Configuration;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager as CoreBackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Flux ConfigurationManager implementation: Backend
 *
 * @package Flux
 * @subpackage Configuraion
 */
class BackendConfigurationManager extends CoreBackendConfigurationManager implements SingletonInterface {

	/**
	 * @var integer
	 */
	protected $currentPageUid = 0;

	/**
	 * Extended page UID fetch
	 *
	 * Uses a range of additional page UID resolve methods to
	 * find the currently active page UID from URL, active
	 * record, etc.
	 *
	 * @return integer
	 */
	protected function getCurrentPageId() {
		if (0 < $this->currentPageUid) {
			return $this->currentPageUid;
		}
		$pageUids = $this->getPrioritizedPageUids();
		foreach ($pageUids as $pageUid) {
			if (0 < $pageUid) {
				$this->currentPageUid = $pageUid;
				break;
			}
		}
		return $this->currentPageUid;
	}

	/**
	 * @return array
	 */
	protected function getPrioritizedPageUids() {
		return array(
			$this->getPageIdFromGet(),
			$this->getPageIdFromPost(),
			$this->getPageIdFromRecordIdentifiedInEditUrlArgument(),
			$this->getPageIdFromContentObject(),
			$this->getPageIdFromTypoScriptRecordIfOnlyOneRecordExists(),
		);
	}

	/**
	 * Reads the reserved "id" GET variable if specified
	 *
	 * @return integer
	 */
	protected function getPageIdFromGet() {
		return (integer) GeneralUtility::_GET('id');
	}

	/**
	 * Reads the reserved "id" variable if it was POST'ed
	 *
	 * @return integer
	 */
	protected function getPageIdFromPost() {
		return (integer) GeneralUtility::_POST('id');
	}

	/**
	 * Reads page UID from the $_GET['edit'] argument which is
	 * used on the "alt_doc.php" file (TCEforms rendering file)
	 * which is possible since we can know the PID if:
	 *
	 * - one record is being edited from "pages" table
	 * - one or more content records being edited, in which case
	 *   each content record will have the same PID and using the
	 *   first one is then sufficient.
	 *
	 * @return integer
	 * @throws \UnexpectedValueException
	 */
	protected function getPageIdFromRecordIdentifiedInEditUrlArgument() {
		$editArgument = GeneralUtility::_GET('edit');
		if (FALSE === is_array($editArgument)) {
			return 0;
		}
		$table = key($editArgument);
		$argumentPair = reset($editArgument);
		$id = key($argumentPair);
		if ('pages' === $table || 'new' === reset($argumentPair)) {
			return (integer) $id;
		}
		if (FALSE === isset($GLOBALS['TCA'][$table])) {
			throw new \UnexpectedValueException('Submitted table "' . $table . '" is not registered in TCA', 1392143931);
		}
		$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('pid', $table, "uid = '" . (integer) $id . "'");
		return (FALSE !== $record ? $this->getPageIdFromRecord($record) : 0);
	}

	/**
	 * Reads the PID from the record belonging to the content object
	 * that's currently being rendered/manipulated. Is unlikely to
	 * return any value but is included for completeness.
	 *
	 * @return integer
	 */
	protected function getPageIdFromContentObject() {
		$record = $this->getContentObject()->data;
		return TRUE === is_array($record) ? $this->getPageIdFromRecord($record) : 0;
	}

	/**
	 * Reads the PID of the root TypoScript record if there is only
	 * one single, active TypoScript template in the site. If there
	 * is more then one active template, no page UID is returned from
	 * this method - but very likely, one is returned from one of the
	 * other methods.
	 *
	 * @return integer
	 */
	protected function getPageIdFromTypoScriptRecordIfOnlyOneRecordExists() {
		$time = time();
		$condition = 'root = 1 AND hidden = 0 AND deleted = 0 AND (starttime = 0 OR starttime < ' . $time . ') AND (endtime = 0 OR endtime > ' . $time . ')';
		$numberOfTemplates = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows('uid', 'sys_template', $condition);
		if (1 > $numberOfTemplates) {
			return 0;
		}
		$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'sys_template', $condition);
		return $this->getPageIdFromRecord($record);
	}

	/**
	 * @param array $record
	 * @return integer
	 */
	protected function getPageIdFromRecord(array $record) {
		return (integer) (FALSE === isset($record['pid']) ? : $record['pid']);
	}

}
