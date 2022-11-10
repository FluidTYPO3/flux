<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Service to wrap around record operations normally going through
 * the $TYPO3_DB global variable.
 */
class WorkspacesAwareRecordService extends RecordService implements SingletonInterface
{
    /**
     * @param string $table
     * @param string $fields
     * @param string $clause
     * @param string $groupBy
     * @param string $orderBy
     * @param integer $limit
     * @param integer $offset
     * @return array|null
     */
    public function get($table, $fields, $clause = '1=1', $groupBy = '', $orderBy = '', $limit = 0, $offset = 0)
    {
        $records = parent::get($table, $fields, $clause, $groupBy, $orderBy, $limit, $offset);
        return null === $records ? null : $this->overlayRecords($table, $records);
    }

    /**
     * @param string $table
     * @param string $fields
     * @param integer $uid
     * @return array|null
     */
    public function getSingle($table, $fields, $uid)
    {
        $record = parent::getSingle($table, $fields, $uid);
        if ($record) {
            return $this->overlayRecord($table, $record);
        }
        return $record;
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
        $records = parent::preparedGet($table, $fields, $condition, $values);
        return $this->overlayRecords($table, $records);
    }

    /**
     * @param string $table
     * @param array $records
     * @return array
     */
    protected function overlayRecords($table, array $records)
    {
        if (false === $this->hasWorkspacesSupport($table)) {
            return $records;
        }
        foreach ($records as $index => $record) {
            $overlay = $this->overlayRecordInternal($table, $record);
            if (!$overlay) {
                unset($records[$index]);
            } else {
                $records[$index] = $overlay;
            }
        }
        return $records;
    }

    /**
     * @param string $table
     * @param array $record
     * @return array
     */
    protected function overlayRecord($table, array $record)
    {
        return $this->getWorkspaceVersionOfRecordOrRecordItself($table, $record) ?: $record;
    }

    /**
     * @param string $table
     * @param array $record
     * @return array
     */
    protected function getWorkspaceVersionOfRecordOrRecordItself($table, $record)
    {
        $copy = false;
        if ($this->hasWorkspacesSupport($table)) {
            $copy = $record;
            $this->overlayRecordInternal($table, $copy);
        }
        return $copy === false ? $record : $copy;
    }

    /**
     * @param string $table
     * @return boolean
     */
    protected function hasWorkspacesSupport($table)
    {
        return (
            $GLOBALS['BE_USER'] instanceof BackendUserAuthentication
            && ExtensionManagementUtility::isLoaded('workspaces')
            && BackendUtility::isTableWorkspaceEnabled($table)
        );
    }

    /**
     * @param string $table
     * @param array $copy
     * @return array|false
     * @codeCoverageIgnore
     */
    protected function overlayRecordInternal(string $table, array $copy)
    {
        BackendUtility::workspaceOL($table, $copy, -99, false);
        /** array|false */
        return $copy;
    }
}
