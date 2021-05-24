<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

class NormalizedDataConfigurationProvider implements FormDataProviderInterface
{
    /**
     * Add form data to result array
     *
     * @param array $result Initialized result array
     * @return array Result filled with more data
     */
    public function addData(array $result)
    {
        if ($result['tableName'] === 'flux_field') {
            if (!empty($result['databaseRow']['field_options'])) {
                $result['processedTca']['columns']['field_value']['config'] = json_decode($result['databaseRow']['field_options'], true) ?? ['type' => 'passthrough'];
            }
            $result['processedTca']['columns']['field_value']['label'] = $result['databaseRow']['field_label'];
        }
        return $result;
    }
}
