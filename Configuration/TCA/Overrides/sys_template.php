<?php
defined ('TYPO3_MODE') or die ('Access denied.');

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluidpages')) {
    return;
}

if (class_exists(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('flux');
} else {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] ?? unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['flux']);
}

if (!($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['autoload'] ?? true)) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('flux', 'Configuration/TypoScript', 'Flux PAGE rendering');
}
