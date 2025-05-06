<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Enum\PreviewOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Integration\Overrides\PageLayoutView;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Proxy\SiteFinderProxy;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Backend\View\Drawing\DrawingConfiguration;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Backend\View\PageViewMode;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\TemplateView;

class PreviewView extends TemplateView
{
    protected array $templates = [
        'gridToggle' => '<div class="grid-visibility-toggle" data-toggle-uid="%s">%s</div>',
        'link' => '<a href="%s" title="%s" class="btn btn-default btn-sm">%s %s</a>'
    ];

    protected ConfigurationManagerInterface $configurationManager;
    protected WorkspacesAwareRecordService $workspacesAwareRecordService;

    public function __construct(?RenderingContextInterface $context = null)
    {
        parent::__construct($context);

        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $this->configurationManager = $configurationManager;

        /** @var WorkspacesAwareRecordService $workspacesAwareRecordService */
        $workspacesAwareRecordService = GeneralUtility::makeInstance(WorkspacesAwareRecordService::class);
        $this->workspacesAwareRecordService = $workspacesAwareRecordService;
    }

    public function getPreview(ProviderInterface $provider, array $row, bool $withoutGrid = false): string
    {
        $form = $provider->getForm($row);
        $options = $this->getPreviewOptions($form);
        $mode = $this->getOptionMode($options);
        $previewContent = (string) $this->renderPreviewSection($provider, $row, $form);

        if ($withoutGrid || PreviewOption::MODE_NONE === $mode || !is_object($form)) {
            return $previewContent;
        }

        $gridContent = $this->renderGrid($provider, $row, $form);
        $collapsedClass = '';
        if (in_array($row['uid'], (array) json_decode($this->getCookie() ?? ''))) {
            $collapsedClass = ' flux-grid-hidden';
        }
        $gridContent = sprintf(
            '<div class="flux-collapse%s" data-grid-uid="%d">%s</div>',
            $collapsedClass,
            $row['uid'],
            $gridContent
        );
        if (PreviewOption::MODE_APPEND === $mode) {
            $previewContent = $previewContent . $gridContent;
        } elseif (PreviewOption::MODE_PREPEND === $mode) {
            $previewContent = $gridContent . $previewContent;
        }

        $previewContent = trim($previewContent);

        return HookHandler::trigger(
            HookHandler::PREVIEW_RENDERED,
            ['form' => $form, 'preview' => $previewContent]
        )['preview'];
    }

    protected function getPreviewOptions(?Form $form = null): array
    {
        if (!is_object($form) || !$form->hasOption(PreviewOption::PREVIEW)) {
            return [
                PreviewOption::MODE => $this->getOptionMode(),
                PreviewOption::TOGGLE => $this->getOptionToggle()
            ];
        }

        return (array) $form->getOption(PreviewOption::PREVIEW);
    }

    protected function getOptionMode(array $options = []): string
    {
        return $options[PreviewOption::MODE] ?? PreviewOption::MODE_APPEND;
    }

    protected function getOptionToggle(array $options = []): bool
    {
        return (boolean) ($options[PreviewOption::TOGGLE] ?? true);
    }

    protected function renderPreviewSection(ProviderInterface $provider, array $row, ?Form $form = null): ?string
    {
        $templatePathAndFilename = $provider->getTemplatePathAndFilename($row);
        if (!$templatePathAndFilename) {
            return null;
        }
        $extensionKey = $provider->getExtensionKey($row);

        $templateVariables = $provider->getTemplateVariables($row);
        $flexformVariables = $provider->getFlexFormValues($row);
        $variables = RecursiveArrayUtility::merge($templateVariables, $flexformVariables);
        $variables['row'] = $row;
        $variables['record'] = $row;

        if (is_object($form)) {
            $formLabel = $form->getLabel();
            $label = $this->getLanguageService()->sL((string) $formLabel);
            $variables['label'] = $label;
        }

        $renderingContext = $this->getRenderingContext();
        $renderingContext->setControllerName($provider->getControllerNameFromRecord($row));
        $renderingContext->setControllerAction($provider->getControllerActionFromRecord($row));
        $renderingContext->getTemplatePaths()->fillDefaultsByPackageName(
            ExtensionNamingUtility::getExtensionKey($extensionKey)
        );
        $renderingContext->getTemplatePaths()->setTemplatePathAndFilename($templatePathAndFilename);
        return $this->renderSection('Preview', $variables, true);
    }

