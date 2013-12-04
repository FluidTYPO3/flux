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

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Data/Xml.php');
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Data/Records.php');

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
abstract class Tx_Flux_Provider_AbstractProviderTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @var string
	 */
	protected $configurationProviderClassName = 'Tx_Flux_Provider_ContentProvider';

	/**
	 * @return Tx_Flux_Provider_ProviderInterface
	 */
	protected function getConfigurationProviderInstance() {
		$potentialClassName = substr(get_class($this), 0, -4);
		/** @var Tx_Flux_Provider_ProviderInterface $instance */
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
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Tx_Flux_Tests_Fixtures_Data_Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		return $record;
	}

	/**
	 * @test
	 */
	public function canExecuteClearCacheCommand() {
		touch(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('typo3temp/flux-test.manifest'));
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
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
		/** @var Tx_Flux_Provider_ProviderInterface $instance */
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
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
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
			$this->assertInstanceOf('Tx_Flux_Form', $form);
		}
	}

	/**
	 * @test
	 */
	public function canGetFormWithFieldsFromTemplate() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_FIELD_CHECKBOX);
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->setTemplatePathAndFilename($templatePathAndFilename);
		$form = $provider->getForm($record);
		$this->assertInstanceOf('Tx_Flux_Form', $form);
		$this->assertTrue($form->get('options')->has('settings.checkbox'));
	}

	/**
	 * @test
	 */
	public function canGetGrid() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$provider = $this->getConfigurationProviderInstance();
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($provider, 'templatePathAndFilename', $templatePathAndFilename, TRUE);
		$record = $this->getBasicRecord();
		$form = $provider->getGrid($record);
		$this->assertInstanceOf('Tx_Flux_Form_Container_Grid', $form);
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
		$record = $this->getBasicRecord();
		$values1 = $provider->getFlexformValues($record);
		$values2 = $provider->getFlexformValues($record);
		$this->assertIsArray($values1);
		$this->assertSame($values1, $values2);
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
		$form = Tx_Flux_Form::create();
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
		$parentInstance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
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
		$record[$fieldName] = Tx_Flux_Tests_Fixtures_Data_Xml::EXPECTING_FLUX_REMOVALS;
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
		$parentInstance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
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
		$parentInstance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
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
		$form = Tx_Flux_Form::create(array('name' => 'test'));
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$provider = $this->getConfigurationProviderInstance();
		$provider->setForm($form);
		$this->assertSame($form, $provider->getForm($record));
	}
	/**
	 * @test
	 */
	public function canSetGrid() {
		$grid = Tx_Flux_Form_Container_Grid::create(array('name' => 'test'));
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
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
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
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
		$this->assertSame(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($template), $provider->getTemplatePathAndFilename($record));
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
		$this->assertSame(Tx_Flux_Utility_Path::translatePath($templatePaths), $provider->getTemplatePaths($record));
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
		$provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_FIELD_INPUT));
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
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$result = $this->callInaccessibleMethod($provider, 'loadRecordFromDatabase', $row['uid']);
		$this->assertNotNull($result);
	}

	/**
	 * @test
	 */
	public function setsDefaultValueInFieldsBasedOnInheritedValue() {
		$row = array();
		$className = substr(get_class($this), 0, -4);
		$service = $this->getMock($className, array('getInheritedPropertyValueByDottedPath'));
		$service->expects($this->once())->method('getInheritedPropertyValueByDottedPath')->with($row, 'input')->will($this->returnValue('default'));
		$form = Tx_Flux_Form::create();
		$field = $form->createField('Input', 'input');
		$returnedForm = $this->callInaccessibleMethod($service, 'setDefaultValuesInFieldsWithInheritedValues', $form, $row);
		$this->assertSame($form, $returnedForm);
		$this->assertEquals('default', $field->getDefault());
	}

}
