<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extension Utility
 */
class ExtensionNamingUtility
{

    /**
     * @param string $qualifiedExtensionName
     * @return boolean
     */
    public static function hasVendorName($qualifiedExtensionName)
    {
        return false !== strpos($qualifiedExtensionName, '.');
    }

    /**
     * @param string $qualifiedExtensionName
     * @return string
     */
    public static function getVendorName($qualifiedExtensionName)
    {
        list($vendorName, ) = self::getVendorNameAndExtensionKey($qualifiedExtensionName);
        return $vendorName;
    }

    /**
     * @param string $qualifiedExtensionName
     * @return string
     */
    public static function getExtensionKey($qualifiedExtensionName)
    {
        list(, $extensionKey) = self::getVendorNameAndExtensionKey($qualifiedExtensionName);
        return $extensionKey;
    }

    /**
     * @param string $qualifiedExtensionName
     * @return string
     */
    public static function getExtensionName($qualifiedExtensionName)
    {
        list(, $extensionName) = self::getVendorNameAndExtensionName($qualifiedExtensionName);
        return $extensionName;
    }

    /**
     * @param string $qualifiedExtensionName
     * @return string
     */
    public static function getExtensionSignature($qualifiedExtensionName)
    {
        static $cache = [];
        if (isset($cache[$qualifiedExtensionName])) {
            return $cache[$qualifiedExtensionName];
        }
        $extensionKey = self::getExtensionKey($qualifiedExtensionName);
        $cache[$qualifiedExtensionName] = str_replace('_', '', $extensionKey);
        return $cache[$qualifiedExtensionName];
    }

    /**
     * @param string $qualifiedExtensionName
     * @return array
     */
    public static function getVendorNameAndExtensionKey($qualifiedExtensionName)
    {
        static $cache = [];
        if (isset($cache[$qualifiedExtensionName])) {
            return $cache[$qualifiedExtensionName];
        }
        if (true === self::hasVendorName($qualifiedExtensionName)) {
            list($vendorName, $extensionKey) = GeneralUtility::trimExplode('.', $qualifiedExtensionName);
        } else {
            $vendorName = null;
            $extensionKey = $qualifiedExtensionName;
        }
        $extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($extensionKey);
        $cache[$qualifiedExtensionName] = [$vendorName, $extensionKey];
        return [$vendorName, $extensionKey];
    }

    /**
     * @param string $qualifiedExtensionName
     * @return array
     */
    public static function getVendorNameAndExtensionName($qualifiedExtensionName)
    {
        static $cache = [];
        if (isset($cache[$qualifiedExtensionName])) {
            return $cache[$qualifiedExtensionName];
        }
        if (true === self::hasVendorName($qualifiedExtensionName)) {
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
