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

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Service to wrap around record operations normally going through
 * the $TYPO3_DB global variable.
 *
 * @package Flux
 * @subpackage Service
 */
class RecordService implements SingletonInterface {

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
		$connection = $this->getDatabaseConnection();
		return $connection->exec_SELECTgetRows($fields, $table, $clause, $groupBy, $orderBy, $limit);
	}

	/**
	 * @param string $table
	 * @param string $fields
	 * @param string $uid
	 * @return array|NULL
	 */
	public function getSingle($table, $fields, $uid) {
		$connection = $this->getDatabaseConnection();
		$record = $connection->exec_SELECTgetSingleRow($fields, $table, "uid = '" . intval($uid) . "'");
		return FALSE !== $record ? $record : NULL;
	}

	/**
	 * @param string $table
	 * @param array $record
	 * @return boolean
	 */
	public function update($table, array $record) {
		$connection = $this->getDatabaseConnection();
		return $connection->exec_UPDATEquery($table, "uid = '" . intval($record['uid']) . "'", $record);
	}

	/**
	 * @param string $table
	 * @param mixed $recordOrUid
	 * @return boolean
	 */
	public function delete($table, $recordOrUid) {
		$connection = $this->getDatabaseConnection();
		$clauseUid = TRUE === is_array($recordOrUid) ? $recordOrUid['uid'] : $recordOrUid;
		$clause = "uid = '" . intval($clauseUid) . "'";
		return $connection->exec_DELETEquery($table, $clause);
	}

	/**
	 * @param string $table
	 * @param string $fields
	 * @param string $condition
	 * @param array $values
	 * @return array
	 */
	public function preparedGet($table, $fields, $condition, $values = array()) {
		$connection = $this->getDatabaseConnection();
		$query = $connection->prepare_SELECTquery($fields, $table, $condition);
		$query->execute($values);
		$result = $query->fetchAll();
		$query->free();
		return $result;
	}

	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}
