<?php

$conf = isset($_EXTCONF) ? $_EXTCONF : null;

(function () use ($conf) {
    $coreVersion = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version();

    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['flux'] ?? null)) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['flux'] = array(
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
            'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
            'groups' => array('system'),
            'options' => [
                'defaultLifetime' => 2592000,
            ],
        );
    }

    \FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::initialize($conf);

    if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Enum\ExtensionOption::OPTION_PAGE_INTEGRATION)) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Flux',
            'Page',
            [
                \FluidTYPO3\Flux\Controller\PageController::class => 'render,error',
            ]
        );

        \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Provider\PageProvider::class);

        if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Enum\ExtensionOption::OPTION_AUTOLOAD)) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Configuration/TypoScript/constants.txt')));
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Configuration/TypoScript/setup.txt')));
        }

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider']['flux'] = \FluidTYPO3\Flux\Backend\BackendLayoutDataProvider::class;

        if (version_compare($coreVersion, '12', '<')) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][] = \FluidTYPO3\Flux\Integration\HookSubscribers\PagePreviewRenderer::class . '->render';
        }

        if (version_compare($coreVersion, '13.4', '<')) {
            $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ($GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] == '' ? '' : ',') .
                'tx_fed_page_controller_action,tx_fed_page_controller_action_sub,tx_fed_page_flexform,tx_fed_page_flexform_sub,';
        }
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\FluidTYPO3\Flux\Updates\MigrateColPosWizard::class]
        = \FluidTYPO3\Flux\Updates\MigrateColPosWizard::class;

    // Globally registered fluid namespace
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['flux'] = ['FluidTYPO3\\Flux\\ViewHelpers'];

    // FormEngine integration between TYPO3 forms and Flux Providers
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\FluidTYPO3\Flux\Integration\FormEngine\ProviderProcessor::class] = [
        'before' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexProcess::class,
        ],
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsOverrides::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessShowitem::class
        ],
    ];

    // FormEngine integration for custom TCA field types used by Flux
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry']['fluxPageLayoutSelector'] = [
        'nodeName' => 'fluxPageLayoutSelector',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\PageLayoutSelector::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry']['fluxColumnPosition'] = [
        'nodeName' => 'fluxColumnPosition',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\ColumnPositionNode::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry']['fluxHtmlOutput'] = [
        'nodeName' => 'fluxHtmlOutput',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\HtmlOutputNode::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry']['fluxClearValue'] = [
        'nodeName' => 'fluxClearValue',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\ClearValueWizard::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry']['fluxProtectValue'] = [
        'nodeName' => 'fluxProtectValue',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\ProtectValueWizard::class,
    ];

    if (version_compare($coreVersion, '13.4', '<')) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class]['className'] = version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '11.0', '<')
            ? \FluidTYPO3\Flux\Integration\Overrides\LegacyChimeraConfigurationManager::class
            : \FluidTYPO3\Flux\Integration\Overrides\ChimeraConfigurationManager::class;
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
        \FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] =
        \FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber::class;

    \FluidTYPO3\Flux\Utility\CompatibilityRegistry::register(
        \FluidTYPO3\Flux\Builder\ContentTypeBuilder::DEFAULT_SHOWITEM,
        [
            '10.4.0' => '
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

    if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_FLEXFORM_TO_IRRE)) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\FluidTYPO3\Flux\Integration\FormEngine\NormalizedDataStructureProvider::class] = [
            'before' => [
                \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class,
            ],
        ];

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\FluidTYPO3\Flux\Integration\FormEngine\NormalizedDataConfigurationProvider::class] = [
            'before' => [
                \TYPO3\CMS\Backend\Form\FormDataProvider\InlineOverrideChildTca::class,
            ],
        ];

        \FluidTYPO3\Flux\Integration\NormalizedData\ImplementationRegistry::registerImplementation(
            \FluidTYPO3\Flux\Integration\NormalizedData\FlexFormImplementation::class
        );

        \FluidTYPO3\Flux\Integration\NormalizedData\FlexFormImplementation::registerForTableAndField('tt_content', 'pi_flexform');
    }
})();
