<?php
namespace FluidTYPO3\Flux\Proxy;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Hooray for totally unnecessary breaking changes.
 */
class TemplatePathsProxy
{
    public static function toArray(TemplatePaths $templatePaths): array
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            return [
                TemplatePaths::CONFIG_TEMPLATEROOTPATHS => $templatePaths->getTemplateRootPaths(),
                TemplatePaths::CONFIG_PARTIALROOTPATHS => $templatePaths->getPartialRootPaths(),
                TemplatePaths::CONFIG_LAYOUTROOTPATHS => $templatePaths->getLayoutRootPaths(),
            ];
        }
        return $templatePaths->toArray();
    }
}
