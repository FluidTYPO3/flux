<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Utility;

use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\DropInContentTypeDefinition;
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
    public const OPTION_PAGE_LANGUAGE_OVERLAY = 'pagesLanguageConfigurationOverlay';

    protected static $defaults = [
        self::OPTION_DEBUG_MODE => false,
        self::OPTION_DOKTYPES => '0,1,4',
        self::OPTION_HANDLE_ERRORS => false,
        self::OPTION_AUTOLOAD => true,
        self::OPTION_PLUG_AND_PLAY => false,
        self::OPTION_PLUG_AND_PLAY_DIRECTORY => DropInContentTypeDefinition::DESIGN_DIRECTORY,
        self::OPTION_PAGE_LANGUAGE_OVERLAY => false,
    ];

    public static function initialize(?string $extensionConfiguration): void
    {
        if (class_exists(ExtensionConfiguration::class)) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('flux');
        } else {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] ?? unserialize($extensionConfiguration);
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'] ?? [];
    }

    public static function getOptions(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux'];
    }

    public static function getOption(string $optionName)
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'][$optionName] ?? static::$defaults[$optionName] ?? null;
    }
}
