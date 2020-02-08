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
use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Lang\LanguageService;
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

    /**
     * @var array
     */
    protected $templates = [
        'gridToggle' => '<div class="grid-visibility-toggle" data-toggle-uid="%s">
							%s
						</div>',
        'link' => '<a href="%s" title="%s"
                      class="btn btn-default btn-sm">%s %s</a>'
    ];

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var FluxService
     */
    protected $configurationService;

    /**
     * @var WorkspacesAwareRecordService
     */
    protected $workspacesAwareRecordService;

    /**
     * @var RecordService
     */
    protected $recordService;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @param WorkspacesAwareRecordService $workspacesAwareRecordService
     * @return void
     */
    public function injectWorkspacesAwareRecordService(WorkspacesAwareRecordService $workspacesAwareRecordService)
    {
        $this->workspacesAwareRecordService = $workspacesAwareRecordService;
    }

    /**
     * @param RecordService $recordService
     * @return void
     */
    public function injectRecordService(RecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * @param ProviderInterface $provider
     * @param array $row
     * @return string
     */
    public function getPreview(ProviderInterface $provider, array $row)
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
        if (in_array($row['uid'], (array) json_decode((string) $_COOKIE['fluxCollapseStates']))) {
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

        return HookHandler::trigger(HookHandler::PREVIEW_RENDERED, ['form' => $form, 'preview' => $previewContent])['preview'];
    }

    /**
     * @param Form $form
     * @return array
     */
    protected function getPreviewOptions(Form $form = null)
    {
        if (!is_object($form) || !$form->hasOption(static::OPTION_PREVIEW)) {
            return [
                static::OPTION_MODE => $this->getOptionMode(),
                static::OPTION_TOGGLE => $this->getOptionToggle()
            ];
        }

        return $form->getOption(static::OPTION_PREVIEW);
    }

    /**
     * @param array $options
     * @return string
     */
    protected function getOptionMode(array $options = null)
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

    /**
     * @param array $options
     * @return boolean
     */
    protected function getOptionToggle(array $options = null)
    {
        return (boolean) ($options[static::OPTION_TOGGLE] ?? true);
    }

    /**
     * @param ProviderInterface $provider
     * @param array $row
     * @param Form $form
     * @return string|NULL
     */
    protected function renderPreviewSection(ProviderInterface $provider, array $row, Form $form = null)
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
            $label = $this->getLanguageService()->sL($formLabel);
            $variables['label'] = $label;
        }

        $this->getRenderingContext()->setControllerName($provider->getControllerNameFromRecord($row));
        $this->getRenderingContext()->setControllerAction($provider->getControllerActionFromRecord($row));
        $this->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName(
            ExtensionNamingUtility::getExtensionKey($extensionKey)
        );
        $this->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($templatePathAndFilename);
        return $this->renderSection('Preview', $variables, true);
    }

    /**
     * @param ProviderInterface $provider
     * @param array $row
     * @param Form $form
     * @return string
     */
    protected function renderGrid(ProviderInterface $provider, array $row, Form $form)
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
                $placeholder = BackendUtility::getMovePlaceholder('tt_content', $row['uid'], 'pid', $GLOBALS['BE_USER']->workspace);
                $pageUid = $placeholder['pid'] ?? $pageUid;
            }
            $pageLayoutView = $this->getInitializedPageLayoutView($provider, $row);
            $pageLayoutView->start($pageUid, 'tt_content', 0);
            if (method_exists($pageLayoutView, 'generateList')) {
                $pageLayoutView->generateList();
                $content .= $pageLayoutView->HTMLcode;
            } else {
                $content .= $pageLayoutView->getTable_tt_content($pageUid);
            }

            $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'] = $tcaBackup;

        }
        return $content;
    }

    /**
     * @param array $row
     * @param string $content
     * @return string
     */
    protected function drawGridToggle(array $row, $content)
    {
        return sprintf($this->templates['gridToggle'], $row['uid'], $content);
    }

    /**
     * @param array $row
     * @return string
     */
    protected function isRowCollapsed(array $row)
    {
        $collapsed = false;
        $cookie = $this->getCookie();
        if (null !== $_COOKIE) {
            $cookie = json_decode(urldecode($cookie));
            $collapsed = in_array($row['uid'], (array) $cookie);
        }
        return $collapsed;
    }

    /**
     * @return string|NULL
     */
    protected function getCookie()
    {
        return true === isset($_COOKIE['fluxCollapseStates']) ? $_COOKIE['fluxCollapseStates'] : null;
    }

    /**
     * @param ProviderInterface $provider
     * @param array $row
     * @return PageLayoutView
     */
    protected function getInitializedPageLayoutView(ProviderInterface $provider, array $row)
    {
        $pageRecord = $this->workspacesAwareRecordService->getSingle('pages', '*', $row['pid']);
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

        $dblist = $this->getPageLayoutView($provider, $row);
        $layoutConfiguration = $provider->getGrid($row)->buildExtendedBackendLayoutArray($parentRecordUid);

        if (!empty($layoutConfiguration['__items'])) {
            array_push(
                $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'],
                ...$layoutConfiguration['__items']
            );
        }

        $columnsAsCSV = implode(',', $layoutConfiguration['__colPosList']);

        $dblist->script = 'db_layout.php';
        $dblist->showIcon = 1;
        $dblist->setLMargin = 0;
        $dblist->doEdit = 1;
        $dblist->no_noWrap = 1;
        $dblist->ext_CALC_PERMS = $this->getBackendUser()->calcPerms($pageRecord);
        $dblist->id = $row['pid'];
        $dblist->table = 'tt_content';
        $dblist->tableList = 'tt_content';
        $dblist->currentTable = 'tt_content';
        $dblist->tt_contentConfig['showCommands'] = 1;
        $dblist->tt_contentConfig['showInfo'] = 1;
        $dblist->tt_contentConfig['single'] = 0;
        $dblist->nextThree = 1;
        $dblist->tt_contentConfig['sys_language_uid'] = (int) $moduleData['language'];
        $dblist->tt_contentConfig['showHidden'] = $showHiddenRecords;
        $dblist->tt_contentConfig['activeCols'] = $columnsAsCSV;
        $dblist->tt_contentConfig['cols'] = $columnsAsCSV;
        $dblist->CType_labels = [];
        $dblist->setPageinfo(BackendUtility::readPageAccess($row['pid'], ''));
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $val) {
            $dblist->CType_labels[$val[1]] = $this->getLanguageService()->sL($val[0]);
        }
        $dblist->itemLabels = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns'] as $name => $val) {
            $dblist->itemLabels[$name] = ($val['label'] ?? false) ? $this->getLanguageService()->sL($val['label']) : '';
        }
        return $dblist;
    }

    /**
     * @param ProviderInterface $provider
     * @param array $record
     * @return PageLayoutView
     */
    protected function getPageLayoutView(ProviderInterface $provider, array $record)
    {
        $eventDispatcher = null;
        if (class_exists(EventDispatcher::class)) {
            $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        }

        /** @var PageLayoutView $view */
        $view = GeneralUtility::makeInstance(PageLayoutView::class, $eventDispatcher);
        $view->setProvider($provider);
        $view->setRecord($record);
        return $view;
    }

    /**
     * @codeCoverageIgnore
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @codeCoverageIgnore
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @codeCoverageIgnore
     * @return integer
     */
    protected function getActiveWorkspaceId()
    {
        return (integer) (true === isset($GLOBALS['BE_USER']->workspace) ? $GLOBALS['BE_USER']->workspace : 0);
    }
}
