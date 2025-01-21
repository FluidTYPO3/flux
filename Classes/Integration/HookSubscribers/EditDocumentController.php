<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EditDocumentController
{
    /**
     * @var bool
     */
    private static $assetLoaded = false;

    /**
     * Make sure the FluxColPosAssignment JavaScript module is loaded
     *
     * @codeCoverageIgnore
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
