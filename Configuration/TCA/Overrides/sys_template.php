<?php
defined ('TYPO3_MODE') or die ('Access denied.');

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluidpages')) {
    return;
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['flux']);

if (!($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['autoload'] ?? true)) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('flux', 'Configuration/TypoScript', 'Flux PAGE rendering');
}
