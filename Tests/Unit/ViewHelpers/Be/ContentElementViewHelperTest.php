<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_ViewHelpers_Be_ContentElementViewHelperTest extends Tx_Flux_ViewHelpers_AbstractViewHelperTest {

	/**
	 * Setup
	 */
	protected function setUp() {
		parent::setUp();
		$GLOBALS['TBE_TEMPLATE'] = new template();
		$GLOBALS['SOBE'] = new TYPO3backend();
		$GLOBALS['SOBE']->doc = new mediumDoc();
		$GLOBALS['TSFE'] = new tslib_fe($GLOBALS['TYPO3_CONF_VARS'], 1, 0);
	}

	/**
	 * @test
	 */
	public function canRender() {
		$arguments = array(
			'row' => Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren,
			'area' => 'test',
			'dblist' => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\View\\PageLayoutView')
		);
		$this->executeViewHelper($arguments);
	}

}