    protected function renderGrid(ProviderInterface $provider, array $row, Form $form): string
    {
        $content = '';
        $grid = $provider->getGrid($row);
        if ($grid->hasChildren()) {
            $options = $this->getPreviewOptions($form);
            if ($this->getOptionToggle($options)) {
                $content = $this->drawGridToggle($row, $content);
            }

            // Live-patching TCA to add items, which will be read by the BackendLayoutView in order to read
            // the LLL labels of individual columns. Unfortunately, BackendLayoutView calls functions in a way
            // that it is not possible to overrule the colPos values via the BackendLayout without creating an
            // XCLASS - so a bit of runtime TCA patching is preferable.
            $tcaBackup = $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'];
            $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'] = array_merge(
                $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'],
                $grid->buildExtendedBackendLayoutArray($row['uid'])['__items']
            );

            $pageUid = $row['pid'];
            if (($workspaceId = $this->getBackendUser()->workspace) > 0) {
                $workspaceVersion = $this->fetchWorkspaceVersionOfRecord($workspaceId, $row['uid']);
                $pageUid = $workspaceVersion['pid'] ?? $pageUid;
            }
            $pageLayoutView = $this->getInitializedPageLayoutView($provider, $row);
            if ($pageLayoutView instanceof BackendLayoutRenderer) {
                if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.0', '>=')) {
                    $content .= $pageLayoutView->drawContent(
                        $GLOBALS['TYPO3_REQUEST'],
                        $pageLayoutView->getContext(),
                        $form->getOption(FormOption::RECORD_TABLE) === 'pages' // render unused area only for "pages"
                    );
                } else {
                    $content .= $pageLayoutView->drawContent(false);
                }
            } elseif (method_exists($pageLayoutView, 'start') && method_exists($pageLayoutView, 'generateList')) {
                $pageLayoutView->start($pageUid, 'tt_content', 0);
                $pageLayoutView->generateList();
                $content .= $pageLayoutView->HTMLcode;
            } else {
                $content .= $pageLayoutView->getTable_tt_content($row['pid']);
            }

