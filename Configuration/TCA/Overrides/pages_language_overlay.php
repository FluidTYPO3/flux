<?php
defined('TYPO3_MODE') or die('Access denied.');

(function () {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluidpages')
        || !\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_PAGE_LANGUAGE_OVERLAY)
        || !\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_PAGE_INTEGRATION)
    ) {
        return;
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages_language_overlay', [
        'tx_fed_page_flexform' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_flexform',
            'config' => [
                'type' => 'flex',
                'ds' => [
                    'default' => '<T3DataStructure><ROOT><el></el></ROOT></T3DataStructure>'
                ]
            ]
        ],
        'tx_fed_page_flexform_sub' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_flexform_sub',
            'config' => [
                'type' => 'flex',
                'ds' => [
                    'default' => '<T3DataStructure><ROOT><el></el></ROOT></T3DataStructure>'
                ]
            ]
        ],
    ]);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'pages_language_overlay',
        '--div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_configuration,tx_fed_page_flexform,tx_fed_page_flexform_sub'
    );

    $userFunctionsClass = new \FluidTYPO3\Flux\Integration\FormEngine\UserFunctions();
    if (is_callable([$userFunctionsClass, 'fluxFormFieldDisplayCondition'])) {
        // Flux version is recent enough to support the custom displayCond from Flux that hides the entire "flex" field
        // if there are no fields in the DS it uses.
        $GLOBALS['TCA']['pages_language_overlay']['columns']['tx_fed_page_flexform']['displayCond'] = 'USER:' . $userFunctionsClass . '->fluxFormFieldDisplayCondition:pages_language_overlay:tx_fed_page_flexform';
        $GLOBALS['TCA']['pages_language_overlay']['columns']['tx_fed_page_flexform_sub']['displayCond'] = 'USER:' . $userFunctionsClass . '->fluxFormFieldDisplayCondition:pages_language_overlay:tx_fed_page_flexform_sub';
    }
})();
