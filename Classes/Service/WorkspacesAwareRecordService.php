<?php
namespace FluidTYPO3\Flux\Service;
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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Service to wrap around record operations normally going through
 * the $TYPO3_DB global variable.
 *
 * @package Flux
 * @subpackage Service
 */
class WorkspacesAwareRecordService extends RecordService implements SingletonInterface {

	/**
	 * @param string $table
	 * @param string $fields
	 * @param string $clause
	 * @param string $groupBy
	 * @param string $orderBy
	 * @param string $limit
	 * @return array|NULL
	 */
	public function get($table, $fields, $clause = '1=1', $groupBy = '', $orderBy = '', $limit = '') {
		$records = parent::get($table, $fields, $clause, $groupBy, $orderBy, $limit);
		return NULL === $records ? NULL : $this->overlayRecords($table, $records);
	}

	/**
	 * @param string $table
	 * @param string $fields
	 * @param string $uid
	 * @return array|NULL
	 */
	public function getSingle($table, $fields, $uid) {
		$record = parent::getSingle($table, $fields, $uid);
		return NULL === $record ? NULL : $this->overlayRecord($table, $record);
	}

	/**
	 * @param string $table
	 * @param string $fields
	 * @param string $condition
	 * @param array $values
	 * @return array
	 */
	public function preparedGet($table, $fields, $condition, $values = array()) {
		$records = parent::preparedGet($table, $fields, $condition, $values);
		return $this->overlayRecords($table, $records);
	}

	/**
	 * @param string $table
	 * @param array $records
	 * @return array
	 */
	protected function overlayRecords($table, array $records) {
		if (FALSE === $this->hasWorkspacesSupport($table)) {
			return $records;
		}
		foreach ($records as $index => $record) {
			$records[$index] = $this->overlayRecord($table, $record);
		}
		return $records;
	}

	/**
	 * @param string $table
	 * @param array $record
	 * @return array
	 */
	protected function overlayRecord($table, array $record) {
		if (FALSE === $this->hasWorkspacesSupport($table)) {
			return $record;
		}
		$copy = $record;
		BackendUtility::workspaceOL($table, $copy);
		return $copy === FALSE ? $record : $copy;
	}

	/**
	 * @param string $table
	 * @return boolean
	 */
	protected function hasWorkspacesSupport($table) {
		return BackendUtility::isTableWorkspaceEnabled($table);
	}

}
