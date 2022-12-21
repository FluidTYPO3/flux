<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Interface RecordProviderInterface
 *
 * Contract for Providers which interact with database
 * records, e.g. Providers that operate on DB tables
 * and contains special triggering that requires a
 * certain table or field to be present.
 */
interface RecordProviderInterface
{
    /**
     * Must return TRUE if this ConfigurationProvider instance wants
     * to be the one used for proccesing $row.
     */
    public function trigger(array $row, ?string $table, ?string $field, ?string $extensionKey = null): bool;

    /**
     * Get the field name which will trigger processing
     */
    public function getFieldName(array $row): ?string;

    /**
     * Get the list_type value that will trigger processing
     */
    public function getTableName(array $row): ?string;

    public function setTableName(string $tableName): self;
    public function setFieldName(?string $fieldName): self;
}
