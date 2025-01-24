<?php
namespace FluidTYPO3\Flux\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\PreviewOption;
use FluidTYPO3\Flux\Provider\PageProvider;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class ModifyPageLayoutContentEventListener
{
    private PageProvider $pageProvider;

    public function __construct(PageProvider $pageProvider)
    {
        $this->pageProvider = $pageProvider;
    }

    public function renderPreview(ModifyPageLayoutContentEvent $event): void
    {
        $id = $event->getRequest()->getQueryParams()['id'] ?? 0;

        $row = $this->getRecord(is_scalar($id) ? (integer) $id : 0);
        if ($row === null) {
            return;
        }

        $form = $this->pageProvider->getForm($row);
        if (!$form || !$form->getEnabled()) {
            return;
        }

        [, $previewContent, ] = $this->pageProvider->getPreview($row);
        if (empty($previewContent)) {
            return;
        }

        $previewOptions = $form->getOption(PreviewOption::PREVIEW);
        $previewMode = is_array($previewOptions) ? $previewOptions[PreviewOption::MODE] ?? null : $previewOptions;

        switch ($previewMode) {
            case PreviewOption::MODE_NONE:
                return;
            case PreviewOption::MODE_PREPEND:
                $event->setHeaderContent($previewContent . $event->getHeaderContent());
                break;
            case PreviewOption::MODE_REPLACE:
                $event->setHeaderContent($previewContent);
                break;
            default:
            case PreviewOption::MODE_APPEND:
                $event->addHeaderContent($previewContent);
                break;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getRecord(int $uid): ?array
    {
        return BackendUtility::getRecord('pages', $uid);
    }
}
