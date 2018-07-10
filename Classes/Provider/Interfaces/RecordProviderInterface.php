<?php
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\DataHandling\DataHandler;

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
     * to be the one used for proccesing $row
     *
     * @param array $row
     * @param string $table
     * @param string $field
     * @param string $extensionKey
     * @return boolean
     */
    public function trigger(array $row, $table, $field, $extensionKey = null);

    /**
     * Pre-process record data for the table that this ConfigurationProvider
     * is attached to.
     *
     * @param array $row The record by reference. Changing fields' values changes the record's values before display
     * @param integer $id The ID of the current record (which is sometimes now included in $row
     * @param DataHandler $reference A reference to the DataHandler object that is currently displaying the record
     * @return void
     * @deprecated Will be removed in Flux 10.0, please use DataHandler hooks directly
     */
    public function preProcessRecord(array &$row, $id, DataHandler $reference);

    /**
     * Post-process record data for the table that this ConfigurationProvider
     * is attached to.
     *
     * @param string $operation TYPO3 operation identifier, i.e. "update", "new" etc.
     * @param integer $id The ID of the current record (which is sometimes now included in $row
     * @param array $row the record by reference. Changing fields' values changes the record's values before saving
     * @param DataHandler $reference A reference to the DataHandler object that is currently saving the record
     * @param array $removals Allows methods to pass an array of field names to remove from the stored Flux value
     * @return void
     * @deprecated Will be removed in Flux 10.0, please use DataHandler hooks directly
     */
    public function postProcessRecord($operation, $id, array &$row, DataHandler $reference, array $removals = []);

    /**
     * Get the field name which will trigger processing
     *
     * @param array $row The record which triggered the processing
     * @return string|NULL
     */
    public function getFieldName(array $row);

    /**
     * Get the list_type value that will trigger processing
     *
     * @param array $row The record which triggered the processing
     * @return string|NULL
     */
    public function getTableName(array $row);

    /**
     * @param string $tableName
     * @return void
     */
    public function setTableName($tableName);

    /**
     * @param string $fieldName
     * @return void
     */
    public function setFieldName($fieldName);
}
