<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Utility;

use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\DropInContentTypeDefinition;
use FluidTYPO3\Flux\Enum\ExtensionOption;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionConfigurationUtility
{
    public const OPTION_DEBUG_MODE = 'debugMode';
    public const OPTION_DOKTYPES = 'doktypes';
    public const OPTION_HANDLE_ERRORS = 'handleErrors';
    public const OPTION_AUTOLOAD = 'autoload';
    public const OPTION_PLUG_AND_PLAY = 'plugAndPlay';
    public const OPTION_PLUG_AND_PLAY_DIRECTORY = 'plugAndPlayDirectory';
    public const OPTION_PAGE_INTEGRATION = 'pageIntegration';
    public const OPTION_FLEXFORM_TO_IRRE = 'flexFormToIrre';

    protected static array $defaults = [
        ExtensionOption::OPTION_DEBUG_MODE => false,
        ExtensionOption::OPTION_DOKTYPES => '0,1,4',
        ExtensionOption::OPTION_HANDLE_ERRORS => false,
        ExtensionOption::OPTION_AUTOLOAD => true,
        ExtensionOption::OPTION_PLUG_AND_PLAY => false,
        ExtensionOption::OPTION_PLUG_AND_PLAY_DIRECTORY => DropInContentTypeDefinition::DESIGN_DIRECTORY,
        ExtensionOption::OPTION_PAGE_INTEGRATION => true,
        ExtensionOption::OPTION_FLEXFORM_TO_IRRE => false,
    ];

    public static function initialize(?string $extensionConfiguration): void
    {
        $currentConfiguration = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux'];
        $currentConfiguration['hooks'] = $currentConfiguration['hooks'] ?? [];

        if (empty($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'])) {
            $legacyConfiguration = &$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'];
            /** @var ExtensionConfiguration $extensionConfigurationManager */
            $extensionConfigurationManager = GeneralUtility::makeInstance(ExtensionConfiguration::class);
            $legacyConfiguration = $extensionConfigurationManager->get('flux');
        }
    }

    public static function getOptions(): array
    {
        return ($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'] ?? $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'])['flux'] ?? [];
    }

    /**
     * @return mixed|null
     */
    public static function getOption(string $optionName)
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'][$optionName]
            ?? $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['flux'][$optionName]
            ?? static::$defaults[$optionName]
            ?? null;
    }
}
