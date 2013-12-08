<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin($_EXTKEY, 'API', array('Flux' => 'renderChildContent'), array());

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

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass']['flux'] = 'Tx_Flux_Backend_DynamicFlexForm';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass'][] = 'Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'Tx_Flux_Backend_TceMain->clearCacheCommand';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources']['flux'] = 'Tx_Flux_Backend_TypoScriptTemplate->preprocessIncludeStaticTypoScriptSources';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['flux'] = 'Tx_Flux_Backend_TableConfigurationPostProcessor';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] = 'Tx_Flux_Backend_Preview';
\FluidTYPO3\Flux\Core::registerConfigurationProvider('FluidTYPO3\Flux\Provider\ContentProvider');

// native Outlets, replaceable by short name in subsequent registerOutlet() calls by adding second argument (string, name of type)
\FluidTYPO3\Flux\Core::registerOutlet('standard');

// native Pipes, replaceable by short name in subsequent registerPipe() calls by adding second argument (string, name of type)
\FluidTYPO3\Flux\Core::registerPipe('standard');
\FluidTYPO3\Flux\Core::registerPipe('controller');
\FluidTYPO3\Flux\Core::registerPipe('email');
\FluidTYPO3\Flux\Core::registerPipe('flashMessage');
\FluidTYPO3\Flux\Core::registerPipe('typeConverter');
