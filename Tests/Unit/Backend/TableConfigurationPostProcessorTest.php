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

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Flux
 */
class TableConfigurationPostProcessorTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canLoadFluxService() {
		$object = GeneralUtility::getUserObj($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']['flux']);
		$object->processData();
	}

	/**
	 * @test
	 */
	public function canCreateTcaFromFluxForm() {
		$table = 'this_table_does_not_exist';
		$field = 'input';
		$form = Form::create();
		$form->setExtensionName('FluidTYPO3.Flux');
		$form->createField('Input', $field);
		$form->setOption('labels', array('title'));
		Core::registerFormForTable($table, $form);
		$object = GeneralUtility::getUserObj('FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor');
		$object->processData();
		$this->assertArrayHasKey($table, $GLOBALS['TCA']);
		$this->assertArrayHasKey($field, $GLOBALS['TCA'][$table]['columns']);
		$this->assertContains($field, $GLOBALS['TCA'][$table]['interface']['showRecordFieldList']);
		$this->assertContains($field, $GLOBALS['TCA'][$table]['types'][0]['showitem']);
		$this->assertEquals($GLOBALS['TCA'][$table]['ctrl']['label'], 'title');
		$this->assertEquals('flux.this_table_does_not_exist', $GLOBALS['TCA'][$table]['title']);
	}

	/**
	 * @test
	 */
	public function canCreateFluxFormFromClassName() {
		$class = 'FluidTYPO3\Flux\Domain\Model\Dummy';
		$object = GeneralUtility::getUserObj('FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor');
		if ('6.2' !== substr(TYPO3_version, 0, 3)) {
			// note: mock contains a DateTime which has a constructor argument which ObjectManager tries to reflect;
			// causing an ReflectionException. Problem is suppressed on 6.2 only, at the time of writing this comment.
			$this->setExpectedException('ReflectionException');
		}
		$form = $object->generateFormInstanceFromClassName($class, 'tt_content');
		$this->assertIsValidAndWorkingFormObject($form);
		$this->callInaccessibleMethod($object, 'processFormForTable', 'void', $form);
		$this->assertIsArray($GLOBALS['TCA']['void']);
	}

	/**
	 * @test
	 */
	public function triggersDomainModelAnalysisWhenFormsAreRegistered() {
		$class = 'FluidTYPO3\Flux\Domain\Model\Dummy';
		if ('6.2' !== substr(TYPO3_version, 0, 3)) {
			// note: mock contains a DateTime which has a constructor argument which ObjectManager tries to reflect;
			// causing an ReflectionException. Problem is suppressed on 6.2 only, at the time of writing this comment.
			$this->setExpectedException('ReflectionException');
		}
		$form = Form::create();
		Core::registerAutoFormForModelObjectClassName($class);
		$object = GeneralUtility::getUserObj('FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor');
		$object->processData();
		Core::registerFormForModelObjectClassName($class, $form);
		$object->processData();
	}

	/**
	 * @test
	 */
	public function canExtensionNameFromLegacyModelClassName() {
		$class = 'Tx_Flux_Domain_Model_Dummy';
		$object = GeneralUtility::getUserObj('FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor');
		$extensionName = $this->callInaccessibleMethod($object, 'getExtensionNameFromModelClassName', $class);
		$this->assertEquals('Flux', $extensionName);
	}

	/**
	 * @test
	 */
	public function canExtensionNameFromNameSpacedClassName() {
		$class = 'Flux\Domain\Model\Dummy';
		$object = GeneralUtility::getUserObj('FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor');
		$extensionName = $this->callInaccessibleMethod($object, 'getExtensionNameFromModelClassName', $class, 'void');
		$this->assertEquals('Flux', $extensionName);
	}

	/**
	 * @test
	 */
	public function canExtensionNameFromNameSpacedClassNameWithVendor() {
		$class = 'FluidTYPO3\Flux\Domain\Model\Dummy';
		$object = GeneralUtility::getUserObj('FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor');
		$extensionName = $this->callInaccessibleMethod($object, 'getExtensionNameFromModelClassName', $class, 'void');
		$this->assertEquals('FluidTYPO3.Flux', $extensionName);
	}

}
