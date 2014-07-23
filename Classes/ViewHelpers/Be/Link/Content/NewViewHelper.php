<?php
namespace FluidTYPO3\Flux\ViewHelpers\Be\Link\Content;
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
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Content / NewViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers\Be\Uri\Content
 */
class NewViewHelper extends AbstractViewHelper {

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
	 * @param integer $after
	 * @return string
	 */
	public function render($after = 0) {
		$pid = $this->arguments['row']['pid'];
		$uid = $this->arguments['row']['uid'];
		$area = $this->arguments['area'];
		$sysLang = $this->arguments['row']['sys_language_uid'];
		$returnUri = rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI'));

		if (FALSE === empty($area) && FALSE === empty($after)) {
			$after = '-' . $after;
		} else {
			$after = $pid;
		}

		$icon = MiscellaneousUtility::getIcon('actions-document-new');
		$uri = 'db_new_content_el.php?id=' . $pid .
			'&uid_pid=' . $after .
			'&colPos=' . ContentService::COLPOS_FLUXCONTENT .
			'&sys_language_uid=' . $sysLang .
			'&defVals[tt_content][tx_flux_parent]=' . $uid .
			'&defVals[tt_content][tx_flux_column]=' . $area .
			'&returnUrl=' . $returnUri;
		$title = LocalizationUtility::translate('new', 'Flux');

		return MiscellaneousUtility::wrapLink($icon, $uri, $title);
	}

}
