<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

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
