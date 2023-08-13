<?php
if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Enum\ExtensionOption::OPTION_PAGE_INTEGRATION)) {
    $GLOBALS['SiteConfiguration']['site']['columns']['flux_page_templates'] = [
        'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:siteConfiguration.pageTemplates',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'itemsProcFunc' => \FluidTYPO3\Flux\Integration\FormEngine\SiteConfigurationProviderItems::class . '->processPageTemplateItems',
        ],
    ];
}
$GLOBALS['SiteConfiguration']['site']['columns']['flux_content_types'] = [
    'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:siteConfiguration.contentTypes',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'itemsProcFunc' => \FluidTYPO3\Flux\Integration\FormEngine\SiteConfigurationProviderItems::class . '->processContentTypeItems',
    ],
];
$GLOBALS['SiteConfiguration']['site']['types'][0]['showitem'] .= ',--div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:siteConfiguration.tab,flux_page_templates,flux_content_types';
