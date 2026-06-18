<?php
namespace FluidTYPO3\Flux\Proxy;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\VersionUtility;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;

/**
 * Breaking changes everywhere. Breaking changes and final/readonly integration destructions as far as the eye can see!
 */
class DataProviderContextProxy
{
    public static function createInstance(int $pageUid): DataProviderContext
    {
        if (VersionUtility::isCoreAtLeast13()) {
            $context = new DataProviderContext(1, 'pages', 'tx_fed_page_flexform', [], []);
        } else {
            $context = new DataProviderContext();
            $context->setPageId(1);
        }
        return $context;
    }

    public static function readPageUidFromObject(DataProviderContext $dataProviderContext): int
    {
        if (VersionUtility::isCoreAtLeast13()) {
            return $dataProviderContext->pageId;
        }
        return $dataProviderContext->getPageId();
    }
}
