<?php
namespace FluidTYPO3\Flux\Backend;
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
 * ************************************************************* */

use FluidTYPO3\Flux\Backend\DynamicFlexForm;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * @package Flux
 */
class DynamicFlexFormTest extends AbstractTestCase {

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
	 * @test
	 */
	public function canExecuteDataStructurePostProcessHookWithNullFieldAndBadTableName() {
		$this->canExecuteDataStructurePostProcessHookInternal(NULL, 'badtablename');
	}

	/**
	 * @param string $fieldName
	 * @param string $table
	 * @return void
	 */
	protected function canExecuteDataStructurePostProcessHookInternal($fieldName = 'pi_flexform', $table = 'tt_content') {
		$instance = $this->getInstance();
		$dataStructure = array();
		$config = array();
		$row = array();
		$instance->getFlexFormDS_postProcessDS($dataStructure, $config, $row, $table, $fieldName);
		$isArrayConstraint = new \PHPUnit_Framework_Constraint_IsType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY);
		$this->assertThat($dataStructure, $isArrayConstraint);
	}

	/**
	 * @return DynamicFlexForm
	 */
	protected function getInstance() {
		/** @var ObjectManagerInterface $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var DynamicFlexForm $instance */
		$instance = $objectManager->get('FluidTYPO3\Flux\Backend\DynamicFlexForm');
		return $instance;
	}

}
