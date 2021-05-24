<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

(function () use ($_EXTCONF) {
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['flux'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['flux'] = array(
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
            'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
            'groups' => array('system'),
            'options' => [
                'defaultLifetime' => 2592000,
            ],
        );
    }

    \FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::initialize($_EXTCONF);

    if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
        // Globally registered fluid namespace
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['flux'] = ['FluidTYPO3\\Flux\\ViewHelpers'];

        // FormEngine integration between TYPO3 forms and Flux Providers
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\FluidTYPO3\Flux\Integration\FormEngine\ProviderProcessor::class] = array(
            'depends' => array(
                \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
                \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class,
                \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessShowitem::class
            ),
            'before' => array(
                \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class
            )
        );

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

        // Small override for record-localize controller to manipulate the record listing to provide child records in list
        if (!class_exists(\TYPO3\CMS\Backend\Controller\Event\AfterPageColumnsSelectedForLocalizationEvent::class)) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\LocalizationController::class]['className'] = \FluidTYPO3\Flux\Integration\Overrides\LocalizationController::class;
        }

        // Various hooks needed to operate Flux
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\DynamicFlexForm::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber::class . '->clearCacheCommand';
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\Preview::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\WizardItems::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\ContentIcon::class . '->addSubIcon';
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used']['flux'] =
            \FluidTYPO3\Flux\Integration\HookSubscribers\ContentUsedDecision::class . '->isContentElementUsed';

        // The following is a dual registration of the same TCA-manipulating hook; the reason for registering it twice for two
        // different hooks is that extTablesInclusion-PostProcessing does not get executed in FE, resulting in errors due to
        // features provided by this hook subscriber not being loaded.
        if (TYPO3_MODE === 'BE') {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['flux'] =
                \FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor::class;
        } else {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources'][] =
                \FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor::class . '->includeStaticTypoScriptHook';
        }

        $contentTypeManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\FluidTYPO3\Flux\Content\ContentTypeManager::class);
        foreach ($contentTypeManager->fetchContentTypes() as $contentType) {
            $contentTypeManager->registerTypeDefinition($contentType);
            \FluidTYPO3\Flux\Core::registerTemplateAsContentType(
                $contentType->getExtensionIdentity(),
                $contentType->getTemplatePathAndFilename(),
                $contentType->getContentTypeName(),
                $contentType->getProviderClassName()
            );
        }

        /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $signalSlotDispatcher->connect(
            \TYPO3\CMS\Backend\Controller\EditDocumentController::class,
            'initAfter',
            \FluidTYPO3\Flux\Integration\HookSubscribers\EditDocumentController::class,
            'requireColumnPositionJavaScript'
        );

        if (true === class_exists(\FluidTYPO3\Flux\Core::class)) {
            \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Content\ContentTypeProvider::class);
            \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentGridProvider::class);

            // native Outlets, replaceable by short name in subsequent registerOutlet() calls by adding second argument (string, name of type)
            \FluidTYPO3\Flux\Core::registerOutlet('standard');

            // native Pipes, replaceable by short name in subsequent registerPipe() calls by adding second argument (string, name of type)
            \FluidTYPO3\Flux\Core::registerPipe('standard');
            \FluidTYPO3\Flux\Core::registerPipe('controller');
            \FluidTYPO3\Flux\Core::registerPipe('email');
            \FluidTYPO3\Flux\Core::registerPipe('flashMessage');
            \FluidTYPO3\Flux\Core::registerPipe('typeConverter');
        }

        if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_PAGE_INTEGRATION)) {
            \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Provider\PageProvider::class);
            \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Provider\SubPageProvider::class);
        }
        if (version_compare(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('core'), 9.0, '<')) {
            \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Provider\PageLanguageOverlayProvider::class);
            \FluidTYPO3\Flux\Core::registerConfigurationProvider(\FluidTYPO3\Flux\Provider\SubPageLanguageOverlayProvider::class);
        }

        if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluidpages') && \FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_PAGE_INTEGRATION)) {
            if (version_compare(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('core'), 10.4, '>=')) {
                $pageControllerName = \FluidTYPO3\Flux\Controller\PageController::class;
                $pageControllerExtensionName = 'Flux';
            } else {
                $pageControllerName = 'Page';
                $pageControllerExtensionName = 'FluidTYPO3.Flux';
            }

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
                $pageControllerExtensionName,
                'Page',
                [
                    $pageControllerName => 'render,error',
                ],
                [],
                \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_PLUGIN
            );

            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider']['flux'] = \FluidTYPO3\Flux\Backend\BackendLayoutDataProvider::class;
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][] = \FluidTYPO3\Flux\Integration\HookSubscribers\PagePreviewRenderer::class . '->render';

            if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_AUTOLOAD)) {
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Configuration/TypoScript/constants.txt')));
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Configuration/TypoScript/setup.txt')));
            }

            if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_PAGE_LANGUAGE_OVERLAY)) {
                $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields'] .= ',tx_fed_page_flexform,tx_fed_page_flexform_sub';
            }

            $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ($GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] == '' ? '' : ',') .
                'tx_fed_page_controller_action,tx_fed_page_controller_action_sub,tx_fed_page_flexform,tx_fed_page_flexform_sub,';
        }
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
