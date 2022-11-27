<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Integration\Overrides\PageLayoutView;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Drawing\BackendLayoutRenderer;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * PreviewView
 */
class PreviewView extends TemplateView
{
    const OPTION_PREVIEW = 'preview';
    const OPTION_MODE = 'mode';
    const MODE_APPEND = 'append';
    const MODE_PREPEND = 'prepend';
    const MODE_NONE = 'none';
    const OPTION_TOGGLE = 'toggle';
    const PREVIEW_SECTION = 'Preview';
    const CONTROLLER_NAME = 'Content';

    protected array $templates = [
        'gridToggle' => '<div class="grid-visibility-toggle" data-toggle-uid="%s">
                            %s
                        </div>',
        'link' => '<a href="%s" title="%s"
                      class="btn btn-default btn-sm">%s %s</a>'
    ];

    protected ConfigurationManagerInterface $configurationManager;
    protected FluxService $configurationService;
    protected WorkspacesAwareRecordService $workspacesAwareRecordService;

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    public function injectConfigurationService(FluxService $configurationService): void
    {
        $this->configurationService = $configurationService;
    }

    public function injectWorkspacesAwareRecordService(WorkspacesAwareRecordService $workspacesAwareRecordService): void
    {
        $this->workspacesAwareRecordService = $workspacesAwareRecordService;
    }

    public function getPreview(ProviderInterface $provider, array $row): string
    {
        $form = $provider->getForm($row);
        $options = $this->getPreviewOptions($form);
        $mode = $this->getOptionMode($options);
        $previewContent = (string) $this->renderPreviewSection($provider, $row, $form);

        if (static::MODE_NONE === $mode || !is_object($form)) {
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
        if (static::MODE_APPEND === $mode) {
            $previewContent = $previewContent . $gridContent;
        } elseif (static::MODE_PREPEND === $mode) {
            $previewContent = $gridContent . $previewContent;
        }

        $previewContent = trim($previewContent);

        return HookHandler::trigger(
            HookHandler::PREVIEW_RENDERED,
            ['form' => $form, 'preview' => $previewContent]
        )['preview'];
    }

    protected function getPreviewOptions(Form $form = null): array
    {
        if (!is_object($form) || !$form->hasOption(static::OPTION_PREVIEW)) {
            return [
                static::OPTION_MODE => $this->getOptionMode(),
                static::OPTION_TOGGLE => $this->getOptionToggle()
            ];
        }

        return (array) $form->getOption(static::OPTION_PREVIEW);
    }

    protected function getOptionMode(array $options = []): string
    {
        if (isset($options[static::OPTION_MODE])) {
            if (static::MODE_APPEND === $options[static::OPTION_MODE] ||
                static::MODE_PREPEND === $options[static::OPTION_MODE] ||
                static::MODE_NONE === $options[static::OPTION_MODE]) {
                return $options[static::OPTION_MODE];
            }
        }

        return static::MODE_APPEND;
    }

    protected function getOptionToggle(array $options = []): bool
    {
        return (boolean) ($options[static::OPTION_TOGGLE] ?? true);
    }

    protected function renderPreviewSection(ProviderInterface $provider, array $row, Form $form = null): ?string
    {
        $templatePathAndFilename = $provider->getTemplatePathAndFilename($row);
        if (!$templatePathAndFilename) {
            return null;
        }
        $extensionKey = $provider->getExtensionKey($row);

        $flexformVariables = $provider->getFlexFormValues($row);
        $templateVariables = $provider->getTemplateVariables($row);
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
            if ($GLOBALS['BE_USER']->workspace > 0) {
                $workspaceVersion = BackendUtility::getWorkspaceVersionOfRecord(
                    $GLOBALS['BE_USER']->workspace,
                    'tt_content',
                    $row['uid']
                );
                if ($workspaceVersion) {
                    $pageUid = $workspaceVersion['pid'] ?? $pageUid;
                }
            }
            $pageLayoutView = $this->getInitializedPageLayoutView($provider, $row);
            if ($pageLayoutView instanceof BackendLayoutRenderer) {
                $content = $pageLayoutView->drawContent(false);
            } elseif (method_exists($pageLayoutView, 'start')) {
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
     * @codeCoverageIgnore
     */
    protected function getCookie(): ?string
    {
        return true === isset($_COOKIE['fluxCollapseStates']) ? $_COOKIE['fluxCollapseStates'] : null;
    }

    /**
     * @return PageLayoutView|BackendLayoutRenderer
     */
    protected function getInitializedPageLayoutView(ProviderInterface $provider, array $row)
    {
        $pageId = (int) $row['pid'];
        $pageRecord = $this->workspacesAwareRecordService->getSingle('pages', '*', $pageId);
        $moduleData = $GLOBALS['BE_USER']->getModuleData('web_layout', '');
        $showHiddenRecords = (int) ($moduleData['tt_content_showHidden'] ?? 1);

        // For all elements to be shown in draft workspaces & to also show hidden elements by default if user hasn't
        // disabled the option analog behavior to the PageLayoutController at the end of menuConfig()
        if ($this->getActiveWorkspaceId() != 0
            || !isset($moduleData['tt_content_showHidden'])
            || $moduleData['tt_content_showHidden'] !== '0'
        ) {
            $moduleData['tt_content_showHidden'] = 1;
        }

        $parentRecordUid = ($row['l18n_parent'] ?? 0) > 0 ? $row['l18n_parent'] : ($row['t3ver_oid'] ?: $row['uid']);

        $backendLayout = $provider->getGrid($row)->buildBackendLayout($parentRecordUid);
        $layoutConfiguration = $backendLayout->getStructure();

        /** @var Features $features */
        $features = GeneralUtility::makeInstance(Features::class);
        $fluidBasedLayoutFeatureEnabled = $features->isFeatureEnabled('fluidBasedPageModule');

        if ($fluidBasedLayoutFeatureEnabled) {
            /** @var SiteFinder $siteFinder */
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByPageId($pageId);
            $language = null;
            if ($row['sys_language_uid'] >= 0) {
                $language = $site->getLanguageById((int) $row['sys_language_uid']);
            }

            /** @var PageLayoutContext $context */
            $context = GeneralUtility::makeInstance(
                PageLayoutContext::class,
                BackendUtility::getRecord('pages', $pageId),
                $backendLayout
            );
            if (isset($language)) {
                 $context = $context->cloneForLanguage($language);
            }

            $configuration = $context->getDrawingConfiguration();
            $configuration->setActiveColumns($backendLayout->getColumnPositionNumbers());

            if (isset($language)) {
                $configuration->setSelectedLanguageId($language->getLanguageId());
            }

            /** @var BackendLayoutRenderer $backendLayoutRenderer */
            $backendLayoutRenderer = GeneralUtility::makeInstance(BackendLayoutRenderer::class, $context);
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
            ...$layoutConfiguration['__items']
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
        $view->CType_labels = [];
        $view->setPageinfo(BackendUtility::readPageAccess($pageId, ''));
        $view->CType_labels = $contentTypeLabels;
        $view->itemLabels = [];
        $view->itemLabels = $itemLabels;
        return $view;
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
    protected function getActiveWorkspaceId(): int
    {
        return (integer) (true === isset($GLOBALS['BE_USER']->workspace) ? $GLOBALS['BE_USER']->workspace : 0);
    }
}
