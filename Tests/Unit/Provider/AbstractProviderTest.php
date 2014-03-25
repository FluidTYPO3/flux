<?php
namespace FluidTYPO3\Flux\Provider;
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

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\PathUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
abstract class AbstractProviderTest extends AbstractTestCase {

	/**
	 * @var string
	 */
	protected $configurationProviderClassName = 'FluidTYPO3\Flux\Provider\ContentProvider';

	/**
	 * @test
	 */
	public function prunesEmptyFieldNodesOnRecordSave() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$row['pi_flexform'] = Xml::EXPECTING_FLUX_PRUNING;
		$provider = $this->getConfigurationProviderInstance();
		$provider->setFieldName('pi_flexform');
		$provider->setTableName('tt_content');
		$tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tceMain->datamap['tt_content'][$row['uid']]['pi_flexform']['data'] = array();
		$provider->postProcessRecord('update', $row['uid'], $row, $tceMain);
		$this->assertNotContains('<field index=""></field>', $row['pi_flexform']);
	}

	/**
	 * @test
	 */
	public function canCallResetMethod() {
		$provider = $this->createInstance();
		$provider->reset();
	}

	/**
	 * @return ProviderInterface
	 */
	protected function getConfigurationProviderInstance() {
		$potentialClassName = substr(get_class($this), 0, -4);
		/** @var ProviderInterface $instance */
		if (TRUE === class_exists($potentialClassName)) {
			$instance = $this->objectManager->get($potentialClassName);
		} else {
			$instance = $this->objectManager->get($this->configurationProviderClassName);
		}
		return $instance;
	}

	/**
	 * @return array
	 */
	protected function getBasicRecord() {
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		return $record;
	}

	/**
	 * @test
	 */
	public function canExecuteClearCacheCommand() {
		$provider = $this->getConfigurationProviderInstance();
		$return = $provider->clearCacheCommand(array('all'));
		$this->assertEmpty($return);
	}

	/**
	 * @test
	 */
	public function getInheritanceTreeReturnsEmptyArrayIfFieldNameIsNull() {
		$className = substr(get_class($this), 0, -4);
		$instance = $this->getMock($className, array('getFieldName'));
		$instance->expects($this->once())->method('getFieldName')->will($this->returnValue(NULL));
		$returned = $instance->getInheritanceTree(array('uid' => rand(999999, 99999999)));
		$this->assertIsArray($returned);
		$this->assertEmpty($returned);
	}

	/**
	 * @test
	 */
	public function clearCacheCommandReturnsEarlyWhenGivenUid() {
		$provider = $this->getConfigurationProviderInstance();
		$return = $provider->clearCacheCommand(array('uid' => 1));
		$this->assertEmpty($return);
	}

	/**
	 * @test
	 */
	public function canGetAndSetListType() {
		$record = Records::$contentRecordIsParentAndHasChildren;
		/** @var ProviderInterface $instance */
		$instance = $this->getConfigurationProviderInstance();
		$instance->setExtensionKey('flux');
		$instance->setListType('test');
		$this->assertSame('test', $instance->getListType($record));
	}

	/**
	 * @test
	 */
	public function canGetContentObjectType() {
		$instance = $this->getConfigurationProviderInstance();
		$record = Records::$contentRecordIsParentAndHasChildren;
		$contentType = $instance->getContentObjectType($record);
		$this->assertNull($contentType);
	}

	/**
	 * @test
	 */
	public function canGetForm() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$form = $provider->getForm($record);
		if ($form) {
			$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
		}
	}

	/**
	 * @test
	 */
	public function canGetFormWithFieldsFromTemplate() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW_EMPTY);
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->setTemplatePathAndFilename($templatePathAndFilename);
		$form = $provider->getForm($record);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
		$this->assertTrue($form->get('options')->has('settings.input'));
	}

	/**
	 * @test
	 */
	public function canGetGrid() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$provider = $this->getConfigurationProviderInstance();
		ObjectAccess::setProperty($provider, 'templatePathAndFilename', $templatePathAndFilename, TRUE);
		$record = $this->getBasicRecord();
		$form = $provider->getGrid($record);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $form);
	}

	/**
	 * @test
	 */
	public function canGetTemplatePaths() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$paths = $provider->getTemplatePaths($record);
		$this->assertIsArray($paths);
	}

	/**
	 * @test
	 */
	public function canGetForcedTemplateVariables() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$variables = $provider->getTemplateVariables($record);
		$this->assertIsArray($variables);
	}

	/**
	 * @test
	 */
	public function canGetFlexformValues() {
		$provider = $this->getConfigurationProviderInstance();
		$provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
		$provider->reset();
		$record = $this->getBasicRecord();
		$values1 = $provider->getFlexformValues($record);
		$values2 = $provider->getFlexformValues($record);
		$this->assertIsArray($values1);
		$this->assertSame($values1, $values2);
	}

	/**
	 * @test
	 */
	public function canGetFlexformValuesUnderDirectConditions() {
		$tree = array(
			$this->getBasicRecord(),
			$this->getBasicRecord()
		);
		$record = $this->getBasicRecord();
		$provider = $this->getMock(substr(get_class($this), 0, -4), array('getForm', 'getInheritanceTree', 'getMergedConfiguration'));
		$mockConfigurationService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('convertFlexFormContentToArray'));
		$mockConfigurationService->expects($this->once())->method('convertFlexFormContentToArray')->will($this->returnValue(array('test' => 'test')));
		$provider->expects($this->once())->method('getForm')->will($this->returnValue(Form::create()));
		$provider->expects($this->once())->method('getInheritanceTree')->will($this->returnValue($tree));
		$provider->expects($this->once())->method('getMergedConfiguration')->with($tree)->will($this->returnValue(array('test' => 'test')));
		ObjectAccess::setProperty($provider, 'configurationService', $mockConfigurationService, TRUE);
		$provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
		$provider->reset();
		$values = $provider->getFlexformValues($record);
		$this->assertIsArray($values);
		$this->assertEquals($values, array('test' => 'test'));
	}

	/**
	 * @test
	 */
	public function canGetFlexformValuesUnderInheritanceConditions() {
		$tree = array(
			$this->getBasicRecord(),
			$this->getBasicRecord()
		);
		$record = $this->getBasicRecord();
		$provider = $this->getMock(substr(get_class($this), 0, -4), array('getForm', 'getInheritanceTree', 'getMergedConfiguration'));
		$mockConfigurationService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('convertFlexFormContentToArray'));
		$mockConfigurationService->expects($this->once())->method('convertFlexFormContentToArray')->will($this->returnValue(array()));
		$provider->expects($this->once())->method('getForm')->will($this->returnValue(Form::create()));
		$provider->expects($this->once())->method('getInheritanceTree')->will($this->returnValue($tree));
		$provider->expects($this->once())->method('getMergedConfiguration')->with($tree)->will($this->returnValue(array()));
		$provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
		ObjectAccess::setProperty($provider, 'configurationService', $mockConfigurationService, TRUE);
		$provider->reset();
		$values = $provider->getFlexformValues($record);
		$this->assertEquals($values, array());
	}

	/**
	 * @test
	 */
	public function canGetTemplateVariables() {
		$provider = $this->getConfigurationProviderInstance();
		$provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
		$record = $this->getBasicRecord();
		$values = $provider->getTemplateVariables($record);
		$this->assertIsArray($values);
	}

	/**
	 * @test
	 */
	public function canGetConfigurationSection() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$section = $provider->getConfigurationSectionName($record);
		$this->assertIsString($section);
	}

	/**
	 * BASIC STUB: override this in your own test class if your
	 * Provider is expected to return an extension key.
	 *
	 * @test
	 */
	public function canGetExtensionKey() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$extensionKey = $provider->getExtensionKey($record);
		$this->assertNull($extensionKey);
	}

	/**
	 * BASIC STUB: override this in your own test class if your
	 * Provider is expected to return an extension key.
	 *
	 * @test
	 */
	public function canGetTableName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$tableName = $provider->getTableName($record);
		$this->assertNull($tableName);
	}

	/**
	 * @test
	 */
	public function canGetControllerExtensionKey() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->getControllerExtensionKeyFromRecord($record);
	}

	/**
	 * @test
	 */
	public function canGetControllerActionName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->getControllerActionFromRecord($record);
	}

	/**
	 * @test
	 */
	public function canGetControllerActionReferenceName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->getControllerActionReferenceFromRecord($record);
	}

	/**
	 * @test
	 */
	public function canGetPriority() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$priority = $provider->getPriority($record);
		$this->assertIsInteger($priority);
	}

	/**
	 * @test
	 */
	public function canGetFieldName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->getFieldName($record);
	}

	/**
	 * @test
	 */
	public function canGetTemplateFilePathAndFilename() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->getTemplatePathAndFilename($record);
	}

	/**
	 * @test
	 */
	public function canPostProcessDataStructure() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$dataStructure = array();
		$config = array();
		$provider->postProcessDataStructure($record, $dataStructure, $config);
	}

	/**
	 * @test
	 */
	public function canPostProcessDataStructureWithManualFormInstance() {
		$provider = $this->getConfigurationProviderInstance();
		$form = Form::create();
		$record = $this->getBasicRecord();
		$dataStructure = array();
		$config = array();
		$provider->setForm($form);
		$provider->postProcessDataStructure($record, $dataStructure, $config);
		$this->assertIsArray($dataStructure);
		$this->assertNotEquals(array(), $dataStructure);
		$this->assertNotEmpty($dataStructure);
	}

	/**
	 * @test
	 */
	public function canPostProcessRecord() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$parentInstance = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$record['test'] = 'test';
		$id = $record['uid'];
		$tableName = $provider->getTableName($record);
		if (TRUE === empty($tableName)) {
			$tableName = 'tt_content';
			$provider->setTableName($tableName);
		}
		$fieldName = $provider->getFieldName($record);
		if (TRUE === empty($fieldName)) {
			$fieldName = 'pi_flexform';
			$provider->setFieldName($fieldName);
		}
		$record[$fieldName] = array(
			'data' => array(
				'options' => array(
					'lDEF' => array(
						'settings.input' => array(
							'vDEF' => 'test'
						),
						'settings.input_clear' => array(
							'vDEF' => 1
						)
					)
				)
			)
		);
		$parentInstance->datamap[$tableName][$id] = $record;
		$record[$fieldName] = Xml::EXPECTING_FLUX_REMOVALS;
		$provider->postProcessRecord('update', $id, $record, $parentInstance);
		$this->assertIsString($record[$fieldName]);
		$this->assertNotContains('settings.input', $record[$fieldName]);
	}

	/**
	 * @test
	 */
	public function canPostProcessRecordWithNullFieldName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$parentInstance = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$record['test'] = 'test';
		$id = $record['uid'];
		$tableName = $provider->getTableName($record);
		if (TRUE === empty($tableName)) {
			$tableName = 'tt_content';
			$provider->setTableName($tableName);
		}
		$fieldName = NULL;
		$provider->setFieldName(NULL);
		$parentInstance->datamap[$tableName][$id] = $record;
		$provider->postProcessRecord('update', $id, $record, $parentInstance);
	}

	/**
	 * @test
	 */
	public function canPreProcessRecordAndTransferDataToRecordValues() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$parentInstance = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tableName = $provider->getTableName($record);
		if (TRUE === empty($tableName)) {
			$tableName = 'tt_content';
			$provider->setTableName($tableName);
		}
		$fieldName = $provider->getFieldName($record);
		if (TRUE === empty($fieldName)) {
			$fieldName = 'pi_flexform';
			$provider->setFieldName($fieldName);
		}
		$record['header'] = 'old';
		$record[$fieldName] = array(
			'data' => array(
				'options' => array(
					'lDEF' => array(
						$tableName . '.header' => array(
							'vDEF' => 'overridden-header'
						)
					)
				)
			)
		);
		$id = $record['uid'];
		$provider->preProcessRecord($record, $id, $parentInstance);
		$this->assertSame($record['header'], $record[$fieldName]['data']['options']['lDEF'][$tableName . '.header']['vDEF']);
	}

	/**
	 * @test
	 */
	public function canSetForm() {
		$form = Form::create(array('name' => 'test'));
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$provider = $this->getConfigurationProviderInstance();
		$provider->setForm($form);
		$this->assertSame($form, $provider->getForm($record));
	}
	/**
	 * @test
	 */
	public function canSetGrid() {
		$grid = Grid::create(array('name' => 'test'));
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$provider = $this->getConfigurationProviderInstance();
		$provider->setGrid($grid);
		$this->assertSame($grid, $provider->getGrid($record));
	}

	/**
	 * @test
	 */
	public function canSetTableName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->setTableName('test');
		$this->assertSame('test', $provider->getTableName($record));
	}

	/**
	 * @test
	 */
	public function canSetFieldName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->setFieldName('test');
		$this->assertSame('test', $provider->getFieldName($record));
	}

	/**
	 * @test
	 */
	public function canSetExtensionKey() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->setExtensionKey('test');
		$this->assertSame('test', $provider->getExtensionKey($record));
	}

	/**
	 * @test
	 */
	public function canSetExtensionKeyAndPassToFormThroughLoadSettings() {
		$provider = $this->getConfigurationProviderInstance();
		$settings = array(
			'extensionKey' => 'my_ext',
			'form' => array(
				'name' => 'test'
			)
		);
		$provider->loadSettings($settings);
		$record = Records::$contentRecordIsParentAndHasChildren;
		$this->assertSame('my_ext', $provider->getExtensionKey($record));
		$this->assertSame('MyExt', $provider->getForm($record)->getExtensionName());
	}

	/**
	 * @test
	 */
	public function canSetTemplateVariables() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$variables = array('test' => 'test');
		$provider->setTemplateVariables($variables);
		$this->assertArrayHasKey('test', $provider->getTemplateVariables($record));
	}

	/**
	 * @test
	 */
	public function canSetTemplatePathAndFilename() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$template = 'test.html';
		$provider->setTemplatePathAndFilename($template);
		$this->assertSame(GeneralUtility::getFileAbsFileName($template), $provider->getTemplatePathAndFilename($record));
	}

	/**
	 * @test
	 */
	public function canUseAbsoluteTemplatePathDirectly() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$template = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$provider->setTemplatePathAndFilename($template);
		$this->assertSame($provider->getTemplatePathAndFilename($record), $template);
	}

	/**
	 * @test
	 */
	public function canSetTemplatePaths() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$templatePaths = array(
			'templateRootPath' => 'EXT:flux/Resources/Private/Templates'
		);
		$provider->setTemplatePaths($templatePaths);
		$this->assertSame(PathUtility::translatePath($templatePaths), $provider->getTemplatePaths($record));
	}

	/**
	 * @test
	 */
	public function canSetConfigurationSectionName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$section = 'Custom';
		$provider->setConfigurationSectionName($section);
		$this->assertSame($section, $provider->getConfigurationSectionName($record));
	}

	/**
	 * @test
	 */
	public function canUseInheritanceTree() {
		$provider = $this->getConfigurationProviderInstance();
		$provider->setFieldName('pi_flexform');
		$provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW_EMPTY));
		$record = $this->getBasicRecord();
		$byPathExists = $this->callInaccessibleMethod($provider, 'getInheritedPropertyValueByDottedPath', $record, 'settings');
		$byDottedPathExists = $this->callInaccessibleMethod($provider, 'getInheritedPropertyValueByDottedPath', $record, 'settings.input');
		$byPathDoesNotExist = $this->callInaccessibleMethod($provider, 'getInheritedPropertyValueByDottedPath', $record, 'void.doesnotexist');
		$this->assertEmpty($byPathDoesNotExist);
		$this->assertEmpty($byPathExists);
		$this->assertEmpty($byDottedPathExists);
	}

	/**
	 * @test
	 */
	public function canLoadRecordFromDatabase() {
		$provider = $this->getConfigurationProviderInstance();
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$table = $provider->getTableName($row);
		if (FALSE === empty($table)) {
			$result = $this->callInaccessibleMethod($provider, 'loadRecordFromDatabase', $row['uid']);
			$this->assertNotNull($result);
		}
	}

	/**
	 * @test
	 */
	public function canLoadRecordTreeFromDatabase() {
		$record = $this->getBasicRecord();
		$provider = $this->getMock(substr(get_class($this), 0, -4), array('loadRecordFromDatabase', 'getParentFieldName', 'getParentFieldValue'));
		$provider->expects($this->exactly(2))->method('getParentFieldName')->will($this->returnValue('somefield'));
		$provider->expects($this->exactly(1))->method('getParentFieldValue')->will($this->returnValue(1));
		$provider->expects($this->exactly(1))->method('loadRecordFromDatabase')->will($this->returnValue($record));
		$output = $this->callInaccessibleMethod($provider, 'loadRecordTreeFromDatabase', $record);
		$expected = array($record);
		$this->assertEquals($expected, $output);
	}

	/**
	 * @test
	 */
	public function setsDefaultValueInFieldsBasedOnInheritedValue() {
		$row = array();
		$className = substr(get_class($this), 0, -4);
		$service = $this->getMock($className, array('getInheritedPropertyValueByDottedPath'));
		$service->expects($this->once())->method('getInheritedPropertyValueByDottedPath')->with($row, 'input')->will($this->returnValue('default'));
		$form = Form::create();
		$field = $form->createField('Input', 'input');
		$returnedForm = $this->callInaccessibleMethod($service, 'setDefaultValuesInFieldsWithInheritedValues', $form, $row);
		$this->assertSame($form, $returnedForm);
		$this->assertEquals('default', $field->getDefault());
	}

	/**
	 * @test
	 */
	public function canCallPreProcessCommand() {
		$provider = $this->getConfigurationProviderInstance();
		$command = 'dummy';
		$id = 0;
		$record = $this->getBasicRecord();
		$relativeTo = 1;
		$reference = new DataHandler();
		$provider->preProcessCommand($command, $id, $record, $relativeTo, $reference);
	}

	/**
	 * @test
	 */
	public function canGetMergedConfiguration() {
		$form = Form::create();
		$form->createContainer('Grid', 'grid');
		$form->createField('Input', 'test');
		$form->createContainer('Object', 'testobject');
		$record = $this->getBasicRecord();
		$tree = array($record);
		$instance = $this->getMock(substr(get_class($this), 0, -4), array('getForm', 'getFlexFormValues'));
		$instance->reset();
		$instance->expects($this->once())->method('getForm')->will($this->returnValue($form));
		$output = $this->callInaccessibleMethod($instance, 'getMergedConfiguration', $tree);
		$this->assertEquals(array(), $output);
	}

	/**
	 * @test
	 */
	public function canGetMergedConfigurationAndMergeToCache() {
		$form = Form::create();
		$form->createContainer('Grid', 'grid');
		$form->createField('Input', 'test');
		$form->createContainer('Object', 'testobject');
		$record = $this->getBasicRecord();
		$tree = array($record);
		$instance = $this->getMock(substr(get_class($this), 0, -4), array('getForm', 'getFlexFormValues', 'hasCacheForMergedConfiguration'));
		$instance->reset();
		$instance->expects($this->once())->method('getForm')->will($this->returnValue($form));
		$instance->expects($this->once())->method('hasCacheForMergedConfiguration')->will($this->returnValue(TRUE));
		$this->callInaccessibleMethod($instance, 'getMergedConfiguration', $tree, 'testing', TRUE);
	}

	/**
	 * @test
	 */
	public function getMergedConfigurationReturnsEmptyArrayIfFormIsNull() {
		$record = $this->getBasicRecord();
		$tree = array($record);
		$instance = $this->getMock(substr(get_class($this), 0, -4), array('getForm'));
		$instance->reset();
		$instance->expects($this->once())->method('getForm')->will($this->returnValue(NULL));
		$output = $this->callInaccessibleMethod($instance, 'getMergedConfiguration', $tree);
		$this->assertEquals(array(), $output);
	}

	/**
	 * @test
	 */
	public function canAssertHasCachedMergedConfiguration() {
		$instance = $this->createInstance();
		$instance->reset();
		$this->assertFalse($this->callInaccessibleMethod($instance, 'hasCacheForMergedConfiguration', 'test'));
	}

	/**
	 * @test
	 */
	public function canGetCacheKeyForMergedConfiguration() {
		$instance = $this->createInstance();
		$instance->reset();
		$tree = array(
			array(
				'test' => 'test'
			)
		);
		$expected = 'merged_' . md5(json_encode($tree));
		$this->assertEquals($expected, $this->callInaccessibleMethod($instance, 'getCacheKeyForMergedConfiguration', $tree));
	}

}
