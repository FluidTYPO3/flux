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
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Lang\LanguageService;

/**
 * @package Flux
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

		$previewContent = $this->renderPreviewSection($provider, $row, $form);
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
	 * @return string
	 */
	protected function renderPreviewSection(ProviderInterface $provider, array $row, Form $form = NULL) {
		$templatePathAndFilename = $provider->getTemplatePathAndFilename($row);
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

		return <<<CONTENT
		<table cellspacing="0" cellpadding="0" id="content-grid-{$row['uid']}" class="flux-grid$collapsedClass">
			<tbody>
				$content
			</tbody>
		</table>
CONTENT;
	}

	/**
	 * @param array $row
	 * @param Column $column
	 * @return string
	 */
	protected function drawGridColumn(array $row, Column $column) {
		$colPosFluxContent = ContentService::COLPOS_FLUXCONTENT;

		$dblist = $this->getInitializedPageLayoutView($row);
		$this->configurePageLayoutViewForLanguageMode($dblist);
		$records = $this->getRecords($dblist, $row, $column->getName());

		$content = '';
		foreach ($records as $record) {
			$content .= $this->drawRecord($row, $column, $record, $dblist);
		}

		$id = 'colpos-' . $colPosFluxContent . '-page-' . $row['pid'] . '--top-' . $row['uid'] . '-' . $column->getName();
		$target = $this->registerTargetContentAreaInSession($row['uid'], $column->getName());

		return <<<CONTENT
		<td colspan="{$column->getColspan()}" rowspan="{$column->getRowspan()}" style="{$column->getStyle()}">
			<div class="fce-header t3-row-header t3-page-colHeader t3-page-colHeader-label">
				<div>{$column->getLabel()}</div>
			</div>
			<div class="fce-container t3-page-ce-wrapper">
				<div class="t3-page-ce ui-draggable" data-page="{$target}">
					<div class="t3-page-ce-dropzone ui-droppable" id="{$id}" style="min-height: 16px;">
						<div class="t3-page-ce-wrapper-new-ce">
							{$this->drawNewIcon($row, $column)}
							{$this->drawPasteIcon($row, $column)}
							{$this->drawPasteIcon($row, $column, TRUE)}
						</div>
					</div>
				</div>
				$content
			</div>
		</td>
CONTENT;
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

		return <<<CONTENT
		<div class="t3-page-ce$disabledClass {$record['_CSSCLASS']} ui-draggable" id="element-tt_content-{$record['uid']}" data-table="tt_content" data-uid="{$record['uid']}">
			$element
			<div class="t3-page-ce-dropzone ui-droppable"
				 id="colpos-$colPosFluxContent-page-{$parentRow['pid']}-{$parentRow['uid']}-after-{$record['uid']}"
				 style="min-height: 16px;">
				<div class="t3-page-ce-wrapper-new-ce">
					{$this->drawNewIcon($parentRow, $column, $record['uid'])}
					{$this->drawPasteIcon($parentRow, $column, FALSE, $record)}
					{$this->drawPasteIcon($parentRow, $column, TRUE, $record)}
				</div>
			</div>
		</div>
CONTENT;
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
		$disableMoveAndNewButtons = FALSE;
		$langMode = $dblist->tt_contentConfig['languageMode'];
		$dragDropEnabled = FALSE;
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
		$icon = IconUtility::getSpriteIcon('actions-document-new');
		$legacy = $this->isLegacyCoreVersion();
		$uri = (FALSE === $legacy ? $this->getNewLink($row, $after, $columnName) : $this->getNewLinkLegacy($row, $after, $columnName));
		$title = $this->getLanguageService()->getLL('newRecordHere');
		$inner = $this->getLanguageService()->getLL('content');
		$link = '<a href="#" onclick="window.location.href=\'' . htmlspecialchars($uri) . '\'" title="' . $title .
			'" class="btn btn-default btn-sm">' . $icon . ' ' . $inner . '</a>';
		return $link;
	}

	/**
	 * @return boolean
	 */
	protected function isLegacyCoreVersion() {
		return (FALSE === version_compare(VersionNumberUtility::getNumericTypo3Version(), '7.1.0', '>='));
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
			'defVals[tt_content][tx_flux_parent]' => $row['uid'],
			'defVals[tt_content][tx_flux_column]' => $columnName,
			'returnUrl' => $returnUri
		));
		return $uri;
	}

	/**
	 * Generate a link valid on TYPO3 6.2
	 *
	 * @param array $row
	 * @param integer $after
	 * @param string $columnName
	 * @return string
	 */
	protected function getNewLinkLegacy(array $row, $after, $columnName) {
		$returnUri = rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI'));
		$uri = 'db_new_content_el.php?id=' . $row['pid'] .
			'&uid_pid=' . $after .
			'&colPos=' . ContentService::COLPOS_FLUXCONTENT .
			'&sys_language_uid=' . $row['sys_language_uid'] .
			'&defVals[tt_content][tx_flux_parent]=' . $row['uid'] .
			'&defVals[tt_content][tx_flux_column]=' . $columnName .
			'&returnUrl=' . $returnUri;
		return $uri;
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
			$row['uid'] . (FALSE === empty($columnName) ? '-' . $columnName : '');
		return ClipBoardUtility::createIconWithUrl($relativeTo, $reference);
	}

	/**
	 * @param array $row
	 * @param string $content
	 * @return string
	 */
	protected function drawGridToggle(array $row, $content) {
		$collapsedClass = TRUE === $this->isRowCollapsed($row) ? 'expand' : 'collapse';

		return <<<CONTENT
		<div class="grid-visibility-toggle">
			<div class="toggle-content" data-uid="{$row['uid']}">
				<span class="t3-icon t3-icon-actions t3-icon-view-table-$collapsedClass"></span>
			</div>
			$content
		</div>
CONTENT;
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
		$condition = "AND tx_flux_parent = '" . $row['uid'] . "' AND tx_flux_column = '" . $area . "' ";
		$condition .= "AND colPos = '" . ContentService::COLPOS_FLUXCONTENT . "' ";
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
		/** @var $dblist PageLayoutView */
		$dblist = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager')->get('TYPO3\CMS\Backend\View\PageLayoutView');
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
		$dblist->tt_contentConfig['activeCols'] .= ',' . ContentService::COLPOS_FLUXCONTENT;
		$dblist->CType_labels = array();
		$dblist->pidSelect = "pid = '" . $row['pid'] . "'";
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

}
