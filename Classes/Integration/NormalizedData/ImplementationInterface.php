<?php
namespace FluidTYPO3\Flux\Integration\NormalizedData;

use FluidTYPO3\Flux\Integration\NormalizedData\Converter\ConverterInterface;

interface ImplementationInterface
{
    /**
     * Constructor must receive a single "settings" argument
     * and nothing else. Defined in this interface to prevent
     * implementations from breaking this rule but still
     * allowing an implementation to not use any settings.
     *
     * @param array $settings
     */
    public function __construct(array $settings = []);

    /**
     * Must return TRUE only if this implementation applies
     * to the table and field provided. Each implementation
     * can then allow configuring whether or not it should
     * apply to a given table/field in any way desired.
     *
     * @param string $table
     * @param string $field
     * @return boolean
     */
    public function appliesToTableField(string $table, string $field): bool;

    /**
     * Must return TRUE only if this implementation applies
     * to the table provided. Each implementation can then
     * allow configuring whether or not it should apply to
     * a given table in any way desired.
     *
     * @param string $table
     * @return boolean
     */
    public function appliesToTable(string $table): bool;

    /**
     * Must return TRUE only if this implementation applies
     * to the record provided. Is only called if the other
     * two appliesTo() methods return TRUE.
     *
     * @param array $record
     * @return boolean
     */
    public function appliesToRecord(array $record): bool;

    /**
     * Returns a Converter that does the actual processing
     * required by this Implementation. Requires the table,
     * field and record as input parameters, allowing an
     * Implementation to return any number of different
     * Converters based on these identifying values.
     *
     * @param string $table
     * @param string $field
     * @param array $record
     * @return ConverterInterface
     */
    public function getConverterForTableFieldAndRecord(string $table, string $field, array $record): ConverterInterface;
}
