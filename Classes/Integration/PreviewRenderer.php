<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\PreviewOption;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Page\PageRenderer;

class PreviewRenderer
{
    protected static bool $assetsIncluded = false;
    private PageRenderer $pageRenderer;
    private ProviderResolver $providerResolver;

    public function __construct(PageRenderer $pageRenderer, ProviderResolver $providerResolver)
    {
        $this->pageRenderer = $pageRenderer;
        $this->providerResolver = $providerResolver;
    }

    public function renderPreview(array|Record|RawRecord $row, ?string $header, ?string $currentPreview): ?array
    {
        if ($row instanceof Record) {
            $row = $row->toArray();
        }
        $fieldName = null;
        $headerContent = $header;
        $drawItem = true;
        $itemContent = $currentPreview;
        $preview = [$headerContent, $itemContent, $drawItem];
        $anchorLink = '<a name="c' . $row['uid'] . '"></a>';
        $providers = $this->providerResolver->resolveConfigurationProviders('tt_content', $fieldName, $row);
        foreach ($providers as $provider) {
            /** @var ProviderInterface $provider */
            $form = $provider->getForm($row);
            if (!$form) {
                continue;
            }

            $previewOptions = $form->getOption(PreviewOption::PREVIEW);
            $previewOptionValue = is_array($previewOptions) ? $previewOptions[PreviewOption::MODE] ?? null : null;

            if ($previewOptionValue === PreviewOption::MODE_NONE) {
                continue;
            }

            [$previewHeader, $previewContent, $continueDrawing] = $provider->getPreview($row);
            if (!empty($previewContent)) {
                $drawItem = false;
                $itemContent = match ($previewOptionValue) {
                    PreviewOption::MODE_PREPEND => $anchorLink . $previewContent . $currentPreview,
                    PreviewOption::MODE_APPEND => $anchorLink . $currentPreview . $previewContent,
                    default => $anchorLink . $previewContent,
                };
            }

            if (!empty($previewHeader)) {
                $drawItem = false;
                $headerContent = match ($previewOptionValue) {
                    PreviewOption::MODE_PREPEND => $previewHeader . (!empty($header) ? ': ' . $header : ''),
                    PreviewOption::MODE_APPEND => (!empty($header) ? $header . ': ' : '') . $previewHeader,
                    default => $previewHeader,
                };
            }

            $preview = [$headerContent, $itemContent, $drawItem];
            if (!$continueDrawing) {
                break;
            }
        }
        $this->attachAssets();
        return $preview;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function attachAssets(): void
    {
        if (!static::$assetsIncluded) {
            $this->pageRenderer->addCssFile('EXT:flux/Resources/Public/css/flux.css');
            static::$assetsIncluded = true;
        }
    }
}
