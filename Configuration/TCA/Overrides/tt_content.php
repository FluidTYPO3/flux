<?php
defined ('TYPO3_MODE') or die ('Access denied.');

\FluidTYPO3\Flux\Utility\CompatibilityRegistry::register(
    \FluidTYPO3\Flux\Integration\ContentTypeBuilder::DEFAULT_SHOWITEM,
    [
        '8.7.0' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,
                pi_flexform,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories, 
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription'
    ]
);

// Initialize the TCA needed by "template as CType" integrations
\FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor::spoolQueuedContentTypeTableConfigurations(
    \FluidTYPO3\Flux\Core::getQueuedContentTypeRegistrations()
);

$GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'] =
    \FluidTYPO3\Flux\Integration\Overrides\BackendLayoutView::class . '->colPosListItemProcFunc';
$GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['label'] = 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform';
