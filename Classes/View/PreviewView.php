<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ClipBoardUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Lang\LanguageService;

/**
 * PreviewView
 */
class PreviewView {

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
	protected $templates = array(
		'grid' => '<table cellspacing="0" cellpadding="0" id="content-grid-%s" class="flux-grid%s">
						<tbody>
							%s
						</tbody>
					</table>',
		'gridColumn' => '<td colspan="%s" rowspan="%s" style="%s">
								<div data-colpos="%s" class="t3js-sortable t3js-sortable-lang t3js-sortable-lang-%s t3-page-ce-wrapper ui-sortable" data-language-uid="%s">
									<div class="fce-header t3-row-header t3-page-colHeader t3-page-colHeader-label">
										<div>%s</div>
									</div>
									<div class="t3-page-ce t3js-page-ce" data-page="%s">
										<div class="t3js-page-new-ce t3-page-ce-wrapper-new-ce" id="%s" style="display: block;">
										   %s
										</div>
										<div class="t3-page-ce-dropzone-available t3js-page-ce-dropzone-available" ></div>
									</div>
									%s
								</div>
							</td>',
		'record' => '<div class="t3-page-ce%s %s t3js-page-ce t3js-page-ce-sortable" id="element-tt_content-%s" data-table="tt_content" data-uid="%s">
						%s
						<div class="t3js-page-new-ce t3-page-ce-wrapper-new-ce" id="colpos-%s-page-%s-%s-after-%s" style="display: block;">
							%s
						</div>
						<div class="t3-page-ce-dropzone-available t3js-page-ce-dropzone-available"></div>
					</div>',
		'gridToggle' => '<div class="grid-visibility-toggle">
							<div class="toggle-content" data-uid="%s">
								<span class="t3-icon t3-icon-actions t3-icon-view-table-%s"></span>
							</div>
							%s
						</div>',
		'link' => '<a href="#" onclick="window.location.href=\'%s\'" title="%s" class="btn btn-default btn-sm">%s %s</a>'
	);

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
	 * @param ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param WorkspacesAwareRecordService $workspacesAwareRecordService
	 * @return void
	 */
	public function injectWorkspacesAwareRecordService(WorkspacesAwareRecordService $workspacesAwareRecordService) {
		$this->workspacesAwareRecordService = $workspacesAwareRecordService;
	}

	/**
	 * @param ProviderInterface $provider
	 * @param array $row
	 * @return string
	 */
	public function getPreview(ProviderInterface $provider, array $row) {
		$form = $provider->getForm($row);
		$options = $this->getPreviewOptions($form);
		$mode = $this->getOptionMode($options);
		$previewContent = (string) $this->renderPreviewSection($provider, $row, $form);

		if (self::MODE_NONE === $mode || FALSE === is_object($form)) {
			return $previewContent;
		}

		$gridContent = $this->renderGrid($provider, $row, $form);
		if (self::MODE_APPEND === $mode) {
			$previewContent = $previewContent . $gridContent;
		} else if (self::MODE_PREPEND === $mode) {
			$previewContent = $gridContent . $previewContent;
		}

		$previewContent = trim($previewContent);

		return $previewContent;
	}

	/**
	 * @param Form $form
	 * @return array
	 */
	protected function getPreviewOptions(Form $form = NULL) {
		if (FALSE === is_object($form) || FALSE === $form->hasOption(self::OPTION_PREVIEW)) {
			return array(
				self::OPTION_MODE => $this->getOptionMode(),
				self::OPTION_TOGGLE => $this->getOptionToggle()
			);
		}

		return $form->getOption(self::OPTION_PREVIEW);
	}

	/**
	 * @param array $options
	 * @return string
	 */
	protected function getOptionMode(array $options = NULL) {
		if (TRUE === isset($options[self::OPTION_MODE])) {
			if (self::MODE_APPEND === $options[self::OPTION_MODE] ||
				self::MODE_PREPEND === $options[self::OPTION_MODE] ||
				self::MODE_NONE === $options[self::OPTION_MODE]) {
				return $options[self::OPTION_MODE];
			}
		}

		return self::MODE_APPEND;
	}

	/**
	 * @param array $options
	 * @return boolean
	 */
	protected function getOptionToggle(array $options = NULL) {
		if (TRUE === isset($options[self::OPTION_TOGGLE])) {
			return (boolean) $options[self::OPTION_TOGGLE];
		}

		return TRUE;
	}

