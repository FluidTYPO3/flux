<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class AccessibleExtensionManagementUtility extends ExtensionManagementUtility
{
    public static function setPackageManager(PackageManager $packageManager): void
    {
        static::$packageManager = $packageManager;
    }

    public static function removePackageManager(): void
    {
        static::$packageManager = null;
    }

    public static function getPackageManager(): ?PackageManager
    {
        return static::$packageManager;
    }
}
