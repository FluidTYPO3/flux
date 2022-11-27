<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionNamingUtility
{
    public static function hasVendorName(string $qualifiedExtensionName): bool
    {
        return false !== strpos($qualifiedExtensionName, '.');
    }

    public static function getVendorName(string $qualifiedExtensionName): ?string
    {
        list($vendorName, ) = static::getVendorNameAndExtensionKey($qualifiedExtensionName);
        return $vendorName;
    }

    public static function getExtensionKey(string $qualifiedExtensionName): string
    {
        list(, $extensionKey) = static::getVendorNameAndExtensionKey($qualifiedExtensionName);
        return $extensionKey;
    }

    public static function getExtensionName(string $qualifiedExtensionName): string
    {
        list(, $extensionName) = static::getVendorNameAndExtensionName($qualifiedExtensionName);
        return (string) $extensionName;
    }

    public static function getExtensionSignature(string $qualifiedExtensionName): string
    {
        static $cache = [];
        if (isset($cache[$qualifiedExtensionName])) {
            return $cache[$qualifiedExtensionName];
        }
        $extensionKey = static::getExtensionKey($qualifiedExtensionName);
        $cache[$qualifiedExtensionName] = str_replace('_', '', $extensionKey);
        return $cache[$qualifiedExtensionName];
    }

    public static function getVendorNameAndExtensionKey(string $qualifiedExtensionName): array
    {
        static $cache = [];
        if (isset($cache[$qualifiedExtensionName])) {
            return $cache[$qualifiedExtensionName];
        }
        if (true === static::hasVendorName($qualifiedExtensionName)) {
            list($vendorName, $extensionKey) = GeneralUtility::trimExplode('.', $qualifiedExtensionName);
        } else {
            $vendorName = null;
            $extensionKey = $qualifiedExtensionName;
        }
        $extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($extensionKey);
        $cache[$qualifiedExtensionName] = [$vendorName, $extensionKey];
        return [$vendorName, $extensionKey];
    }

    public static function getVendorNameAndExtensionName(string $qualifiedExtensionName): array
    {
        static $cache = [];
        if (isset($cache[$qualifiedExtensionName])) {
            return $cache[$qualifiedExtensionName];
        }
        if (true === static::hasVendorName($qualifiedExtensionName)) {
            list($vendorName, $extensionName) = GeneralUtility::trimExplode('.', $qualifiedExtensionName);
        } else {
            $vendorName = null;
            $extensionName = $qualifiedExtensionName;
        }
        if (false !== strpos($extensionName, '_')) {
            $extensionName = GeneralUtility::underscoredToUpperCamelCase($extensionName);
        } else {
            $extensionName = ucfirst($extensionName);
        }
        $cache[$qualifiedExtensionName] = [$vendorName, $extensionName];
        return [$vendorName, $extensionName];
    }
}