	/**
	 * @param ProviderInterface $provider
	 * @param array $row
	 * @param Form $form
	 * @return string|NULL
	 */
	protected function renderPreviewSection(ProviderInterface $provider, array $row, Form $form = NULL) {
		$templatePathAndFilename = $provider->getTemplatePathAndFilename($row);
		if (NULL === $templatePathAndFilename) {
			return NULL;
		}
		$extensionKey = $provider->getExtensionKey($row);
		$paths = $provider->getTemplatePaths($row);

		$flexformVariables = $provider->getFlexFormValues($row);
		$templateVariables = $provider->getTemplateVariables($row);
		$variables = RecursiveArrayUtility::merge($templateVariables, $flexformVariables);
		$variables['row'] = $row;
		$variables['record'] = $row;

		if (TRUE === is_object($form)) {
			$formLabel = $form->getLabel();
			$label = LocalizationUtility::translate($formLabel, $extensionKey);
			$variables['label'] = $label;
		}

		$templatePaths = new TemplatePaths($paths);
		$viewContext = new ViewContext($templatePathAndFilename, $extensionKey, self::CONTROLLER_NAME);
		$viewContext->setTemplatePaths($templatePaths);
		$viewContext->setVariables($variables);
		$view = $this->configurationService->getPreparedExposedTemplateView($viewContext);

		$existingContentObject = $this->configurationManager->getContentObject();
		$contentObject = new ContentObjectRenderer();
		$contentObject->start($row, $provider->getTableName($row));
		$this->configurationManager->setContentObject($contentObject);
		$previewContent = $view->renderStandaloneSection(self::PREVIEW_SECTION, $variables, TRUE);
		$this->configurationManager->setContentObject($existingContentObject);
		$previewContent = trim($previewContent);

		return $previewContent;
	}

	/**
	 * @param ProviderInterface $provider
	 * @param array $row
	 * @param Form $form
	 * @return string
	 */
	protected function renderGrid(ProviderInterface $provider, array $row, Form $form) {
		$grid = $provider->getGrid($row);
		$content = '';
		if (TRUE === $grid->hasChildren()) {
			$workspaceVersionOfRow = $this->workspacesAwareRecordService->getSingle('tt_content', '*', $row['uid']);
			if ((integer) $workspaceVersionOfRow['pid'] === -1 && !empty($workspaceVersionOfRow['t3ver_oid'])) {
				$originalRecord = BackendUtility::getRecord('tt_content', $workspaceVersionOfRow['t3ver_oid'], '*', '', FALSE);
				$workspaceVersionOfRow['pid'] = $originalRecord['pid'];
			}
			$content = $this->drawGrid($workspaceVersionOfRow, $grid, $form);

			$options = $this->getPreviewOptions($form);
			if (TRUE === $this->getOptionToggle($options)) {
				$content = $this->drawGridToggle($workspaceVersionOfRow, $content);
			}
		}
		return $content;
	}

	/**
	 * @param array $row
	 * @param Grid $grid
	 * @param Form $form
	 * @return string
	 */
	protected function drawGrid(array $row, Grid $grid, Form $form) {
		$options = $this->getPreviewOptions($form);
		$canToggle = $this->getOptionToggle($options);
		$isCollapsed = $this->isRowCollapsed($row);
		$collapsedClass = TRUE === $canToggle && TRUE === $isCollapsed ? ' flux-grid-hidden' : '';
		$gridRows = $grid->getRows();
		$content = '';
		foreach ($gridRows as $gridRow) {
			$content .= '<tr>';
			$columns = $gridRow->getColumns();
			foreach ($columns as $column) {
				$content .= $this->drawGridColumn($row, $column);
			}
			$content .= '</tr>';
		}

		return sprintf($this->templates['grid'], $row['uid'], $collapsedClass, $content);
	}

