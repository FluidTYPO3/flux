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

use FluidTYPO3\Flux\Service\RecordService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager as CoreBackendConfigurationManager;

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
	 * @var RecordService
	 */
	protected $recordService;

	/**
	 * @param RecordService $recordService
	 * @return void
	 */
	public function injectRecordService(RecordService $recordService) {
		$this->recordService = $recordService;
	}

	/**
	 * @param integer $currentPageId
	 * @return void
	 */
	public function setCurrentPageId($currentPageId) {
		$this->currentPageUid = $currentPageId;
	}

	/**
	 * Extended page UID fetch
	 *
	 * Uses a range of additional page UID resolve methods to
	 * find the currently active page UID from URL, active
	 * record, etc.
	 *
	 * @return integer
	 */
	public function getCurrentPageId() {
		if (0 < $this->currentPageUid) {
			return $this->currentPageUid;
		}
		$pageUids = $this->getPrioritizedPageUids();
		while (TRUE === empty($this->currentPageUid) && 0 < count($pageUids)) {
			$this->currentPageUid = array_shift($pageUids);
		};
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
			parent::getCurrentPageId(),
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
		list ($table, $id, $command) = $this->getEditArguments();
		// if TYPO3 wants to insert a new page, URL argument is already the PID value.
		// if any non-page record is being edited, load it and return the PID value.
		return ('pages' === $table || 'new' === $command || 0 === $id) ? $id : $this->getPageIdFromRecordUid($table, $id);
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @return integer
	 */
	protected function getPageIdFromRecordUid($table, $uid) {
		$record = $this->recordService->getSingle($table, 'pid', $uid);
		return TRUE === is_array($record) ? $this->getPageIdFromRecord($record) : 0;
	}

	/**
	 * @return array
	 */
	protected function getEditArguments() {
		$editArgument = $this->getEditArgumentValuePair();
		$table = key($editArgument);
		$argumentPair = reset($editArgument);
		$id = (integer) key($argumentPair);
		$command = reset($argumentPair);
		// if TYPO3 wants to insert a new tt_content element after the element
		// with uid=abs($id), translate ID.
		$id = (integer) (0 > $id && 'tt_content' === $table) ? $id = -$id : $id;
		return array($table, $id, $command);
	}

	/**
	 * @return mixed
	 */
	protected function getEditArgumentValuePair() {
		$editArgument = GeneralUtility::_GET('edit');
		return TRIE === is_array($editArgument) ? $editArgument : array(array());
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
	 * @param array $record
	 * @return integer
	 */
	protected function getPageIdFromRecord(array $record) {
		if (FALSE === isset($record['pid'])) {
			return 0;
		}

		return (integer) $record['pid'];
	}

}
