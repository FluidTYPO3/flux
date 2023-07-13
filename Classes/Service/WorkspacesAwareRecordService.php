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
    public function get(
        string $table,
        string $fields,
        ?string $clause = null,
        ?string $groupBy = null,
        ?string $orderBy = null,
        int $limit = 0,
        int $offset = 0
    ): ?array {
        $records = parent::get($table, $fields, $clause, $groupBy, $orderBy, $limit, $offset);
        return null === $records ? null : $this->overlayRecords($table, $records);
    }

    public function getSingle(string $table, string $fields, int $uid): ?array
    {
        $record = parent::getSingle($table, $fields, $uid);
        if ($record) {
            return $this->overlayRecord($table, $record);
        }
        return $record;
    }

    public function preparedGet(string $table, string $fields, string $condition, array $values = []): array
    {
        $records = parent::preparedGet($table, $fields, $condition, $values);
        return $this->overlayRecords($table, $records);
    }

    protected function overlayRecords(string $table, array $records): array
    {
        if (!$this->hasWorkspacesSupport($table)) {
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

    protected function overlayRecord(string $table, array $record): array
    {
        return $this->getWorkspaceVersionOfRecordOrRecordItself($table, $record) ?: $record;
    }

    protected function getWorkspaceVersionOfRecordOrRecordItself(string $table, array $record): array
    {
        $copy = false;
        if ($this->hasWorkspacesSupport($table)) {
            $copy = $record;
            $this->overlayRecordInternal($table, $copy);
        }
        return $copy === false ? $record : $copy;
    }

    protected function hasWorkspacesSupport(string $table): bool
    {
        return (
            $GLOBALS['BE_USER'] instanceof BackendUserAuthentication
            && ExtensionManagementUtility::isLoaded('workspaces')
            && BackendUtility::isTableWorkspaceEnabled($table)
        );
    }

    /**
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
