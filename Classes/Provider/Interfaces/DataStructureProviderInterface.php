<?php
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Interface DataStructureProviderInterface
 *
 * Contract for Providers which generate or manipulate
 * data structures (FlexForm DS, TCA). Must also be
 * implemented by Providers which must be capable of
 * returning FlexForm variables (which is required if
 * for example the Provider transforms data types).
 */
interface DataStructureProviderInterface
{
    /**
     * Processes the table configuration (TCA) for the table associated
     * with this Provider, as determined by the trigger() method. Gets
     * passed an instance of the record being edited/created along with
     * the current configuration array - and must return a complete copy
     * of the configuration array manipulated to the Provider's needs.
     *
     * @param array $row The record being edited/created
     * @param array $configuration
     * @return array The large FormEngine configuration array - see FormEngine documentation!
     */
    public function processTableConfiguration(array $row, array $configuration);

    /**
     * Post-process the TCEforms DataStructure for a record associated
     * with this ConfigurationProvider
     *
     * @param array $row
     * @param mixed $dataStructure Array or string; should only be processed if argument is an array
     * @param array $conf
     * @return void
     */
    public function postProcessDataStructure(array &$row, &$dataStructure, array $conf);

    /**
     * Converts the contents of the provided row's Flux-enabled field,
     * at the same time running through the inheritance tree generated
     * by getInheritanceTree() in order to apply inherited values.
     *
     * @param array $row
     * @return array
     */
    public function getFlexFormValues(array $row);
}
