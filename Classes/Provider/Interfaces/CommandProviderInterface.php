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
 * Interface CommandProviderInterface
 *
 * Contract for Providers which listen and react to
 * DataHandler commands being dispatched in the BE.
 *
 * @deprecated Will be removed in Flux 10.0, please use DataHandler hooks directly
 */
interface CommandProviderInterface
{
    /**
     * Post-process database operation for the table that this ConfigurationProvider
     * is attached to.
     *
     * @abstract
     * @param string $status TYPO3 operation identifier, i.e. "new" etc.
     * @param integer $id The ID of the current record (which is sometimes now included in $row
     * @param array $row The record by reference. Changing fields' values changes the record's values
     *                   just before saving after operation
     * @param DataHandler $reference A reference to the DataHandler object that is currently performing the operation
     * @return void
     */
    public function postProcessDatabaseOperation($status, $id, &$row, DataHandler $reference);

    /**
     * Pre-process a command executed on a record form the table this ConfigurationProvider
     * is attached to.
     *
     * @abstract
     * @param string $command
     * @param integer $id
     * @param array $row
     * @param integer $relativeTo
     * @param DataHandler $reference
     * @return void
     */
    public function preProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference);

    /**
     * Post-process a command executed on a record form the table this ConfigurationProvider
     * is attached to.
     *
     * @abstract
     * @param string $command
     * @param integer $id
     * @param array $row
     * @param integer $relativeTo
     * @param DataHandler $reference
     * @return void
     */
    public function postProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference);

    /**
     * Perform operations upon clearing cache(s)
     *
     * @param array $command
     * @return void
     */
    public function clearCacheCommand($command = []);
}
