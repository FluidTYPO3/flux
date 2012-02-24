<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Claus Due <claus@wildside.dk>, Wildside A/S
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

/**
 * @package Flux
 * @subpackage ViewHelpers\Be
 */
class Tx_Flux_ViewHelpers_Be_ContentAreaViewHelper extends Tx_Flux_Core_ViewHelper_AbstractBackendViewHelper {

	/**
	 * Render uri
	 *
	 * @return string
	 */
	public function render() {

		$row = $this->arguments['row'];
		$area = $this->arguments['area'];

		$pageRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', "uid = '{$row['pid']}'");
		$pageRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($pageRes);
		$GLOBALS['TYPO3_DB']->sql_free_result($pageRes);
		$dblist = t3lib_div::makeInstance('tx_cms_layout');
		$dblist->backPath = $GLOBALS['BACK_PATH'];
		$dblist->thumbs = $this->imagemode;
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

		$records = array();
		$condition = "tx_flux_column = '{$area}:{$row['uid']}' AND deleted = 0";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_content', $condition, 'uid', 'sorting ASC');
		$records = $dblist->getResult($res);

		$this->templateVariableContainer->add('records', $records);
		$this->templateVariableContainer->add('dblist', $dblist);
		$content = $this->renderChildren();
		$this->templateVariableContainer->remove('records');
		$this->templateVariableContainer->remove('dblist');

		$content = '<div id="column-' . $area . '-' . $row['uid'] . '-' . $row['pid'] . '-FLUX">' . $content . '</div>';

		return $content;
	}
}

?>