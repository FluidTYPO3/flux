<?php
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
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
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
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
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

    \FluidTYPO3\Flux\Integration\MultipleItemsProcFunc::register(
        'pages',
        'tx_fed_page_controller_action',
        \FluidTYPO3\Flux\Backend\PageLayoutDataProvider::class . '->addItems'
    );

    \FluidTYPO3\Flux\Integration\MultipleItemsProcFunc::register(
        'pages',
        'tx_fed_page_controller_action_sub',
        \FluidTYPO3\Flux\Backend\PageLayoutDataProvider::class . '->addItems'
    );

    $GLOBALS['TCA']['pages']['columns']['tx_fed_page_flexform']['displayCond'] = 'USER:' . \FluidTYPO3\Flux\Integration\FormEngine\UserFunctions::class . '->fluxFormFieldDisplayCondition:pages:tx_fed_page_flexform';
    $GLOBALS['TCA']['pages']['columns']['tx_fed_page_flexform_sub']['displayCond'] = 'USER:' . \FluidTYPO3\Flux\Integration\FormEngine\UserFunctions::class . '->fluxFormFieldDisplayCondition:pages:tx_fed_page_flexform_sub';

    $doktypes = '0,1,4';
    $doktypesOptionValue = \FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_DOKTYPES);
    if (is_scalar($doktypesOptionValue)) {
        $additionalDoktypes = trim((string) $doktypesOptionValue, ',');
        if (false === empty($additionalDoktypes)) {
            $doktypes .= ',' . $additionalDoktypes;
        }
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
