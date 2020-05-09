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
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Fluid Template preview renderer
 */
class Preview implements PageLayoutViewDrawItemHookInterface
{
    /**
     * @var boolean
     */
    protected static $assetsIncluded = false;

    /**
     *
     * @param PageLayoutView $parentObject
     * @param boolean $drawItem
     * @param string $headerContent
     * @param string $itemContent
     * @param array $row
     * @return void
     */
    public function preProcess(PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {
        $fieldName = null;
        $itemContent = '<a name="c' . $row['uid'] . '"></a>' . $itemContent;
        $providers = $this->getConfigurationService()->resolveConfigurationProviders('tt_content', $fieldName, $row);
        foreach ($providers as $provider) {
            /** @var ProviderInterface $provider */
            list ($previewHeader, $previewContent, $continueDrawing) = $provider->getPreview($row);
            if (false === empty($previewHeader)) {
                $headerContent = $previewHeader . (false === empty($headerContent) ? ': ' . $headerContent : '');
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

    /**
     * @return void
     */
    protected function attachAssets()
    {
        if (false === static::$assetsIncluded) {
            $doc = GeneralUtility::makeInstance(ModuleTemplate::class);
            $doc->backPath = $GLOBALS['BACK_PATH'] ?? '';

            /** @var PageRenderer $pageRenderer */
            $pageRenderer = $doc->getPageRenderer();

            $fullJsPath = PathUtility::getRelativePath(
                defined('PATH_typo3') ? PATH_typo3 : Environment::getPublicPath(),
                GeneralUtility::getFileAbsFileName('EXT:flux/Resources/Public/js/')
            );

            // requirejs
            $pageRenderer->addRequireJsConfiguration([
                'paths' => [
                    'FluidTypo3/Flux/FluxCollapse' => $fullJsPath . 'fluxCollapse',
                ],
            ]);
            $pageRenderer->loadRequireJsModule('FluidTypo3/Flux/FluxCollapse');

            static::$assetsIncluded = true;
        }
    }

    protected function getConfigurationService(): FluxService
    {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(FluxService::class);
    }
}
