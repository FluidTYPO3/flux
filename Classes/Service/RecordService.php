<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @param integer $limit
     * @return array|NULL
     */
    public function get($table, $fields, $clause = null, $groupBy = null, $orderBy = null, $limit = 0)
    {
        $statement = $this->getQueryBuilder($table)->from($table)->select(...explode(',', $fields));

        if ($groupBy) {
            $statement->groupBy($groupBy);
        }
        if ($orderBy) {
            $statement->orderBy(...explode(' ', $orderBy));
        }
        if ($clause) {
            $statement->where($clause);
        }
        if ($limit) {
            $statement->setMaxResults($limit);
        }

        return $statement->execute()->fetchAll();
    }

    /**
     * @param string $table
     * @param string $fields
     * @param string $uid
     * @return array|NULL
     */
    public function getSingle($table, $fields, $uid)
    {
        if (TYPO3_MODE === 'BE') {
            return BackendUtility::getRecord($table, $uid, $fields);
        }
        $results = $this->getQueryBuilder($table)
            ->from($table)
            ->select(...explode(',', $fields))
            ->where(sprintf('uid = %d', $uid))
            ->execute()
            ->fetchAll() ?: [];
        return reset($results);
    }

    /**
     * @param string $table
     * @param array $record
     * @return boolean
     */
    public function update($table, array $record)
    {
        $builder = $this->getQueryBuilder($table)->update($table)->where(sprintf('uid = %d', $record['uid']));
        foreach ($record as $name => $value) {
            $builder->set($name, $value);
        }
        return $builder->execute();
    }

    /**
     * @param string $table
     * @param mixed $recordOrUid
     * @return boolean
     */
    public function delete($table, $recordOrUid)
    {
        $clauseUid = true === is_array($recordOrUid) ? $recordOrUid['uid'] : $recordOrUid;
        $clause = "uid = '" . intval($clauseUid) . "'";
        return (bool) $this->getQueryBuilder($table)->delete($table)->where($clause)->execute();
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
        return $this->getQueryBuilder($table)->select(...explode(',', $fields))->from($table)->where($condition)->setParameters($values)->execute()->fetchAll();
    }

    /**
     * @param $table
     * @return QueryBuilder
     */
    protected function getQueryBuilder($table)
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

}
