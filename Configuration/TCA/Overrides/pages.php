<?php
defined('TYPO3_MODE') or die('Access denied.');

(function () {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluidpages') || !\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_PAGE_INTEGRATION)) {
        return;
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', [
        'tx_fed_page_controller_action' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_controller_action',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => 'FluidTYPO3\Flux\Backend\PageLayoutDataProvider->addItems',
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false
                    ]
                ]
            ]
        ],
        'tx_fed_page_controller_action_sub' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_controller_action_sub',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => 'FluidTYPO3\Flux\Backend\PageLayoutDataProvider->addItems',
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false
                    ]
                ]
            ]
        ],
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

    $userFunctionsClass = new \FluidTYPO3\Flux\Integration\FormEngine\UserFunctions();
    if (is_callable([$userFunctionsClass , 'fluxFormFieldDisplayCondition'])) {

        // Flux version is recent enough to support the custom displayCond from Flux that hides the entire "flex" field
        // if there are no fields in the DS it uses.
        $GLOBALS['TCA']['pages']['columns']['tx_fed_page_flexform']['displayCond'] = 'USER:' . $userFunctionsClass::class . '->fluxFormFieldDisplayCondition:pages:tx_fed_page_flexform';
        $GLOBALS['TCA']['pages']['columns']['tx_fed_page_flexform_sub']['displayCond'] = 'USER:' . $userFunctionsClass::class . '->fluxFormFieldDisplayCondition:pages:tx_fed_page_flexform_sub';
    }

    $doktypes = '0,1,4';
    $additionalDoktypes = trim(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_DOKTYPES), ',');
    if (false === empty($additionalDoktypes)) {
        $doktypes .= ',' . $additionalDoktypes;
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'pages',
        '--div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_layoutselect,tx_fed_page_controller_action,tx_fed_page_controller_action_sub',
        $doktypes
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'pages',
        '--div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_configuration,tx_fed_page_flexform,tx_fed_page_flexform_sub',
        $doktypes
    );
})();
