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

use FluidTYPO3\Flux\Utility\VersionUtility;
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

		$pageRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', "uid = '" . $row['pid'] . "'");
		$pageRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($pageRes);
		$GLOBALS['TYPO3_DB']->sql_free_result($pageRes);
		// note: the following chained makeInstance is not an error; it is there to make the ViewHelper work on TYPO3 6.0
		/** @var $dblist PageLayoutView */
		$dblist = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager')->get('TYPO3\CMS\Backend\View\PageLayoutView');
		$dblist->backPath = $GLOBALS['BACK_PATH'];
		$dblist->script = 'db_layout.php';
		$dblist->showIcon = 1;
		$dblist->setLMargin = 0;
		$dblist->doEdit = 1;
		$dblist->no_noWrap = 1;
		$dblist->setLMargin = 0;
		$dblist->ext_CALC_PERMS = $GLOBALS['BE_USER']->calcPerms($pageRecord);
		$dblist->id = $row['pid'];
		$dblist->nextThree = 1;
		$dblist->showCommands = 1;
		$dblist->tt_contentConfig['showCommands'] = 1;
		$dblist->tt_contentConfig['showInfo'] = 1;
		$dblist->tt_contentConfig['single'] = 0;
		$dblist->CType_labels = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $val) {
			$dblist->CType_labels[$val[1]] = $GLOBALS['LANG']->sL($val[0]);
		}
		$dblist->itemLabels = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns'] as $name => $val) {
			$dblist->itemLabels[$name] = $GLOBALS['LANG']->sL($val['label']);
		}

		$condition = "((tx_flux_column = '" . $area . ':' . $row['uid'] . "') OR (tx_flux_parent = '" . $row['uid'] . "' AND tx_flux_column = '" . $area . "')) AND deleted = 0";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_content', $condition, 'uid', 'sorting ASC');
		$records = $dblist->getResult($res);

		$fluxColumnId = 'column-' . $area . '-' . $row['uid'] . '-' . $row['pid'] . '-FLUX';

		$this->templateVariableContainer->add('records', $records);
		$this->templateVariableContainer->add('dblist', $dblist);
		$this->templateVariableContainer->add('fluxColumnId', $fluxColumnId);
		$content = $this->renderChildren();
		$this->templateVariableContainer->remove('records');
		$this->templateVariableContainer->remove('dblist');
		$this->templateVariableContainer->remove('fluxColumnId');

		if (FALSE === VersionUtility::assertExtensionVersionIsAtLeastVersion('gridelements', 2)) {
			$content = '<div id="column-' . $area . '-' . $row['uid'] . '-' . $row['pid'] . '-FLUX">' . $content . '</div>';
		}

		return $content;
	}
}
