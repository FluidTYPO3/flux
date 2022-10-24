<?php
namespace FluidTYPO3\Flux\Integration\NormalizedData;

abstract class AbstractImplementation
{

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * Default implementation of constructor that's
     * valid according to the expected interface.
     *
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Default implementation applies to any record
     * that's not empty, given that the other to
     * appliesTo() methods have both returned TRUE.
     *
     * @param array $record
     * @return boolean
     */
    public function appliesToRecord(array $record): bool
    {
        return !empty($record);
    }

}
