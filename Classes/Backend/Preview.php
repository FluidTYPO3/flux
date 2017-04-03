<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

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
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var FluxService
     */
    protected $configurationService;

    /**
     * @var RecordService
     */
    protected $recordService;

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->configurationService = $this->objectManager->get(FluxService::class);
        $this->recordService = $this->objectManager->get(RecordService::class);
    }

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
        $this->renderPreview($headerContent, $itemContent, $row, $drawItem);
        unset($parentObject);
    }

    /**
     * @param string $headerContent
     * @param string $itemContent
     * @param array $row
     * @param boolean $drawItem
     * @return NULL
     */
    public function renderPreview(&$headerContent, &$itemContent, array &$row, &$drawItem)
    {
        // every provider for tt_content will be asked to get a preview
        $fieldName = null;
        $itemContent = '<a name="c' . $row['uid'] . '"></a>' . $itemContent;
        $providers = $this->configurationService->resolveConfigurationProviders('tt_content', $fieldName, $row);
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
        return null;
    }

    /**
     * @param integer $contentUid
     * @return array
     */
    protected function getPageTitleAndPidFromContentUid($contentUid)
    {
        return reset(
            $this->recordService->get(
                'tt_content t, pages p',
                'p.title, t.pid',
                "t.uid = '" . $contentUid . "' AND p.uid = t.pid"
            )
        );
    }

    /**
     * @return void
     */
    protected function attachAssets()
    {
        if (false === self::$assetsIncluded) {
            $doc = GeneralUtility::makeInstance(ModuleTemplate::class);
            $doc->backPath = $GLOBALS['BACK_PATH'];

            /** @var PageRenderer $pageRenderer */
            $pageRenderer = $doc->getPageRenderer();
            $pageRenderer->addCssFile(
                $doc->backPath . ExtensionManagementUtility::extRelPath('flux') . 'Resources/Public/css/grid.css'
            );

            $fullJsPath = PathUtility::getRelativePath(
                PATH_typo3,
                GeneralUtility::getFileAbsFileName('EXT:flux/Resources/Public/js/')
            );

            // requirejs
            $pageRenderer->addRequireJsConfiguration([
                'paths' => [
                    'FluidTypo3/Flux/FluxCollapse' => $fullJsPath . 'fluxCollapse',
                ],
            ]);
            $pageRenderer->loadRequireJsModule('FluidTypo3/Flux/FluxCollapse');

            self::$assetsIncluded = true;
        }
    }
}
