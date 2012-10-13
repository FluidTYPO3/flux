<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

Tx_Extbase_Utility_Extension::registerPlugin($_EXTKEY, 'API', 'Flux API (do not use on pages)');

t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content', array(
		'tx_flux_column' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang_db.xml:tt_content.tx_flux_column',
			'config' => array (
				'type' => 'select',
				'default' => '',
				'itemsProcFunc' => 'EXT:flux/Classes/Backend/AreaListItemsProcessor.php:Tx_Flux_Backend_AreaListItemsProcessor->itemsProcFunc'
			)
		),
		'tx_flux_parent' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang_db.xml:tt_content.tx_flux_parent',
			'config' => array (
				'type' => 'select',
				'renderMode' => 'tree',
				'treeConfig' => array(
					'parentField' => 'tx_flux_parent',
				),
				'foreign_table' => 'tt_content',
				'foreign_table_where' => "AND tt_content.CType = 'fed_fce' AND tt_content.pid = '###CURRENT_PID###'",
				'default' => 0,
				'size' => 10,
				'maxitems' => 1,
			)
		),
		'tx_flux_children' => array (
			'exclude' => 0,
			'config' => array (
				'type' => 'inline',
				'foreign_table' => 'tt_content',
				'foreign_field' => 'tx_flux_parent',
				'foreign_sortby' => 'sorting',
			)
		),
	)
);

$TCA['tt_content']['columns']['colPos']['config']['items'][] = array('LLL:EXT:flux/locallang_db.xml:tt_content.tx_flux_container', '-42');
$TCA['tt_content']['ctrl']['requestUpdate'] .= ',tx_flux_parent';
if (t3lib_extMgm::isLoaded('gridelements')) {
	$TCA['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = 'EXT:flux/Classes/Backend/ExtendedColumnPositionListItemsProcessor.php:Tx_Flux_Backend_ExtendedColumnPositionListItemsProcessor->itemsProcFunc';
} else {
	$TCA['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = 'EXT:flux/Classes/Backend/StandaloneColumnPositionListItemsProcessor.php:Tx_Flux_Backend_StandaloneColumnPositionListItemsProcessor->itemsProcFunc';
}

t3lib_extMgm::addToAllTCAtypes('tt_content', 'tx_flux_parent, tx_flux_column');

Tx_Flux_Core::registerConfigurationProvider('Tx_Flux_Provider_Configuration_ContentObjectConfigurationProvider');
