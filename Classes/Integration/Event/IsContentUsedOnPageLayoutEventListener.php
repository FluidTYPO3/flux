<?php
namespace FluidTYPO3\Flux\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\PreviewRenderer;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Backend\View\Event\IsContentUsedOnPageLayoutEvent;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IsContentUsedOnPageLayoutEventListener
{
    public function handleEvent(IsContentUsedOnPageLayoutEvent $event): void
    {
        if (!$event->isRecordUsed()) {
            $event->setUsed($event->getRecord()['colPos'] >= ColumnNumberUtility::MULTIPLIER);
        }
    }
}
