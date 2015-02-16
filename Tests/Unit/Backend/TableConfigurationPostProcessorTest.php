<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\Domain\Model\Dummy;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Flux
 */
class TableConfigurationPostProcessorTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canLoadProcessorAsUserObject() {
		$object = GeneralUtility::getUserObj(TableConfigurationPostProcessor::class);
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
		$object = GeneralUtility::getUserObj(TableConfigurationPostProcessor::class);
		$object->processData();
		$this->assertArrayHasKey($table, $GLOBALS['TCA']);
		$this->assertArrayHasKey($field, $GLOBALS['TCA'][$table]['columns']);
		$this->assertContains($field, $GLOBALS['TCA'][$table]['interface']['showRecordFieldList']);
		$this->assertContains($field, $GLOBALS['TCA'][$table]['types'][0]['showitem']);
		$this->assertEquals($GLOBALS['TCA'][$table]['ctrl']['label'], 'title');
		$this->assertContains('flux.this_table_does_not_exist', $GLOBALS['TCA'][$table]['ctrl']['title']);
	}

	/**
	 * @test
	 */
	public function canCreateFluxFormFromClassName() {
		$object = GeneralUtility::getUserObj(TableConfigurationPostProcessor::class);
		$form = $object->generateFormInstanceFromClassName(Dummy::class, 'tt_content');
		$this->assertIsValidAndWorkingFormObject($form);
		$this->callInaccessibleMethod($object, 'processFormForTable', 'void', $form);
		$this->assertIsArray($GLOBALS['TCA']['void']);
	}

	/**
	 * @test
	 */
	public function triggersDomainModelAnalysisWhenFormsAreRegistered() {
		$form = Form::create();
		$form->setExtensionName('FluidTYPO3.Flux');
		Core::registerAutoFormForModelObjectClassName(Dummy::class);
		$object = GeneralUtility::getUserObj(TableConfigurationPostProcessor::class);
		$object->processData();
		Core::registerFormForModelObjectClassName($class, $form);
		$object->processData();
	}

	/**
	 * @test
	 */
	public function canExtensionNameFromLegacyModelClassName() {
		$class = 'Tx_Flux_Domain_Model_Dummy';
		$object = GeneralUtility::getUserObj(TableConfigurationPostProcessor::class);
		$extensionName = $this->callInaccessibleMethod($object, 'getExtensionNameFromModelClassName', $class);
		$this->assertEquals('Flux', $extensionName);
	}

	/**
	 * @test
	 */
	public function canExtensionNameFromNameSpacedClassName() {
		$class = 'Flux\Domain\Model\Dummy';
		$object = GeneralUtility::getUserObj(TableConfigurationPostProcessor::class);
		$extensionName = $this->callInaccessibleMethod($object, 'getExtensionNameFromModelClassName', $class, 'void');
		$this->assertEquals('Flux', $extensionName);
	}

	/**
	 * @test
	 */
	public function canExtensionNameFromNameSpacedClassNameWithVendor() {
		$class = 'FluidTYPO3\Flux\Domain\Model\Dummy';
		$object = GeneralUtility::getUserObj(TableConfigurationPostProcessor::class);
		$extensionName = $this->callInaccessibleMethod($object, 'getExtensionNameFromModelClassName', $class, 'void');
		$this->assertEquals('FluidTYPO3.Flux', $extensionName);
	}

	/**
	 * @test
	 * @dataProvider getClassToTableTestValues
	 * @param string $class
	 * @param string $expectedTable
	 */
	public function testResolveTableName($class, $expectedTable) {
		$instance = new TableConfigurationPostProcessor();
		$result = $this->callInaccessibleMethod($instance, 'resolveTableName', $class);
		$this->assertEquals($expectedTable, $result);
	}

	/**
	 * @return array
	 */
	public function getClassToTableTestValues() {
		return array(
			array('syslog', 'syslog'),
			array('FluidTYPO3\\Flux\\Domain\\Model\\ObjectName', 'tx_flux_domain_model_objectname'),
			array('TYPO3\\CMS\\Extbase\\Domain\\Model\\ObjectName', 'tx_extbase_domain_model_objectname'),
			array('Tx_Flux_Domain_Model_ObjectName', 'tx_flux_domain_model_objectname'),
		);
	}

}
