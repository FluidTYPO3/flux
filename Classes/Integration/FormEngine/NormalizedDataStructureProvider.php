<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Integration\NormalizedData\ImplementationInterface;
use FluidTYPO3\Flux\Integration\NormalizedData\ImplementationRegistry;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

class NormalizedDataStructureProvider implements FormDataProviderInterface
{
    /**
     * Add form data to result array
     *
     * @param array $result Initialized result array
     * @return array Result filled with more data
     */
    public function addData(array $result): array
    {
        foreach ($result['processedTca']['columns'] as $fieldName => $_) {
            $implementations = $this->resolveImplementationsForTableField($result['tableName'], $fieldName, $result['databaseRow']);
            foreach ($implementations as $implementation) {
                if ($implementation->appliesToTableField($result['tableName'], $fieldName)) {
                    $result = $implementation->getConverterForTableFieldAndRecord(
                        $result['tableName'],
                        $fieldName,
                        $result['databaseRow']
                    )->convertStructure($result);
                }
            }
        }
        return $result;
    }

    /**
     * @param string $table
     * @param array $record
     * @return ImplementationInterface[]
     */
    protected function resolveImplementationsForTableField(string $table, string $field, array $record): iterable
    {
        return ImplementationRegistry::resolveImplementations($table, $field, $record);
    }
}
