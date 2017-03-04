<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass']['flux'] = \FluidTYPO3\Flux\Backend\DynamicFlexForm::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing']['flux'] = \FluidTYPO3\Flux\Backend\DynamicFlexForm::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \FluidTYPO3\Flux\Backend\TceMain::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \FluidTYPO3\Flux\Backend\TceMain::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass'][] = \FluidTYPO3\Flux\Backend\TceMain::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = \FluidTYPO3\Flux\Backend\TceMain::class . '->clearCacheCommand';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources']['flux'] = \FluidTYPO3\Flux\Backend\TypoScriptTemplate::class . '->preprocessIncludeStaticTypoScriptSources';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] = \FluidTYPO3\Flux\Backend\Preview::class;

// The following is a dual registration of the same TCA-manipulating hook; the reason for registering it twice for two
// different hooks is that extTablesInclusion-PostProcessing does not get executed in FE, resulting in errors due to
// features provided by this hook subscriber not being loaded. We use the includeStaticTypoScriptSourcesAtEnd since this
// is the absolutely last possible place we can configure plugins before causing a "Content type XYZ has no rendering
// definition" error in FE output.
if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['flux'] = \FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor::class;
} else {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][] = \FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor::class . '->processData';
}


if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

    // Configure the CompatibilityRegistry so it will return the right values based on TYPO3 version:
    // PreviewView class name (expecting needed changes on TYPO3 8.6+)
    \FluidTYPO3\Flux\Utility\CompatibilityRegistry::register(
        \FluidTYPO3\Flux\View\PreviewView::class . '->drawPasteIcon',
        array(
            '7.6.0' => true,
            '8.6.0' => false
        )
    );

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('FluidTYPO3.Flux', 'API', array('Flux' => 'renderChildContent'), array());

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'setup', '
		plugin.tx_flux.view {
			templateRootPath = EXT:flux/Resources/Private/Templates/
			partialRootPath = EXT:flux/Resources/Private/Partials/
			layoutRootPath = EXT:flux/Resources/Private/Layouts/
		}
		plugin.tx_flux.settings {
			flexform {
				rteDefaults = richtext:rte_transform[flag=rte_enabled|mode=ts_css]
			}
		}
	');

	if (TRUE === class_exists(\FluidTYPO3\Flux\Core::class)) {

		// native Outlets, replaceable by short name in subsequent registerOutlet() calls by adding second argument (string, name of type)
		\FluidTYPO3\Flux\Core::registerOutlet('standard');

		// native Pipes, replaceable by short name in subsequent registerPipe() calls by adding second argument (string, name of type)
		\FluidTYPO3\Flux\Core::registerPipe('standard');
		\FluidTYPO3\Flux\Core::registerPipe('controller');
		\FluidTYPO3\Flux\Core::registerPipe('email');
		\FluidTYPO3\Flux\Core::registerPipe('flashMessage');
		\FluidTYPO3\Flux\Core::registerPipe('typeConverter');
	}

	/** @var $extbaseObjectContainer \TYPO3\CMS\Extbase\Object\Container\Container */
	$extbaseObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
	$extbaseObjectContainer->registerImplementation(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class, \FluidTYPO3\Flux\Configuration\ConfigurationManager::class);
	unset($extbaseObjectContainer);
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['flux'] = \FluidTYPO3\Flux\Hooks\WizardItemsHookSubscriber::class;
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']['flux'] =  \FluidTYPO3\Flux\Hooks\ContentIconHookSubscriber::class . '->addSubIcon';

	if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['listNestedContent']) && !(boolean)$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['listNestedContent']) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable']['flux'] = \FluidTYPO3\Flux\Hooks\RecordListGetTableHookSubscriber::class;
	}

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\FluidTYPO3\Flux\Backend\FormEngine\ProviderProcessor::class] = array(
		'depends' => array(
			\TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
			\TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class,
			\TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessShowitem::class
		),
		'before' => array(
			\TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class
		)
	);
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['flux'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['flux'] = array(
		'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
		'groups' => array('system'),
		'options' => [
			'defaultLifetime' => 2592000,
		],
	);
}
