<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\NormalizedData\Converter;

interface ConverterInterface
{
    /**
     * Constructor of Converters must accept exactly
     * these three parameters (and initialize itself
     * based on them).
     */
    public function __construct(string $table, string $field, array $record);

    /**
     * Modify the input FormEngine structure, returning
     * the modified array.
     */
    public function convertStructure(array $structure): array;

    /**
     * Convert the input data (original data stored in
     * record) to desired output type. Output must either
     * be an array or implement ArrayAccess.
     */
    public function convertData(array $data): array;
}
