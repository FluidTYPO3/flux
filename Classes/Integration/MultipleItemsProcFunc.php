<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MultipleItemsProcFunc
{
    public static function register(string $table, string $field, ?string $additionalFunction = null): void
    {
        $existingFunction = $GLOBALS['TCA'][$table]['columns'][$field]['config']['itemsProcFunc'] ?? null;
        $newFunction = static::class . '->execute';

        $GLOBALS['TCA'][$table]['multipleItemsProcessingFunctions'][$field] = array_values(
            array_filter(
                array_merge(
                    $GLOBALS['TCA'][$table]['multipleItemsProcessingFunctions'][$field] ?? [],
                    [
                        $existingFunction !== $newFunction ? $existingFunction : null,
                        $additionalFunction,
                    ]
                )
            )
        );
        $GLOBALS['TCA'][$table]['columns'][$field]['config']['itemsProcFunc'] = $newFunction;
    }

    public function execute(array &$parameters, FormDataProviderInterface $formDataProvider): void
    {
        $table = $parameters['table'];
        $field = $parameters['field'];
        $processors = $GLOBALS['TCA'][$table]['multipleItemsProcessingFunctions'][$field] ?? [];
        foreach (array_filter($processors) as $functionReference) {
            GeneralUtility::callUserFunction($functionReference, $parameters, $formDataProvider);
        }
    }
}