	/**
	 * @param array $row
	 * @param Column $column
	 * @return string
	 */
	protected function drawGridColumn(array $row, Column $column) {
		$colPosFluxContent = ContentService::COLPOS_FLUXCONTENT;

		$columnName = $column->getName();
		$dblist = $this->getInitializedPageLayoutView($row);
		$this->configurePageLayoutViewForLanguageMode($dblist);
		$records = $this->getRecords($dblist, $row, $columnName);

		$content = '';
		foreach ($records as $record) {
			$content .= $this->drawRecord($row, $column, $record, $dblist);
		}
		// Add localize buttons for flux container elements
		if (isset($row['l18n_parent']) && 0 < $row['l18n_parent']) {
			if (TRUE === empty($dblist->defLangBinding)) {
				$partialOriginalRecord = array('uid' => $row['l18n_parent'], 'pid' => $row['pid']);
				$childrenInDefaultLanguage = $this->getRecords($dblist, $partialOriginalRecord, $columnName);
				$childrenUids = array();
				foreach ($childrenInDefaultLanguage as $child) {
					$childrenUids[] = $child['uid'];
				}
				$langPointer = $row['sys_language_uid'];
				$localizeButton = $dblist->newLanguageButton(
					$dblist->getNonTranslatedTTcontentUids($childrenUids, $dblist->id, $langPointer),
					$langPointer
				);
				$content .= $localizeButton;
			}
		}
		$id = 'colpos-' . $colPosFluxContent . '-page-' . $row['pid'] . '--top-' . $row['uid'] . '-' . $columnName;
		$target = $this->registerTargetContentAreaInSession($row['uid'], $columnName);

		return $this->parseGridColumnTemplate($row, $column, $colPosFluxContent, $dblist, $target, $id, $content);
	}

	/**
	 * @param array $parentRow
	 * @param Column $column
	 * @param array $record
	 * @param PageLayoutView $dblist
	 * @return string
	 */
	protected function drawRecord(array $parentRow, Column $column, array $record, PageLayoutView $dblist) {
		$colPosFluxContent = ContentService::COLPOS_FLUXCONTENT;
		$disabledClass = FALSE === empty($record['isDisabled']) ? ' t3-page-ce-hidden' : '';
		$element = $this->drawElement($record, $dblist);
		if (0 === (integer) $dblist->tt_contentConfig['languageMode']) {
			$element = '<div class="t3-page-ce-dragitem">' . $element . '</div>';
		}

		return sprintf($this->templates['record'], $disabledClass, $record['_CSSCLASS'], $record['uid'], $record['uid'],
			$element, $colPosFluxContent, $parentRow['pid'], $parentRow['uid'], $record['uid'],
			$this->drawNewIcon($parentRow, $column, $record['uid']).
			$this->drawPasteIcon($parentRow, $column, FALSE, $record).
			$this->drawPasteIcon($parentRow, $column, TRUE, $record));

	}

	/**
	 * @param array $row
	 * @param PageLayoutView $dblist
	 * @return string
	 */
	protected function drawElement(array $row, PageLayoutView $dblist) {
		$footerRenderMethod = new \ReflectionMethod($dblist, 'tt_content_drawFooter');
		$footerRenderMethod->setAccessible(TRUE);
		$space = 0;
		$langMode = $dblist->tt_contentConfig['languageMode'];
		$dragDropEnabled = $this->getBackendUser()->doesUserHaveAccess($dblist->getPageinfo(), Permission::CONTENT_EDIT);
		$disableMoveAndNewButtons = !$dragDropEnabled;

		// Necessary for edit button in workspace.
		$dblist->tt_contentData['nextThree'][$row['uid']] = $row['uid'];

		$rendered = $dblist->tt_content_drawHeader($row, $space, $disableMoveAndNewButtons, $langMode, $dragDropEnabled);
		$rendered .= '<div class="t3-page-ce-body-inner">' . $dblist->tt_content_drawItem($row) . '</div>';
		$rendered .= $footerRenderMethod->invokeArgs($dblist, array($row));
		$rendered .= '</div>';
		return $rendered;
	}

	/**
	 * @param array $row
	 * @param Column $column
	 * @param integer $after
	 * @return string
	 */
	protected function drawNewIcon(array $row, Column $column, $after = 0) {
		$columnName = $column->getName();
		$after = (FALSE === empty($columnName) && FALSE === empty($after)) ? '-' . $after : $row['pid'];
		$iconFactory = GeneralUtility::makeInstance(IconFactory::class);
		$icon = $iconFactory->getIcon('actions-document-new', Icon::SIZE_SMALL)->render();
		$uri = $this->getNewLink($row, $after, $columnName);
		$title = $this->getLanguageService()->getLL('newRecordHere');
		$inner = $this->getLanguageService()->getLL('content');
		return sprintf($this->templates['link'], htmlspecialchars($uri), $title, $icon, $inner);
	}

