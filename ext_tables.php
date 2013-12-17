<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

$TCA['tt_content']['columns']['colPos']['config']['items'][] = array(
	'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_container',
	\FluidTYPO3\Flux\Service\ContentService::COLPOS_FLUXCONTENT
);

\FluidTYPO3\Flux\Core::registerConfigurationProvider('FluidTYPO3\Flux\Provider\ContentProvider');
