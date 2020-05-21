<?php
defined ('TYPO3_MODE') or die ('Access denied.');

(function() {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluidpages')) {
        return;
    }

    if (!\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_AUTOLOAD)) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('flux', 'Configuration/TypoScript', 'Flux PAGE rendering');
    }
})();
