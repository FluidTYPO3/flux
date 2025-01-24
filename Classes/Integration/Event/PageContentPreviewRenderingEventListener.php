<?php
namespace FluidTYPO3\Flux\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\PreviewRenderer;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageContentPreviewRenderingEventListener
{
    public function renderPreview(PageContentPreviewRenderingEvent $event): void
    {
        $table = $event->getTable();
        $record = $event->getRecord();

        if ($table !== 'tt_content') {
            return;
        }

        /** @var PreviewRenderer $renderer */
        $renderer = GeneralUtility::makeInstance(PreviewRenderer::class);
        $preview = $renderer->renderPreview($record, null, $event->getPreviewContent());
        if ($preview) {
            $event->setPreviewContent($preview[1]);
        }
    }
}