	/**
	 * Generate a link valid on TYPO3 7.0+
	 *
	 * @param array $row
	 * @param integer $after
	 * @param string $columnName
	 * @return string
	 */
	protected function getNewLink(array $row, $after, $columnName) {
		$returnUri = str_replace('/' . TYPO3_mainDir, '', GeneralUtility::getIndpEnv('REQUEST_URI'));
		$uri = BackendUtility::getModuleUrl('new_content_element', array(
			'id' => $row['pid'],
			'uid_pid' => $after,
			'colPos' => ContentService::COLPOS_FLUXCONTENT,
			'sys_language_uid' => $row['sys_language_uid'],
			'defVals[tt_content][tx_flux_parent]' => $this->getFluxParentUid($row),
			'defVals[tt_content][tx_flux_column]' => $columnName,
			'returnUrl' => $returnUri
		));
		return $uri;
	}

	/**
	 * @param array $row
	 * @param string $content
	 * @return string
	 */
	protected function drawGridToggle(array $row, $content) {
		$collapsedClass = TRUE === $this->isRowCollapsed($row) ? 'expand' : 'collapse';
		return sprintf($this->templates['gridToggle'], $row['uid'], $collapsedClass, $content);
	}

	/**
	 * @param array $row
	 * @return string
	 */
	protected function isRowCollapsed(array $row) {
		$collapsed = FALSE;
		$cookie = $this->getCookie();
		if (NULL !== $_COOKIE) {
			$cookie = json_decode(urldecode($cookie));
			$collapsed = in_array($row['uid'], (array) $cookie);
		}
		return $collapsed;
	}

	/**
	 * @return string|NULL
	 */
	protected function getCookie() {
		return TRUE === isset($_COOKIE['fluxCollapseStates']) ? $_COOKIE['fluxCollapseStates'] : NULL;
	}

	/**
	 * @param PageLayoutView $view
	 * @param array $row
	 * @param string $area
	 * @return array
	 */
	protected function getRecords(PageLayoutView $view, array $row, $area) {
		// The following solution is half lifted from \TYPO3\CMS\Backend\View\PageLayoutView::getContentRecordsPerColumn
		// and relies on TYPO3 core query parts for enable-clause-, language- and versioning placeholders. All that needs
		// to be done after this, is filter the array according to moved/deleted placeholders since TYPO3 will not remove
		// records based on them having remove placeholders.
		$condition = "AND tx_flux_parent = '" . $this->getFluxParentUid($row) . "' AND tx_flux_column = '" . $area . "' ";
		$condition .= "AND colPos = '" . ContentService::COLPOS_FLUXCONTENT . "' ";
		$condition .= (1 === $view->tt_contentConfig['showHidden']) ? '' : 'AND hidden = 0 ';
		$queryParts = $view->makeQueryArray('tt_content', $row['pid'], $condition);
		$result = $this->getDatabaseConnection()->exec_SELECT_queryArray($queryParts);
		$rows = $view->getResult($result);
		$rows = $this->processRecordOverlays($rows, $view);
		return $rows;
	}

	/**
	 * @param array $rows
	 * @param PageLayoutView $view
	 * @return array
	 */
	protected function processRecordOverlays(array $rows, PageLayoutView $view) {
		foreach ($rows as $index => &$record) {
			$record = $this->getWorkspaceVersionOfRecordOrRecordItself($record);
			BackendUtility::movePlhOL('tt_content', $record);
			if (TRUE === $this->isDeleteOrMovePlaceholder($record)) {
				unset($rows[$index]);
			} else {
				$record['isDisabled'] = $view->isDisabled('tt_content', $record);
			}
		}
		return $rows;
	}

	/**
	 * @param array $record
	 * @return array
	 */
	protected function getWorkspaceVersionOfRecordOrRecordItself(array $record) {
		$workspaceId = $this->getActiveWorkspaceId();
		if (0 < $workspaceId) {
			$workspaceRecord = BackendUtility::getWorkspaceVersionOfRecord($GLOBALS['BE_USER']->workspace, 'tt_content', $record['uid']);
			$record = (FALSE !== $workspaceRecord ? $workspaceRecord : $record);
		}
		return $record;
	}

