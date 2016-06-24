<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * VersionUtility
 */
class VersionUtility
{

    /**
     * @param string $extensionKey
     * @param integer $majorVersion
     * @param integer $minorVersion
     * @param integer $bugfixVersion
     * @return boolean
     */
    public static function assertExtensionVersionIsAtLeastVersion(
        $extensionKey,
        $majorVersion,
        $minorVersion = 0,
        $bugfixVersion = 0
    ) {
        if (false === ExtensionManagementUtility::isLoaded($extensionKey)) {
            return false;
        }
        $extensionVersion = ExtensionManagementUtility::getExtensionVersion($extensionKey);
        list ($major, $minor, $bugfix) = explode('.', $extensionVersion);
        return ($majorVersion <= $major && $minorVersion <= $minor && $bugfixVersion <= $bugfix);
    }
}
