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
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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

    public function renderPreview(array $row, ?string $currentHeader, ?string $currentPreview): ?array
    {
        $fieldName = null;
        $headerContent = $currentHeader;
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
                switch ($previewOptionValue) {
                    case PreviewOption::MODE_PREPEND:
                        $itemContent = $anchorLink . $previewContent . $currentPreview;
                        break;
                    case PreviewOption::MODE_APPEND:
                        $itemContent = $anchorLink . $currentPreview . $previewContent;
                        break;
                    case PreviewOption::MODE_REPLACE:
                    default:
                        $itemContent = $anchorLink . $previewContent;
                        break;
                }
            }

            if (!empty($previewHeader)) {
                $drawItem = false;
                switch ($previewOptionValue) {
                    case PreviewOption::MODE_PREPEND:
                        $headerContent = $previewHeader . (!empty($currentHeader) ? ': ' . $currentHeader : '');
                        break;
                    case PreviewOption::MODE_APPEND:
                        $headerContent = (!empty($currentHeader) ? $currentHeader . ': ' : '') . $previewHeader;
                        break;
                    case PreviewOption::MODE_REPLACE:
                    default:
                        $headerContent = $previewHeader;
                        break;
                }
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
            if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.4', '<')) {
                // Collapse feature is inoperable on v12 and above.
                $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Flux/FluxCollapse');
            }

            static::$assetsIncluded = true;
        }
    }
}
