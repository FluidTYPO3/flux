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
class Tx_Flux_Backend_PreviewTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canExecuteNewRenderer() {
		$caller = $this->objectManager->get('TYPO3\\CMS\\Backend\\View\\PageLayoutView');
		$function = 'EXT:flux/Classes/Backend/PreviewSix.php:Tx_Flux_Backend_PreviewSix';
		$this->callUserFunction($function, $caller);
	}

	/**
	 * @test
	 */
	public function canExecuteOldRenderer() {
		$caller = $this->objectManager->get('tx_cms_layout');
		$function = 'EXT:flux/Classes/Backend/Preview.php:Tx_Flux_Backend_Preview';
		$this->callUserFunction($function, $caller);
	}

	/**
	 * @param string $function
	 * @param mixed $caller
	 */
	protected function callUserFunction($function, $caller) {
		$drawItem = TRUE;
		$headerContent = '';
		$itemContent = '';
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$row['pi_flexform'] = Tx_Flux_Tests_Fixtures_Data_Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		Tx_Flux_Core::registerConfigurationProvider('Tx_Flux_Tests_Fixtures_Class_DummyConfigurationProvider');
		$instance = $this->objectManager->get(array_pop(explode(':', $function)));
		$instance->preProcess($caller, $drawItem, $headerContent, $itemContent, $row);
		Tx_Flux_Core::unregisterConfigurationProvider('Tx_Flux_Tests_Fixtures_Class_DummyConfigurationProvider');
	}

}
