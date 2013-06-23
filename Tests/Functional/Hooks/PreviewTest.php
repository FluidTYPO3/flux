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
class Tx_Flux_Tests_Functional_Hook_PreviewTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canExecutePreviewRendererHook() {
		$instance = $this->getInstance();
		$header = '';
		$content = '';
		$draw = TRUE;
		$row = \Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		if (TRUE === \Tx_Flux_Utility_Version::assertCoreVersionIsAtLeastSixPointZero()) {
			$parentInstance = $this->getCallerInstance();
			$instance->renderPreview($parentInstance, $draw, $row, $header, $content);
		} else {
			$instance->renderPreview($header, $content, $row, $draw);
		}
	}

	/**
	 * @return \Tx_Flux_Backend_AbstractPreview
	 */
	protected function getInstance() {
		/** @var Tx_Extbase_Object_ObjectManager $objectManager */
		$objectManager = \t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		/** @var Tx_Flux_Backend_AbstractPreview $instance */
		if (TRUE === \Tx_Flux_Utility_Version::assertCoreVersionIsAtLeastSixPointZero()) {
			$instance = $objectManager->get('Tx_Flux_Backend_PreviewSix');
		} else {
			$instance = $objectManager->get('Tx_Flux_Backend_Preview');
		}
		return $instance;
	}

	/**
	 * @return \TYPO3\CMS\Backend\View\PageLayoutView
	 */
	protected function getCallerInstance() {
		/** @var Tx_Extbase_Object_ObjectManager $objectManager */
		$objectManager = \t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		/** @var \TYPO3\CMS\Backend\View\PageLayoutView $instance */
		$instance = $objectManager->get('TYPO3\\CMS\\Backend\\View\\PageLayoutView');
		return $instance;
	}

}
