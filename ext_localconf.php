<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

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

if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'] ?? [];

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

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

    // Small override for record-localize controller to manipulate the record listing to provide child records in list
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\LocalizationController::class]['className'] = \FluidTYPO3\Flux\Integration\Overrides\LocalizationController::class;

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

    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        \TYPO3\CMS\Backend\Controller\EditDocumentController::class,
        'initAfter',
        \FluidTYPO3\Flux\Integration\HookSubscribers\EditDocumentController::class,
        'requireColumnPositionJavaScript'
    );

    if (TRUE === class_exists(\FluidTYPO3\Flux\Core::class)) {


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
}
