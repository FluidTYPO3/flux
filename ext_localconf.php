<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

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
		object {
			image {
				richTextCaptions = 1
				sliderWidth = 600
				sliderStep = 5
				maxWidth = 1920
				maxHeight = 1080
			}
			video {
				richTextCaptions = 1
			}
			pages {
				minItems = 0
				maxItems = 20
				size = 4
			}
			content {
				minItems = 0
				maxItems = 20
				size = 4
			}
			file {
				richTextCaptions = 1
			}
		}
	}
');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass']['flux'] = 'EXT:flux/Classes/Backend/DynamicFlexForm.php:Tx_Flux_Backend_DynamicFlexForm';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['flux'] = 'EXT:flux/Classes/Backend/TceMain.php:Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['flux'] = 'EXT:flux/Classes/Backend/TceMain.php:Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass']['flux'] = 'EXT:flux/Classes/Backend/TceMain.php:Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'EXT:flux/Classes/Backend/TceMain.php:&Tx_Flux_Backend_TceMain->clearCacheCommand';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources']['flux'] = 'EXT:flux/Classes/Backend/TypoScriptTemplate.php:Tx_Flux_Backend_TypoScriptTemplate->preprocessIncludeStaticTypoScriptSources';


/*
 * The following code fixes the following issue:
 * https://github.com/FluidTYPO3/flux/issues/19
 * Basically, the implementation of preProcess() changes between TYPO3 v4 and v6,
 * and this code includes the appropriate class file to make the hook work on both
 * platforms.
 */
if (Tx_Flux_Utility_Version::assertCoreVersionIsAtLeastSixPointZero()) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] = 'EXT:flux/Classes/Backend/PreviewSix.php:Tx_Flux_Backend_PreviewSix';
} else {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] = 'EXT:flux/Classes/Backend/Preview.php:Tx_Flux_Backend_Preview';
}

Tx_Flux_Core::registerConfigurationProvider('Tx_Flux_Provider_Configuration_ContentObjectConfigurationProvider');

/*
 * The following stub adds VH aliases for 4.5 to use: f:format.raw -> f:escape, f:format.htmlentities -> f:escape
 */
if (0 === strpos(TYPO3_version, '4.5')) {
	class Tx_Fluid_ViewHelpers_Format_RawViewHelper extends Tx_Fluid_ViewHelpers_EscapeViewHelper {

	}
	class Tx_Fluid_ViewHelpers_Format_HtmlentitiesViewHelper extends Tx_Fluid_ViewHelpers_EscapeViewHelper {

	}
}
