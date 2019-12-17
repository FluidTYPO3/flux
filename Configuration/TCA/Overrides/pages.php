<?php
defined ('TYPO3_MODE') or die ('Access denied.');

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluidpages')) {
    return;
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['flux']);

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

if (is_callable([\FluidTYPO3\Flux\Integration\FormEngine\UserFunctions::class, 'fluxFormFieldDisplayCondition'])) {
    // Flux version is recent enough to support the custom displayCond from Flux that hides the entire "flex" field
    // if there are no fields in the DS it uses.
    $GLOBALS['TCA']['pages']['columns']['tx_fed_page_flexform']['displayCond'] = 'USER:' . \FluidTYPO3\Flux\Integration\FormEngine\UserFunctions::class . '->fluxFormFieldDisplayCondition:pages:tx_fed_page_flexform';
    $GLOBALS['TCA']['pages']['columns']['tx_fed_page_flexform_sub']['displayCond'] = 'USER:' . \FluidTYPO3\Flux\Integration\FormEngine\UserFunctions::class . '->fluxFormFieldDisplayCondition:pages:tx_fed_page_flexform_sub';
}

$doktypes = '0,1,4';
$additionalDoktypes = trim($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['doktypes'], ',');
if (FALSE === empty($additionalDoktypes)) {
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

unset($doktypes, $additionalDoktypes, $doktypeIcon);
