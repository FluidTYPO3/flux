<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @codeCoverageIgnore
 */
class EditDocumentController
{
    /**
     * @var bool
     */
    private static $assetLoaded = false;

    /**
     * Make sure the FluxColPosAssignment JavaScript module is loaded
     */
    public function requireColumnPositionJavaScript() : void
    {
        if (self::$assetLoaded) {
            return;
        }

        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        if (method_exists($pageRenderer, 'loadJavaScriptModule')) {
            $pageRenderer->loadJavaScriptModule('@fluidtypo3/flux/FluxColPosAssignment.js');
        } else {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Flux/FluxColPosAssignmentLegacy');
        }

        self::$assetLoaded = true;
    }
}
