<?php
if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Enum\ExtensionOption::OPTION_PAGE_INTEGRATION)) {
    if (!\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Enum\ExtensionOption::OPTION_AUTOLOAD)) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('flux', 'Configuration/TypoScript', 'Flux PAGE rendering');
    }
}
