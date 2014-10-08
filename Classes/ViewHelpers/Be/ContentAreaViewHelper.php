<?php
namespace FluidTYPO3\Flux\ViewHelpers\Be;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ContentAreaViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers\Be
 */
class ContentAreaViewHelper extends AbstractViewHelper {

	/**
	 * @TODO: replace usages with VersionState implementation when dropping 6.1 support
	 */
	const DELETE_PLACEHOLDER = 2;

	/**
	 * @var WorkspacesAwareRecordService
	 */
	protected $recordService;

	/**
	 * @param WorkspacesAwareRecordService $recordService
	 * @return void
	 */
	public function injectRecordService(WorkspacesAwareRecordService $recordService) {
		$this->recordService = $recordService;
	}

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('row', 'array', 'Record row', TRUE);
		$this->registerArgument('area', 'string', 'If placed inside Fluid FCE, use this to indicate which area to insert into');
	}

	/**
	 * Render a list of nested content elements
	 *
	 * @return string
	 */
	public function render() {
		$row = (array) $this->arguments['row'];
		$area = $this->arguments['area'];
		$dblist = $this->getInitializePageLayoutView();
		$this->configurePageLayoutViewForLanguageMode($dblist);
		$this->templateVariableContainer->add('records', $this->getRecords($dblist, $row, $area));
		$this->templateVariableContainer->add('dblist', $dblist);
		// EXT:gridelements support
		$this->templateVariableContainer->add('fluxColumnId', 'column-' . $area . '-' . $row['uid'] . '-' . $row['pid'] . '-FLUX');
		$content = $this->renderChildren();
		$this->templateVariableContainer->remove('records');
		$this->templateVariableContainer->remove('dblist');
		$this->templateVariableContainer->remove('fluxColumnId');
		return $content;
	}

	/**
	 * @return integer
	 */
	protected function getActiveWorkspaceId() {
		return (integer) (TRUE === isset($GLOBALS['BE_USER']->workspace) ? $GLOBALS['BE_USER']->workspace : 0);
	}

	/**
	 * @return array
	 */
	protected function getPageModuleSettings() {
		return $GLOBALS['SOBE']->MOD_SETTINGS;
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
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
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
		return (TRUE === empty($record) || self::DELETE_PLACEHOLDER === (integer) $record['t3ver_state']);
	}

	/**
	 * @return PageLayoutView
	 */
	protected function getInitializePageLayoutView() {
		$row = $this->arguments['row'];
		$pageRecord = $this->recordService->getSingle('pages', '*', $row['pid']);
		// note: the following chained makeInstance is not an error; it is there to make the ViewHelper work on TYPO3 6.0
		/** @var $dblist PageLayoutView */
		$dblist = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager')->get('TYPO3\CMS\Backend\View\PageLayoutView');
		$dblist->backPath = $GLOBALS['BACK_PATH'];
		$dblist->script = 'db_layout.php';
		$dblist->showIcon = 1;
		$dblist->setLMargin = 0;
		$dblist->doEdit = 1;
		$dblist->no_noWrap = 1;
		$dblist->ext_CALC_PERMS = $GLOBALS['BE_USER']->calcPerms($pageRecord);
		$dblist->id = $row['pid'];
		$dblist->nextThree = 1;
		$dblist->tt_contentConfig['showCommands'] = 1;
		$dblist->tt_contentConfig['showInfo'] = 1;
		$dblist->tt_contentConfig['single'] = 0;
		$dblist->CType_labels = array();
		$dblist->pidSelect = "pid = '" . $row['pid'] . "'";
		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $val) {
			$dblist->CType_labels[$val[1]] = $GLOBALS['LANG']->sL($val[0]);
		}
		$dblist->itemLabels = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns'] as $name => $val) {
			$dblist->itemLabels[$name] = $GLOBALS['LANG']->sL($val['label']);
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
			$view->tt_contentConfig['languageCols'] = array(0 => $GLOBALS['LANG']->getLL('m_default'));
			$view->tt_contentConfig['languageColsPointer'] = $modSettings['language'];
		}
		return $view;
	}

}
