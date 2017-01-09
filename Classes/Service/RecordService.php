<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Service to wrap around record operations normally going through
 * the $TYPO3_DB global variable.
 */
class RecordService implements SingletonInterface
{

    /**
     * @param string $table
     * @param string $fields
     * @param string $clause
     * @param string $groupBy
     * @param string $orderBy
     * @param string $limit
     * @return array|NULL
     */
    public function get($table, $fields, $clause = '1=1', $groupBy = '', $orderBy = '', $limit = '')
    {
        $connection = $this->getDatabaseConnection();
        return $connection->exec_SELECTgetRows($fields, $table, $clause, $groupBy, $orderBy, $limit);
    }

    /**
     * @param string $table
     * @param string $fields
     * @param string $uid
     * @return array|NULL
     */
    public function getSingle($table, $fields, $uid)
    {
        $connection = $this->getDatabaseConnection();
        $record = $connection->exec_SELECTgetSingleRow($fields, $table, "uid = '" . intval($uid) . "'");
        return false !== $record ? $record : null;
    }

    /**
     * @param string $table
     * @param array $record
     * @return boolean
     */
    public function update($table, array $record)
    {
        $connection = $this->getDatabaseConnection();
        return $connection->exec_UPDATEquery($table, "uid = '" . intval($record['uid']) . "'", $record);
    }

    /**
     * @param string $table
     * @param mixed $recordOrUid
     * @return boolean
     */
    public function delete($table, $recordOrUid)
    {
        $connection = $this->getDatabaseConnection();
        $clauseUid = true === is_array($recordOrUid) ? $recordOrUid['uid'] : $recordOrUid;
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
    public function preparedGet($table, $fields, $condition, $values = [])
    {
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
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
