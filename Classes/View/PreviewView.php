<?php
namespace FluidTYPO3\Flux\View;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

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
		$templateSource = $provider->getTemplateSource($row);
		if (TRUE === empty($templateSource)) {
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

		$view = $this->configurationService->getPreparedExposedTemplateView($extensionKey, self::CONTROLLER_NAME, $paths, $variables);
		$view->setTemplateSource($templateSource);

		$existingContentObject = $this->configurationManager->getContentObject();
		$contentObject = new ContentObjectRenderer();
		$contentObject->start($row, $provider->getTableName($row));
		$this->configurationManager->setContentObject($contentObject);
		$previewContent = $view->renderStandaloneSection(self::PREVIEW_SECTION, $variables);
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
		if (FALSE === $grid->hasChildren()) {
			return '';
		}
		$workspaceVersionOfRow = $this->workspacesAwareRecordService->getSingle('tt_content', '*', $row['uid']);
		$content = $this->drawGrid($workspaceVersionOfRow, $grid, $form);

		$options = $this->getPreviewOptions($form);
		if (TRUE === $this->getOptionToggle($options)) {
			$content = $this->drawGridToggle($workspaceVersionOfRow, $content);
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
		$collapsedClass = '';
		$options = $this->getPreviewOptions($form);
		if (TRUE === $this->getOptionToggle($options) && TRUE === $this->isRowCollapsed($row)) {
			$collapsedClass = ' flux-grid-hidden';
		}

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

		$dblist = $this->getInitializePageLayoutView($row);
		$this->configurePageLayoutViewForLanguageMode($dblist);
		$records = $this->getRecords($dblist, $row, $column->getName());

		$content = '';
		foreach ($records as $record) {
			$content .= $this->drawRecord($row, $column, $record, $dblist);
		}

		return <<<CONTENT
		<td colspan="{$column->getColspan()}" rowspan="{$column->getRowspan()}" style="{$column->getStyle()}">
			<div class="fce-header t3-row-header t3-page-colHeader t3-page-colHeader-label">
				<div>{$column->getLabel()}</div>
			</div>
			<div class="fce-container t3-page-ce-wrapper">
				<div class="t3-page-ce">
					<div class="t3-page-ce-dropzone" id="colpos-$colPosFluxContent-page-{$row['pid']}--top-{$row['uid']}-{$column->getName()}" style="height: 16px;">
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
	 * @param array $row
	 * @param Column $column
	 * @param array $record
	 * @param PageLayoutView $dblist
	 * @return string
	 */
	protected function drawRecord(array $row, Column $column, array $record, PageLayoutView $dblist) {
		$colPosFluxContent = ContentService::COLPOS_FLUXCONTENT;

		$disabledClass = '';
		if (FALSE === empty($record['isDisabled'])) {
			$disabledClass = ' t3-page-ce-hidden';
		}

		$element = $this->drawElement($record, $dblist);
		if (0 === (int) $dblist->tt_contentConfig['languageMode']) {
			$element = '<div class="t3-page-ce-dragitem">' . $element . '</div>';
		}

		return <<<CONTENT
		<div class="t3-page-ce$disabledClass {$record['_CSSCLASS']}" id="element-tt_content-{$record['uid']}">
			$element
			<div class="t3-page-ce-dropzone" id="colpos-$colPosFluxContent-page-{$row['pid']}-{$row['uid']}-after-{$record['uid']}" style="height: 16px;">
				<div class="t3-page-ce-wrapper-new-ce">
					{$this->drawNewIcon($row, $column, $record['uid'])}
					{$this->drawPasteIcon($row, $column, FALSE, $record)}
					{$this->drawPasteIcon($row, $column, TRUE, $record)}
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
		$space = 0;
		$disableMoveAndNewButtons = FALSE;
		$langMode = $dblist->tt_contentConfig['languageMode'];
		$dragDropEnabled = FALSE;
		$rendered = $dblist->tt_content_drawHeader($row, $space, $disableMoveAndNewButtons, $langMode, $dragDropEnabled);
		$rendered .= '<div class="t3-page-ce-body-inner">' . $dblist->tt_content_drawItem($row) . '</div>';
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
		$returnUri = rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI'));

		$columnName = $column->getName();
		if (FALSE === empty($columnName) && FALSE === empty($after)) {
			$after = '-' . $after;
		} else {
			$after = $row['pid'];
		}

		$icon = MiscellaneousUtility::getIcon('actions-document-new');
		$uri = 'db_new_content_el.php?id=' . $row['pid'] .
			'&uid_pid=' . $after .
			'&colPos=' . ContentService::COLPOS_FLUXCONTENT .
			'&sys_language_uid=' . $row['sys_language_uid'] .
			'&defVals[tt_content][tx_flux_parent]=' . $row['uid'] .
			'&defVals[tt_content][tx_flux_column]=' . $columnName .
			'&returnUrl=' . $returnUri;
		$title = LocalizationUtility::translate('new', 'Flux');

		return MiscellaneousUtility::wrapLink($icon, $uri, $title);
	}

	/**
	 * @param array $row
	 * @param Column $column
	 * @param boolean $reference
	 * @param array $relativeTo
	 * @return string
	 */
	protected function drawPasteIcon(array $row, Column $column, $reference = FALSE, array $relativeTo = array()) {
		if (TRUE === $reference) {
			$command = 'reference';
		} else {
			$command = 'paste';
		}

		$relativeUid = TRUE === isset($relativeTo['uid']) ? $relativeTo['uid'] : 0;
		$relativeTo = $row['pid'] . '-' . $command . '-' . $relativeUid . '-' . $row['uid'];
		if (FALSE === empty($area)) {
			$relativeTo .= '-' . $column->getName();
		}
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
		if (FALSE === isset($_COOKIE['fluxCollapseStates'])) {
			return FALSE;
		}

		$cookie = json_decode(urldecode($_COOKIE['fluxCollapseStates']));
		return in_array($row['uid'], $cookie);
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
	 * @return integer
	 */
	protected function getActiveWorkspaceId() {
		return (integer) (TRUE === isset($GLOBALS['BE_USER']->workspace) ? $GLOBALS['BE_USER']->workspace : 0);
	}

	/**
	 * @param array $row
	 * @return PageLayoutView
	 */
	protected function getInitializePageLayoutView(array $row) {
		$pageRecord = $this->workspacesAwareRecordService->getSingle('pages', '*', $row['pid']);
		// note: the following chained makeInstance is not an error; it is there to make the ViewHelper work on TYPO3 6.0
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
		$dblist->tt_contentConfig['showCommands'] = 1;
		$dblist->tt_contentConfig['showInfo'] = 1;
		$dblist->tt_contentConfig['single'] = 0;
		$dblist->CType_labels = array();
		$dblist->pidSelect = "pid = '" . $row['pid'] . "'";
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
	 * @return array
	 */
	protected function getPageModuleSettings() {
		return $GLOBALS['SOBE']->MOD_SETTINGS;
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}

}

?>
