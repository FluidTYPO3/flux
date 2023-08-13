<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\PreviewOption;
use FluidTYPO3\Flux\Provider\PageProvider;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PagePreviewRenderer
{
    public function render(array $params, PageLayoutController $pageLayoutController): string
    {
        $pageProvider = $this->getPageProvider();
        $previewContent = '';

        $idProperty = new \ReflectionProperty($pageLayoutController, 'id');
        $idProperty->setAccessible(true);
        $id = $idProperty->getValue($pageLayoutController);

        $row = $this->getRecord(is_scalar($id) ? (integer) $id : 0);
        if (!$row) {
            return '';
        }

        $form = $pageProvider->getForm($row);

        if ($form && $form->getEnabled()) {
            // Force the preview to *not* generate content column HTML in preview
            $form->setOption(PreviewOption::PREVIEW, [
                PreviewOption::MODE => PreviewOption::MODE_NONE
            ]);

            [, $previewContent, ] = $pageProvider->getPreview($row);
        }

        return $previewContent;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getRecord(int $uid): ?array
    {
        return BackendUtility::getRecord('pages', $uid);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getPageProvider(): PageProvider
    {
        /** @var PageProvider $pageProvider */
        $pageProvider = GeneralUtility::makeInstance(PageProvider::class);
        return $pageProvider;
    }
}
