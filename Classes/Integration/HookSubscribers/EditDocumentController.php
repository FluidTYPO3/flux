<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EditDocumentController
{
    /**
     * Make sure the FluxColPosAssignment JavaScript module is loaded
     */
    public function requireColumnPositionJavaScript() : void
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Flux/FluxColPosAssignment');
    }
}
