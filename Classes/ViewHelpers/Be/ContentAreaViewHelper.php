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
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ContentAreaViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers\Be
 */
class ContentAreaViewHelper extends AbstractViewHelper {

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
	 * Render uri
	 *
	 * @return string
	 */
	public function render() {

		$row = $this->arguments['row'];
		$area = $this->arguments['area'];

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
		$dblist->showCommands = 1;
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

		$modSettings = $GLOBALS['SOBE']->MOD_SETTINGS;

		// Initializes page languages and icons so they are available in PageLayoutView if languageMode is set.
		$dblist->initializeLanguages();

		if (2 === intval($modSettings['function'])) {
			$dblist->tt_contentConfig['single'] = 0;
			$dblist->tt_contentConfig['languageMode'] = 1;
			$dblist->tt_contentConfig['languageCols'] = array(0 => $GLOBALS['LANG']->getLL('m_default'));
			$dblist->tt_contentConfig['languageColsPointer'] = $modSettings['language'];
		}

		// The following solution is half lifted from \TYPO3\CMS\Backend\View\PageLayoutView::getContentRecordsPerColumn
		// and relies on TYPO3 core query parts for enable-clause-, language- and versioning placeholders. All that needs
		// to be done after this, is filter the array according to moved/deleted placeholders since TYPO3 will not remove
		// records based on them having remove placeholders.
		$condition = "AND tx_flux_parent = '" . $row['uid'] . "' AND tx_flux_column = '" . $area . "' ";
		$condition .= "AND colPos = '" . ContentService::COLPOS_FLUXCONTENT . "' ";
		$queryParts = $dblist->makeQueryArray('tt_content', $row['pid'], $condition);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
		$rows = $dblist->getResult($result);
		$workspaceId = (integer) $GLOBALS['BE_USER']->workspace;
		foreach ($rows as $index => &$record) {
			if (0 < $workspaceId) {
				$workspaceRecord = BackendUtility::getWorkspaceVersionOfRecord($GLOBALS['BE_USER']->workspace, 'tt_content', $record['uid']);
				if (FALSE !== $workspaceRecord) {
					$record = $workspaceRecord;
				}
			}
			BackendUtility::movePlhOL('tt_content', $record);
			if (TRUE === empty($record) || TRUE === VersionState::cast($record['t3ver_state'])->equals(VersionState::DELETE_PLACEHOLDER)) {
				unset($rows[$index]);
			} else {
				$record['isDisabled'] = $dblist->isDisabled('tt_content', $record);
			}
		}

		// EXT:gridelements support
		$fluxColumnId = 'column-' . $area . '-' . $row['uid'] . '-' . $row['pid'] . '-FLUX';

		$this->templateVariableContainer->add('records', $rows);
		$this->templateVariableContainer->add('dblist', $dblist);
		$this->templateVariableContainer->add('fluxColumnId', $fluxColumnId);
		$content = $this->renderChildren();
		$this->templateVariableContainer->remove('records');
		$this->templateVariableContainer->remove('dblist');
		$this->templateVariableContainer->remove('fluxColumnId');

		return $content;
	}

}
