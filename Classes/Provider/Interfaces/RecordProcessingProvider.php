<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Interface RecordProcessingProvider
 *
 * Contract for Providers which may need to manipulate the database
 * record or trigger certain behaviors after it is saved.
 */
interface RecordProcessingProvider
{
    /**
     * Post-process record data for the table that this ConfigurationProvider
     * is attached to.
     *
     * @param string $operation TYPO3 operation identifier, i.e. "update", "new" etc.
     * @param integer $id The ID of the current record (which is sometimes not included in $row)
     * @param array $row the record that was modified
     * @param DataHandler $reference A reference to the DataHandler object that modified the record
     * @param array $removals Allows overridden methods to pass an array of fields to remove from the stored Flux value
     * @return true to stop processing other providers, false to continue processing other providers.
     */
    public function postProcessRecord(
        string $operation,
        int $id,
        array $row,
        DataHandler $reference,
        array $removals = []
    ): bool;
}
