<?php
namespace FluidTYPO3\Flux\Proxy;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Breaking changes everywhere. Breaking changes and final/readonly integration destructions as far as the eye can see!
 */
class DataProviderContextProxy
{
    public static function createInstance(int $pageUid): DataProviderContext
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            $context = new DataProviderContext(1);
        } else {
            $context = new DataProviderContext();
            $context->setPageId(1);
        }
        return $context;
    }

    public static function readPageUidFromObject(DataProviderContext $dataProviderContext): int
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            return $dataProviderContext->pageId;
        }
        return $dataProviderContext->getPageId();
    }
}
