<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);
#$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['mod1']['renderPreviewContent']['fed_fce'] = 'EXT:flux/Classes/Backend/TemplaVoilaPreview.php:Tx_Flux_Backend_TemplaVoilaPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass']['flux'] = 'EXT:flux/Classes/Backend/DynamicFlexForm.php:Tx_Flux_Backend_DynamicFlexForm';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] = 'EXT:flux/Classes/Backend/Preview.php:Tx_Flux_Backend_Preview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['flux'] = 'EXT:flux/Classes/Backend/TceMain.php:Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['flux'] = 'EXT:flux/Classes/Backend/TceMain.php:Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass']['flux'] = 'EXT:flux/Classes/Backend/TceMain.php:Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list.inc']['makeQueryArray']['flux'] = 'EXT:flux/Classes/Backend/MakeQueryArray.php:Tx_Flux_Backend_MakeQueryArray';

// register CLI
if (TYPO3_MODE === 'BE') {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['flux'] = array(
			'EXT:flux/Scripts/CommandLineLauncher.php',
			'_CLI_lowlevel'
		);
}

?>
