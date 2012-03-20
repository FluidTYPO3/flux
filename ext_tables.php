<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content', array(
		'tx_flux_column' => array (
			'exclude' => 1,
			'config' => array (
				'type' => 'passthrough',
			)
		),
	)
);

$TCA['tt_content']['columns']['colPos']['config']['items'][] = array('LLL:EXT:flux/locallang_db.xml:tt_content.tx_flux_container', '-42');
if (t3lib_extMgm::isLoaded('gridelements')) {
	$TCA['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = 'EXT:flux/Classes/Backend/ExtendedColumnPositionListItemsProcessor.php:Tx_Flux_Backend_ExtendedColumnPositionListItemsProcessor->itemsProcFunc';
} else {
	$TCA['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = 'EXT:flux/Classes/Backend/StandaloneColumnPositionListItemsProcessor.php:Tx_Flux_Backend_StandaloneColumnPositionListItemsProcessor->itemsProcFunc';
}



?>