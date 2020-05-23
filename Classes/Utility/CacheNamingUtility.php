<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Utility;

use TYPO3\CMS\Backend\View\Drawing\DrawingConfiguration;

/**
 * Cache Naming Compatibility Utility
 *
 * Used to return the right cache name based on current
 * TYPO3 version. See TYPO3 commits:
 *
 * - 972df083
 * - 828f4262
 */
abstract class CacheNamingUtility
{
    public static function getCacheName(string $cacheName): string
    {
        if (substr($cacheName, 0, 6) === 'cache_' && class_exists(DrawingConfiguration::class)) {
            $cacheName = substr($cacheName, 6);
        }
        return $cacheName;
    }
}
