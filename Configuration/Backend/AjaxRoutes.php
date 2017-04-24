<?php

/**
 * Definitions for routes provided by EXT:backend
 * Contains all AJAX-based routes for entry points
 *
 * Currently the "access" property is only used so no token creation + validation is made
 * but will be extended further.
 */
use FluidTYPO3\Flux\Backend\Controller\LocalizationController;

return [

    // Get languages in page and colPos
    'languages_page_colpos' => [
        'path' => '/records/localize/get-languages',
        'target' => LocalizationController::class . '::getUsedLanguagesInPageAndColumn'
    ],

    // Get summary of records to localize
    'records_localize_summary' => [
        'path' => '/records/localize/summary',
        'target' => LocalizationController::class . '::getRecordLocalizeSummary'
    ],
    // Localize the records
    'records_localize' => [
        'path' => '/records/localize',
        'target' => LocalizationController::class . '::localizeRecords'
    ]

];