	/**
	 * @param mixed $record
	 * @return boolean
	 */
	protected function isDeleteOrMovePlaceholder($record) {
		return (TRUE === empty($record) || VersionState::DELETE_PLACEHOLDER === (integer) $record['t3ver_state']);
	}

	/**
	 * @param array $row
	 * @return PageLayoutView
	 */
	protected function getInitializedPageLayoutView(array $row) {
		$pageRecord = $this->workspacesAwareRecordService->getSingle('pages', '*', $row['pid']);
		$moduleData = $GLOBALS['BE_USER']->getModuleData('web_layout', '');

		// For all elements to be shown in draft workspaces & to also show hidden elements by default if user hasn't disabled the option
		// analog behavior to the PageLayoutController at the end of menuConfig()
		if ($this->getBackendUser()->workspace != 0 || FALSE === isset($this->moduleData['tt_content_showHidden']) || $this->moduleData['tt_content_showHidden'] !== '0') {
			$moduleData['tt_content_showHidden'] = 1;
		}

		/** @var $dblist PageLayoutView */
		$dblist = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class)->get(\FluidTYPO3\Flux\View\PageLayoutView::class);

		$dblist->backPath = $GLOBALS['BACK_PATH'];
		$dblist->script = 'db_layout.php';
		$dblist->showIcon = 1;
		$dblist->setLMargin = 0;
		$dblist->doEdit = 1;
		$dblist->no_noWrap = 1;
		$dblist->ext_CALC_PERMS = $this->getBackendUser()->calcPerms($pageRecord);
		$dblist->id = $row['pid'];
		$dblist->nextThree = 1;
		$dblist->table = 'tt_content';
		$dblist->tableList = 'tt_content';
		$dblist->currentTable = 'tt_content';
		$dblist->tt_contentConfig['showCommands'] = 1;
		$dblist->tt_contentConfig['showInfo'] = 1;
		$dblist->tt_contentConfig['single'] = 0;
		$dblist->tt_contentConfig['showHidden'] = intval($moduleData['tt_content_showHidden']);
		$dblist->tt_contentConfig['activeCols'] .= ',' . ContentService::COLPOS_FLUXCONTENT;
		$dblist->CType_labels = array();
		$dblist->pidSelect = "pid = '" . $row['pid'] . "'";
		$dblist->setPageinfo(BackendUtility::readPageAccess($row['pid'], ''));
		$dblist->initializeLanguages();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $val) {
			$dblist->CType_labels[$val[1]] = $this->getLanguageService()->sL($val[0]);
		}
		$dblist->itemLabels = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns'] as $name => $val) {
			$dblist->itemLabels[$name] = $this->getLanguageService()->sL($val['label']);
		}
		return $dblist;
	}

	/**
	 * @param PageLayoutView $view
	 * @return PageLayoutView
	 */
	protected function configurePageLayoutViewForLanguageMode(PageLayoutView $view) {
		// Initializes page languages and icons so they are available in PageLayoutView if languageMode is set.
		$view->initializeLanguages();
		$modSettings = $this->getPageModuleSettings();
		if (2 === intval($modSettings['function'])) {
			$view->tt_contentConfig['single'] = 0;
			$view->tt_contentConfig['languageMode'] = 1;
			$view->tt_contentConfig['languageCols'] = array(0 => $this->getLanguageService()->getLL('m_default'));
			$view->tt_contentConfig['languageColsPointer'] = $modSettings['language'];
		}
		return $view;
	}

	/**
	 * @codeCoverageIgnore
	 * @return array
	 */
	protected function getPageModuleSettings() {
		return $GLOBALS['SOBE']->MOD_SETTINGS;
	}

	/**
	 * @codeCoverageIgnore
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @codeCoverageIgnore
	 * @return BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * @codeCoverageIgnore
	 * @return LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}

	/**
	 * @codeCoverageIgnore
	 * @return integer
	 */
	protected function getActiveWorkspaceId() {
		return (integer) (TRUE === isset($GLOBALS['BE_USER']->workspace) ? $GLOBALS['BE_USER']->workspace : 0);
	}

	/**
	 * @codeCoverageIgnore
	 * @param integer $contentElementUid
	 * @param string $areaName
	 * @return integer
	 */
	protected function registerTargetContentAreaInSession($contentElementUid, $areaName) {
		if ('' === session_id()) {
			session_start();
		}
		$integer = MiscellaneousUtility::generateUniqueIntegerForFluxArea($contentElementUid, $areaName);
		$_SESSION['target' . $integer] = array($contentElementUid, $areaName);
		return $integer;
	}

	/**
	 * @param array $row
	 * @return mixed
	 */
	protected function getFluxParentUid(array $row) {
		return $row['t3ver_oid'] ?  $row['t3ver_oid'] : $row['uid'];
	}

	/**
	 * @param array $row
	 * @param Column $column
	 * @param boolean $reference
	 * @param array $relativeTo
	 * @return string
	 */
	protected function drawPasteIcon(array $row, Column $column, $reference = FALSE, array $relativeTo = array()) {
		$command = TRUE === $reference ? 'reference' : 'paste';
		$relativeUid = TRUE === isset($relativeTo['uid']) ? $relativeTo['uid'] : 0;
		$columnName = $column->getName();
		$relativeTo = $row['pid'] . '-' . $command . '-' . $relativeUid . '-' .
				$row['uid'] . (FALSE === empty($columnName) ? '-' . $columnName : '') . '-' . ContentService::COLPOS_FLUXCONTENT;
		return ClipBoardUtility::createIconWithUrl($relativeTo, $reference);
	}

	/**
	 * @param array $row
	 * @param Column $column
	 * @param integer $colPosFluxContent
	 * @param PageLayoutView $dblist
	 * @param integer $target
	 * @param string $id
	 * @param string $content
	 * @return string
	 */
	protected function parseGridColumnTemplate(array $row, Column $column, $colPosFluxContent, $dblist, $target, $id, $content) {
		$label = $column->getLabel();
		if (strpos($label, 'LLL:') === 0) {
			$label = LocalizationUtility::translate(
				$label,
				ExtensionNamingUtility::getExtensionName($column->getExtensionName())
			);
			if (empty($label)) {
				$label = $column->getLabel();
			}
		}

		// this variable defines if this drop-area gets activated on drag action
		// of a ce with the same data-language_uid
		$templateClassJsSortableLanguageId = $row['sys_language_uid'];

		// this variable defines which drop-areas will be activated
		// with a drag action of this element
		$templateDataLanguageUid = $row['sys_language_uid'];

		// but for language mode all (uid -1):
		if ((integer) $row['sys_language_uid'] === -1) {
			/** @var \TYPO3\CMS\Backend\Controller\PageLayoutController $pageLayoutController */
			$pageLayoutController = $GLOBALS['SOBE'];
			$isColumnView = ((integer) $pageLayoutController->MOD_SETTINGS['function'] === 1);
			$isLanguagesView = ((integer) $pageLayoutController->MOD_SETTINGS['function'] === 2);
			if ($isColumnView) {
				$templateClassJsSortableLanguageId = $pageLayoutController->current_sys_language;
				$templateDataLanguageUid = $pageLayoutController->current_sys_language;
			} elseif ($isLanguagesView) {
				// If this is a language-all (uid -1) grid-element in languages-view
				// we use language-uid 0 for this elements drop-areas.
				// This can be done because a ce with language-uid -1 in languages view
				// is in TYPO3 7.6.4 only displayed in the default-language-column (maybe a bug atm.?).
				// Additionally there is no access to the information which
				// language column is currently rendered from here!
				// ($lP in typo3/cms/typo3/sysext/backend/Classes/View/PageLayoutView.php L485)
				$templateClassJsSortableLanguageId = 0;
				$templateDataLanguageUid = 0;
			}
		}

		return sprintf($this->templates['gridColumn'],
			$column->getColspan(),
			$column->getRowspan(),
			$column->getStyle(),
			$colPosFluxContent,
			$templateClassJsSortableLanguageId,
			$templateDataLanguageUid,
			$label,
			$target,
			$id,
			$this->drawNewIcon($row, $column). $this->drawPasteIcon($row, $column) . $this->drawPasteIcon($row, $column, TRUE),
			$content
		);
	}

}
