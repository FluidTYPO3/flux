<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Service to wrap around record operations normally going through
 * the $TYPO3_DB global variable.
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
		$enabled = $this->hasWorkspacesSupport($table);
		return (TRUE === $enabled) ? $this->getWorkspaceVersionOfRecordOrRecordItself($table, $record) : $record;
	}

	/**
	 * @param string $table
	 * @param array $record
	 * @return array|boolean
	 */
	protected function getWorkspaceVersionOfRecordOrRecordItself($table, $record) {
		$copy = FALSE;
		if (NULL !== $GLOBALS['BE_USER']) {
			$copy = $record;
			BackendUtility::workspaceOL($table, $copy);
		}
		return $copy === FALSE ? $record : $copy;
	}

	/**
	 * @param string $table
	 * @return boolean
	 */
	protected function hasWorkspacesSupport($table) {
		return (NULL !== $GLOBALS['BE_USER'] && BackendUtility::isTableWorkspaceEnabled($table));
	}

}
