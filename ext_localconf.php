<?php

$conf = isset($_EXTCONF) ? $_EXTCONF : null;

(function () use ($conf) {
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
            ],
            [],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_PLUGIN
        );

        \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Provider\PageProvider::class);

        if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Enum\ExtensionOption::OPTION_AUTOLOAD)) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Configuration/TypoScript/constants.txt')));
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Configuration/TypoScript/setup.txt')));
        } else {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('flux', 'Configuration/TypoScript', 'Flux PAGE rendering');
        }

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider']['flux'] = \FluidTYPO3\Flux\Backend\BackendLayoutDataProvider::class;

        if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '12', '<')) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][] = \FluidTYPO3\Flux\Integration\HookSubscribers\PagePreviewRenderer::class . '->render';
        }

        $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ($GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] == '' ? '' : ',') .
            'tx_fed_page_controller_action,tx_fed_page_controller_action_sub,tx_fed_page_flexform,tx_fed_page_flexform_sub,';
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
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1575276512] = [
        'nodeName' => 'fluxContentTypeValidator',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\ContentTypeValidatorNode::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1575277301] = [
        'nodeName' => 'fluxTemplateSourceDumper',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\TemplateSourceDumperNode::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1578613431] = [
        'nodeName' => 'fluxColumnPosition',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\ColumnPositionNode::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1593341585] = [
        'nodeName' => 'fluxHtmlOutput',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\HtmlOutputNode::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1661337814] = [
        'nodeName' => 'fluxClearValue',
        'priority' => 40,
        'class' => \FluidTYPO3\Flux\Integration\FormEngine\ClearValueWizard::class,
    ];

    // Small override for record-localize controller to manipulate the record listing to provide child records in list
    if (!class_exists(\TYPO3\CMS\Backend\Controller\Event\AfterPageColumnsSelectedForLocalizationEvent::class)) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\LocalizationController::class]['className'] = \FluidTYPO3\Flux\Integration\Overrides\LocalizationController::class;
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
        \FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] =
        \FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] =
        \FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber::class . '->clearCacheCommand';

    if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '12', '<')) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\ContentUsedDecision::class . '->isContentElementUsed';
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\DynamicFlexForm::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\Preview::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\WizardItems::class;
    }

    if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '11', '<')) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\ContentIcon::class . '->addSubIcon';
        // The following is a dual registration of the same TCA-manipulating hook; the reason for registering it twice for two
        // different hooks is that extTablesInclusion-PostProcessing does not get executed in FE, resulting in errors due to
        // features provided by this hook subscriber not being loaded.
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources'][] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\StaticTypoScriptInclusion::class . '->includeStaticTypoScriptHook';
    }

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

    if (class_exists(\FluidTYPO3\Flux\Core::class)) {
        \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Content\ContentTypeProvider::class);
        \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentGridProvider::class);
    }

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
