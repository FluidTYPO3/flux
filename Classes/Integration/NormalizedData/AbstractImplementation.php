<?php
namespace FluidTYPO3\Flux\Integration\NormalizedData;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

abstract class AbstractImplementation
{
    protected array $settings = [];

    /**
     * Default implementation of constructor that's
     * valid according to the expected interface.
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Default implementation applies to any record
     * that's not empty, given that the other to
     * appliesTo() methods have both returned TRUE.
     */
    public function appliesToRecord(array $record): bool
    {
        return !empty($record);
    }
}
