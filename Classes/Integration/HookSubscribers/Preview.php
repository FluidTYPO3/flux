<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Fluid Template preview renderer
 */
class Preview implements PageLayoutViewDrawItemHookInterface
{
    protected static bool $assetsIncluded = false;

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
        $fieldName = null;
        $itemContent = '<a name="c' . $row['uid'] . '"></a>' . $itemContent;
        $providers = $this->getConfigurationService()->resolveConfigurationProviders('tt_content', $fieldName, $row);
        foreach ($providers as $provider) {
            /** @var ProviderInterface $provider */
            [$previewHeader, $previewContent, $continueDrawing] = $provider->getPreview($row);
            if (false === empty($previewHeader)) {
                $headerContent = $previewHeader . (!empty($headerContent) ? ': ' . $headerContent : '');
                $drawItem = false;
            }
            if (false === empty($previewContent)) {
                $itemContent .= $previewContent;
                $drawItem = false;
            }
            if (false === $continueDrawing) {
                break;
            }
        }
        $this->attachAssets();
        unset($parentObject);
    }

    protected function attachAssets(): void
    {
        if (false === static::$assetsIncluded) {
            /** @var PageRenderer $pageRenderer */
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addCssFile('EXT:flux/Resources/Public/css/flux.css');
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Flux/FluxCollapse');

            static::$assetsIncluded = true;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getConfigurationService(): FluxService
    {
        /** @var FluxService $fluxService */
        $fluxService = GeneralUtility::makeInstance(FluxService::class);
        return $fluxService;
    }
}
