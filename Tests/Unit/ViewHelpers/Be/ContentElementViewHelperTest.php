<?php
namespace FluidTYPO3\Flux\ViewHelpers\Be;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@namelesscoder.net>
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
 * ************************************************************* */

use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Template\MediumDocumentTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use FluidTYPO3\Flux\ViewHelpers\AbstractViewHelperTest;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;

/**
 * @package Flux
 */
class ContentElementViewHelperTest extends AbstractViewHelperTest {

	/**
	 * Setup
	 */
	protected function setUp() {
		parent::setUp();
		$GLOBALS['TBE_TEMPLATE'] = new DocumentTemplate();
		$GLOBALS['SOBE'] = new BackendController();
		$GLOBALS['SOBE']->doc = new MediumDocumentTemplate();
		$GLOBALS['TSFE'] = new TypoScriptFrontendController($GLOBALS['TYPO3_CONF_VARS'], 1, 0);
	}

	/**
	 * @test
	 */
	public function canRender() {
		$arguments = array(
			'row' => Records::$contentRecordWithoutParentAndWithoutChildren,
			'area' => 'test',
			'dblist' => GeneralUtility::makeInstance('TYPO3\CMS\Backend\View\PageLayoutView')
		);
		$this->executeViewHelper($arguments);
	}

}
