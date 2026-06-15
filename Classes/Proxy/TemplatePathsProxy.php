<?php
namespace FluidTYPO3\Flux\Proxy;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\VersionUtility;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Hooray for totally unnecessary breaking changes.
 */
class TemplatePathsProxy
{
    public static function toArray(TemplatePaths $templatePaths): array
    {
        if (VersionUtility::isCoreAtLeast13()) {
            return [
                TemplatePaths::CONFIG_TEMPLATEROOTPATHS => $templatePaths->getTemplateRootPaths(),
                TemplatePaths::CONFIG_PARTIALROOTPATHS => $templatePaths->getPartialRootPaths(),
                TemplatePaths::CONFIG_LAYOUTROOTPATHS => $templatePaths->getLayoutRootPaths(),
            ];
        }
        return $templatePaths->toArray();
    }
}
