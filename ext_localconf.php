<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

if (TRUE === (boolean) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['reportErrors']) {
	// Note: constants used only when Flux is configured to report errors remotely. Reports
	// are completely anonymised, containing only exception code and version information.
	define('FLUX_REMOTE_REPORT_ERROR', 'http://fedext.net/report/error.php');
	// Note: monitored-exceptions.json contains a very simple list of Exception codes. The
	// JSON file is downloaded and cached locally in typo3temp with a TTL of 24 hours.
	define('FLUX_REMOTE_REPORT_MONITORED_EXCEPTIONS', 'http://fedext.net/report/monitored-exceptions.json');
}

// Register CLI
if (TYPO3_MODE === 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['flux'] = array(
		'EXT:flux/Scripts/CommandLineLauncher.php',
		'_CLI_lowlevel'
	);
}

Tx_Extbase_Utility_Extension::configurePlugin($_EXTKEY, 'API', array('Flux' => 'renderChildContent'), array());

t3lib_extMgm::addTypoScript($_EXTKEY, 'setup', '
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
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources']['flux'] = 'EXT:flux/Classes/Backend/TypoScriptTemplate.php:Tx_Flux_Backend_TypoScriptTemplate->preprocessIncludeStaticTypoScriptSources';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['flux'] = 'EXT:flux/Classes/Backend/TableConfigurationPostProcessor.php:Tx_Flux_Backend_TableConfigurationPostProcessor';

/*
 * The following code fixes the following issue:
 * https://github.com/FluidTYPO3/flux/issues/19
 * Basically, the implementation of preProcess() changes between TYPO3 v4 and v6,
 * and this code includes the appropriate class file to make the hook work on both
 * platforms.
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] = 'EXT:flux/Classes/Backend/Preview.php:Tx_Flux_Backend_Preview';

Tx_Flux_Core::registerConfigurationProvider('Tx_Flux_Provider_ContentProvider');

/*
 * The following stub adds VH aliases for 4.5 to use: f:format.raw -> f:escape, f:format.htmlentities -> f:escape
 */
if (0 === strpos(TYPO3_version, '4.5')) {
	class Tx_Fluid_ViewHelpers_Format_RawViewHelper extends Tx_Fluid_ViewHelpers_EscapeViewHelper {

	}
	class Tx_Fluid_ViewHelpers_Format_HtmlentitiesViewHelper extends Tx_Fluid_ViewHelpers_EscapeViewHelper {

	}
}
