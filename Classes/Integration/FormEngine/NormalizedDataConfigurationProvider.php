<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

class NormalizedDataConfigurationProvider implements FormDataProviderInterface
{
    public function addData(array $result): array
    {
        if ($result['tableName'] === 'flux_field') {
            $fieldValue = &$result['processedTca']['columns']['field_value'];
            $fieldValue['label'] = $result['databaseRow']['field_label'];
            if (!empty($result['databaseRow']['field_options'])) {
                $fieldValue['config'] = json_decode(
                    $result['databaseRow']['field_options'],
                    true
                ) ?? ['type' => 'passthrough'];
            }
        }
        return $result;
    }
}
