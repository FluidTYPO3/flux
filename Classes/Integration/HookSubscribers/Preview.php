<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\PreviewRenderer;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Fluid Template preview renderer
 *
 * @codeCoverageIgnore
 */
class Preview implements PageLayoutViewDrawItemHookInterface
{
    /**
     * @param PageLayoutView $parentObject
     * @param boolean $drawItem
     * @param string $headerContent
     * @param string $itemContent
     * @param array $row
     * @return void
     */
    public function preProcess(
        PageLayoutView &$parentObject,
        &$drawItem,
        &$headerContent,
        &$itemContent,
        array &$row
    ): void {
        /** @var PreviewRenderer $renderer */
        $renderer = GeneralUtility::makeInstance(PreviewRenderer::class);
        $preview = $renderer->renderPreview($row, $headerContent, $itemContent);
        if (empty($preview)) {
            return;
        }
        [$header, $content, $continue] = $preview;
        if (!empty($content)) {
            $itemContent = (string) $content;
        }
        if (!empty($header)) {
            $headerContent = (string) $header;
        }
        $drawItem = (string) $continue;
        unset($parentObject);
    }
}
