<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('FluidTYPO3.Flux', 'API', array('Flux' => 'renderChildContent'), array());

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'setup', '
	plugin.tx_flux.view {
		templateRootPath = EXT:flux/Resources/Private/Templates/
		partialRootPath = EXT:flux/Resources/Private/Partials/
		layoutRootPath = EXT:flux/Resources/Private/Layouts/
	}
	plugin.tx_flux.settings {
		flexform {
			rteDefaults = richtext[*]:rte_transform[mode=ts_css]
		}
	}
');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass']['flux'] = 'FluidTYPO3\Flux\Backend\DynamicFlexForm';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'FluidTYPO3\Flux\Backend\TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'FluidTYPO3\Flux\Backend\TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass'][] = 'FluidTYPO3\Flux\Backend\TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'FluidTYPO3\Flux\Backend\TceMain->clearCacheCommand';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources']['flux'] = 'FluidTYPO3\Flux\Backend\TypoScriptTemplate->preprocessIncludeStaticTypoScriptSources';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['flux'] = 'FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] = 'FluidTYPO3\Flux\Backend\Preview';


if (TRUE === class_exists('FluidTYPO3\Flux\Core')) {
	\FluidTYPO3\Flux\Core::registerConfigurationProvider('FluidTYPO3\Flux\Provider\ContentProvider');

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
$extbaseObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\Container\Container');
$extbaseObjectContainer->registerImplementation('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface', 'FluidTYPO3\Flux\Configuration\ConfigurationManager');
unset($extbaseObjectContainer);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['flux'] = 'FluidTYPO3\Flux\Hooks\WizardItemsHookSubscriber';