            $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'] = $tcaBackup;
        }
        return $content;
    }

    protected function drawGridToggle(array $row, string $content): string
    {
        return sprintf($this->templates['gridToggle'], $row['uid'], $content);
    }

    /**
     * @return PageLayoutView|BackendLayoutRenderer
     */
    protected function getInitializedPageLayoutView(ProviderInterface $provider, array $row)
    {
        $pageId = (int) $row['pid'];
        $pageRecord = $this->workspacesAwareRecordService->getSingle('pages', '*', $pageId);
        $moduleData = $this->getBackendUser()->getModuleData('web_layout', '');
        $showHiddenRecords = (int) ($moduleData['tt_content_showHidden'] ?? 1);

        // For all elements to be shown in draft workspaces & to also show hidden elements by default if user hasn't
        // disabled the option analog behavior to the PageLayoutController at the end of menuConfig()
        if ($this->getActiveWorkspaceId() !== 0 || !$showHiddenRecords) {
            $moduleData['tt_content_showHidden'] = 1;
        }

        $parentRecordUid = ($row['l18n_parent'] ?? 0) > 0 ? $row['l18n_parent'] : ($row['t3ver_oid'] ?: $row['uid']);

        $backendLayout = $provider->getGrid($row)->buildBackendLayout($parentRecordUid);
        $layoutConfiguration = $backendLayout->getStructure();

        /** @var Features $features */
        $features = GeneralUtility::makeInstance(Features::class);
        $fluidBasedLayoutFeatureEnabled = $features->isFeatureEnabled('fluidBasedPageModule');

        if ($fluidBasedLayoutFeatureEnabled) {
            /** @var SiteFinderProxy $siteFinder */
            $siteFinder = GeneralUtility::makeInstance(SiteFinderProxy::class);
            $site = $siteFinder->getSiteByPageId($pageId);
            $language = null;
            if ($row['sys_language_uid'] >= 0) {
                $language = $site->getLanguageById((int) $row['sys_language_uid']);
            }

            if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
                $configuration = DrawingConfiguration::create($backendLayout, [], PageViewMode::LayoutView);
                $configuration->setSelectedLanguageId($language->getLanguageId());

                /** @var PageLayoutContext $context */
                $context = GeneralUtility::makeInstance(
                    PageLayoutContext::class,
                    $this->fetchPageRecordWithoutOverlay($pageId),
                    $backendLayout,
                    $site,
                    $configuration,
                    $GLOBALS['TYPO3_REQUEST']
                );
            } else {
                /** @var PageLayoutContext $context */
                $context = GeneralUtility::makeInstance(
                    PageLayoutContext::class,
                    $this->fetchPageRecordWithoutOverlay($pageId),
                    $backendLayout
                );
                $configuration = $context->getDrawingConfiguration();
            }

            if (isset($language)) {
                 $context = $context->cloneForLanguage($language);
            }

            if (method_exists($configuration, 'setActiveColumns')) {
                $configuration->setActiveColumns($backendLayout->getColumnPositionNumbers());
            }

            if (isset($language) && method_exists($configuration, 'setSelectedLanguageUid')) {
                $configuration->setSelectedLanguageId($language->getLanguageId());
            }

            $backendLayoutRenderer = $this->createBackendLayoutRenderer($context);

            $backendLayoutRenderer->setContext($context);

            return $backendLayoutRenderer;
        }

        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);

        /** @var PageLayoutView $view */
        $view = GeneralUtility::makeInstance(PageLayoutView::class, $eventDispatcher);
        $view->setProvider($provider);
        $view->setRecord($row);

        $contentTypeLabels = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $val) {
            $contentTypeLabels[$val[1]] = $this->getLanguageService()->sL($val[0]);
        }
        $itemLabels = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns'] as $name => $val) {
            $itemLabels[$name] = ($val['label'] ?? false) ? $this->getLanguageService()->sL($val['label']) : '';
        }

        array_push(
            $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'],
            ...($layoutConfiguration['__items'] ?? [])
        );

        $columnsAsCSV = implode(',', $layoutConfiguration['__colPosList'] ?? []);

        $view->script = 'db_layout.php';
        $view->showIcon = 1;
        $view->setLMargin = 0;
        $view->doEdit = 1;
        $view->no_noWrap = 1;
        $view->ext_CALC_PERMS = $this->getBackendUser()->calcPerms($pageRecord);
        $view->id = $row['pid'];
        $view->table = 'tt_content';
        $view->tableList = 'tt_content';
        $view->currentTable = 'tt_content';
        $view->tt_contentConfig['showCommands'] = 1;
        $view->tt_contentConfig['showInfo'] = 1;
        $view->tt_contentConfig['single'] = 0;
        $view->nextThree = 1;
        $view->tt_contentConfig['sys_language_uid'] = (int) $row['sys_language_uid'];
        $view->tt_contentConfig['showHidden'] = $showHiddenRecords;
        $view->tt_contentConfig['activeCols'] = $columnsAsCSV;
        $view->tt_contentConfig['cols'] = $columnsAsCSV;
        $view->CType_labels = $contentTypeLabels;
        $view->itemLabels = $itemLabels;

        if (($pageInfo = $this->checkAccessToPage($pageId))) {
            $view->setPageinfo($pageInfo);
        }

        return $view;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createBackendLayoutRenderer(PageLayoutContext $context): BackendLayoutRenderer
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.4', '>=')) {
            /** @var BackendViewFactory $backendViewFactory */
            $backendViewFactory = GeneralUtility::getContainer()->get(BackendViewFactory::class);
            /** @var RecordFactory $recordFactory */
            $recordFactory = GeneralUtility::makeInstance(RecordFactory::class);
            /** @var BackendLayoutRenderer $backendLayoutRenderer */
            $backendLayoutRenderer = GeneralUtility::makeInstance(
                BackendLayoutRenderer::class,
                $backendViewFactory,
                $recordFactory
            );
        } elseif (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.0', '>=')) {
            /** @var BackendViewFactory $backendViewFactory */
            $backendViewFactory = GeneralUtility::getContainer()->get(BackendViewFactory::class);
            /** @var BackendLayoutRenderer $backendLayoutRenderer */
            $backendLayoutRenderer = GeneralUtility::makeInstance(
                BackendLayoutRenderer::class,
                $backendViewFactory
            );
        } else {
            /** @var BackendLayoutRenderer $backendLayoutRenderer */
            $backendLayoutRenderer = GeneralUtility::makeInstance(BackendLayoutRenderer::class, $context);
        }
        return $backendLayoutRenderer;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function fetchWorkspaceVersionOfRecord(int $workspaceId, int $recordUid): ?array
    {
        /** @var array|false $workspaceVersion */
        $workspaceVersion = BackendUtility::getWorkspaceVersionOfRecord($workspaceId, 'tt_content', $recordUid);
        return $workspaceVersion ?: null;
    }

    /**
     * @codeCoverageIgnore
     * @return array|false
     */
    protected function checkAccessToPage(int $pageId)
    {
        return BackendUtility::readPageAccess($pageId, '');
    }

    /**
     * @codeCoverageIgnore
     */
    protected function fetchPageRecordWithoutOverlay(int $pageId): ?array
    {
        return BackendUtility::getRecord('pages', $pageId);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getCookie(): ?string
    {
        return $_COOKIE['fluxCollapseStates'] ?? null;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getActiveWorkspaceId(): int
    {
        return (integer) ($GLOBALS['BE_USER']->workspace ?? 0);
    }
}
