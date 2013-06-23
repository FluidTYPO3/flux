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
class Tx_Flux_Tests_Functional_Hook_DynamicFlexFormTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canExecuteDataStructurePostProcessHook() {
		$this->canExecuteDataStructurePostProcessHookInternal();
	}

	/**
	 * @test
	 */
	public function canExecuteDataStructurePostProcessHookWithNullFieldName() {
		$this->canExecuteDataStructurePostProcessHookInternal(NULL);
	}

	/**
	 * @param string $fieldName
	 * @return void
	 */
	protected function canExecuteDataStructurePostProcessHookInternal($fieldName = 'pi_flexform') {
		$instance = $this->getInstance();
		$dataStructure = array();
		$config = array();
		$table = 'tt_content';
		$fieldName = 'pi_flexform';
		$instance->getFlexFormDS_postProcessDS($dataStructure, $config, $row, $table, $fieldName);
		$isArrayConstraint = new PHPUnit_Framework_Constraint_IsType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY);
		$this->assertThat($dataStructure, $isArrayConstraint);
	}

	/**
	 * @return \Tx_Flux_Backend_DynamicFlexForm
	 */
	protected function getInstance() {
		/** @var Tx_Extbase_Object_ObjectManager $objectManager */
		$objectManager = \t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		/** @var Tx_Flux_Backend_DynamicFlexForm $instance */
		$instance = $objectManager->get('Tx_Flux_Backend_DynamicFlexForm');
		return $instance;
	}

}

