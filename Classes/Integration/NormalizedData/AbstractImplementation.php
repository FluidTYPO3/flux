<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\NormalizedData;

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
