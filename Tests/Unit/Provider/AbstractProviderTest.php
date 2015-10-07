<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\PathUtility;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\DataHandling\DataHandler;
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
	 * @return ProviderInterface
	 */
	protected function getConfigurationProviderInstance() {
		$potentialClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
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
	public function getPreviewViewReturnsPreviewViewInstance() {
		$instance = $this->createInstance();
		$result = $this->callInaccessibleMethod($instance, 'getPreviewView');
		$this->assertInstanceOf('FluidTYPO3\\Flux\\View\\PreviewView', $result);
	}

	/**
	 * @test
	 */
	public function getPreviewUsesPreviewView() {
		$instance = $this->getMockForAbstractClass(
			$this->createInstanceClassName(), array(), '', FALSE, TRUE, FALSE, array('getPreviewView')
		);
		$preview = $this->getMock('FluidTYPO3\\Flux\\View\\PreviewView', array('getPreview'));
		$preview->expects($this->once())->method('getPreview')->willReturn('previewcontent');
		$instance->expects($this->once())->method('getPreviewView')->willReturn($preview);
		$result = $instance->getPreview(array());
		$this->assertEquals(array(NULL, 'previewcontent', FALSE), $result);
	}

	/**
	 * @test
	 */
	public function prunesEmptyFieldNodesOnRecordSave() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$row['pi_flexform'] = Xml::EXPECTING_FLUX_PRUNING;
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', array('getSingle', 'update'));
		$recordService->expects($this->once())->method('getSingle')->willReturn($row);
		$recordService->expects($this->once())->method('update');
		$provider = $this->getConfigurationProviderInstance();
		$provider->setFieldName('pi_flexform');
		$provider->setTableName('tt_content');
		$provider->injectRecordService($recordService);
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
		$result = $provider->reset();
		$this->assertNull($result);
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
	public function canGetAndSetContentObjectType() {
		/** @var ProviderInterface $instance */
		$instance = $this->getConfigurationProviderInstance();
		$instance->setContentObjectType('test');
		$this->assertSame('test', $instance->getContentObjectType());
	}

	/**
	 * @test
	 */
	public function canGetParentFieldName() {
		$record = Records::$contentRecordIsParentAndHasChildren;
		/** @var ProviderInterface $instance */
		$instance = $this->getConfigurationProviderInstance();
		ObjectAccess::setProperty($instance, 'parentFieldName', 'test', TRUE);
		$this->assertSame('test', $instance->getParentFieldName($record));
	}

	/**
	 * @test
	 */
	public function canGetForm() {
		$provider = $this->getConfigurationProviderInstance();
		$paths = new TemplatePaths(array(
			'templateRootPaths' => array(__DIR__ . '/../../Fixtures/Templates/'),
			'partialRootPaths' => array(__DIR__ . '/../../Fixtures/Partials/'),
			'layoutRootPaths' => array(__DIR__ . '/../../Fixtures/Layouts/')
		));
		$record = $this->getBasicRecord();
		$context = $provider->getViewContext($record);
		$context->setSectionName('Configuration');
		$context->setPackageName('FluidTYPO3.Flux');
		$context->setTemplatePaths($paths);
		$context->setTemplatePathAndFilename(
			$this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW)
		);
		$provider->setViewContext($context);
		$form = $provider->getForm($record);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
	}

	/**
	 * @test
	 */
	public function canGetFormWithFieldsFromTemplate() {
		$provider = $this->getConfigurationProviderInstance();
		$paths = new TemplatePaths(array(
			'templateRootPaths' => array(__DIR__ . '/../../Fixtures/Templates/'),
			'partialRootPaths' => array(__DIR__ . '/../../Fixtures/Partials/'),
			'layoutRootPaths' => array(__DIR__ . '/../../Fixtures/Layouts/')
		));
		$record = $this->getBasicRecord();
		$context = $provider->getViewContext($record);
		$context->setSectionName('Configuration');
		$context->setPackageName('FluidTYPO3.Flux');
		$context->setTemplatePaths($paths);
		$context->setTemplatePathAndFilename(
			$this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW_EMPTY)
		);
		$provider->setViewContext($context);
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
		ObjectAccess::setProperty($provider, 'templatePaths', array(), TRUE);
		$record = $this->getBasicRecord();
		$grid = $provider->getGrid($record);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $grid);
	}

	/**
	 * @test
	 */
	public function canGetTemplatePaths() {
		$provider = $this->getConfigurationProviderInstance();
		ObjectAccess::setProperty($provider, 'templatePaths', array(), TRUE);
		$record = $this->getBasicRecord();
		$paths = $provider->getTemplatePaths($record);
		$this->assertIsArray($paths);
	}

	/**
	 * @test
	 */
	public function canGetForcedTemplateVariables() {
		$provider = $this->getMock($this->createInstanceClassName(), array('getPageValues'));
		$record = $this->getBasicRecord();
		$variables = $provider->getTemplateVariables($record);
		$this->assertIsArray($variables);
	}

	/**
	 * @test
	 */
	public function canGetFlexformValues() {
		$record = $this->getBasicRecord();
		$provider = $this->getMock(str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4)), array('getForm'));
		$mockConfigurationService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('convertFlexFormContentToArray'));
		$mockConfigurationService->expects($this->once())->method('convertFlexFormContentToArray')->will($this->returnValue(array('test' => 'test')));
		$provider->expects($this->once())->method('getForm')->will($this->returnValue(Form::create()));
		$provider->injectConfigurationService($mockConfigurationService);
		$provider->setTemplatePathAndFilename($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL));
		$provider->reset();
		$values = $provider->getFlexformValues($record);
		$this->assertIsArray($values);
		$this->assertEquals($values, array('test' => 'test'));
	}

	/**
	 * @test
	 */
	public function canGetTemplateVariables() {
		$provider = $this->getMock($this->createInstanceClassName(), array('getPageValues', 'getGrid', 'getForm'));
		$provider->expects($this->once())->method('getPageValues')->willReturnArgument(0);
		$provider->expects($this->once())->method('getForm')->willReturn(NULL);
		$provider->expects($this->once())->method('getGrid')->willReturn(NULL);
		ObjectAccess::setProperty($provider, 'templatePaths', array(), TRUE);
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
		$result = $provider->getControllerExtensionKeyFromRecord($record);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canGetControllerActionName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$result = $provider->getControllerActionFromRecord($record);
		$this->assertNotEmpty($result);
	}

	/**
	 * @test
	 */
	public function canGetControllerActionReferenceName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$result = $provider->getControllerActionReferenceFromRecord($record);
		$this->assertNotEmpty($result);
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
		$result = $provider->getFieldName($record);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canGetTemplateFilePathAndFilename() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$templatePathAndFilename = $provider->getTemplatePathAndFilename($record);
		$this->assertEmpty($result);
	}

	/**
	 * @test
	 */
	public function canPostProcessDataStructure() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$dataStructure = array();
		$config = array();
		$result = $provider->postProcessDataStructure($record, $dataStructure, $config);
		$this->assertNull($result);
		$this->assertIsArray($config);
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
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', array('getSingle', 'update'));
		$recordService->expects($this->once())->method('getSingle')->willReturn($row);
		$recordService->expects($this->once())->method('update');
		$provider->injectRecordService($recordService);
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
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', array('getSingle'));
		$recordService->expects($this->any())->method('getSingle')->willReturn($row);
		$provider->injectRecordService($recordService);
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
		$result = $provider->postProcessRecord('update', $id, $record, $parentInstance);
		$this->assertNull($result);
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
		$provider = $this->getMock($this->createInstanceClassName(), array('getPageValues'));
		$provider->expects($this->once())->method('getPageValues')->willReturnArgument(0);
		$record = $this->getBasicRecord();
		$variables = array('test' => 'test');
		$provider->setTemplateVariables($variables);
		$this->assertArrayHasKey('test', $provider->getTemplateVariables($record));
	}

	/**
	 * @test
	 */
	public function testAppliesLocalisationToPageValues() {
		$GLOBALS['TSFE'] = (object) array('page' => array('foo' => 'bar'));
		$recordService = $this->getMock('FluidTYPO3\\Service\\RecordService', array('get'));
		$recordService->expects($this->once())->method('get')->willReturn(array(array('also' => 'baz')));
		$subject = $this->getAccessibleMockForAbstractClass('FluidTYPO3\\Flux\\Provider\\AbstractProvider');
		$subject->_set('recordService', $recordService);
		$this->assertEquals(array('foo' => 'bar', 'also' => 'baz'), $this->callInaccessibleMethod($subject, 'getPageValues'));
	}

	/**
	 * @test
	 */
	public function canSetTemplatePathAndFilename() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();

		$template = 'test.html';
		$provider->setTemplatePathAndFilename($template);
		$this->assertContains($template, $provider->getTemplatePathAndFilename($record));

		$template = NULL;
		$provider->setTemplatePathAndFilename($template);
		$this->assertSame($template, $provider->getTemplatePathAndFilename($record));

		$template = 'EXT:flux/Tests/Fixtures/Templates/Content/Dummy.html';
		$provider->setTemplatePathAndFilename($template);
		$this->assertTrue(
			GeneralUtility::isAbsPath($provider->getTemplatePathAndFilename($record)),
			'EXT relative paths are transformed'
		);
		$this->assertStringEndsWith(
			'flux/Tests/Fixtures/Templates/Content/Dummy.html',
			$provider->getTemplatePathAndFilename($record),
			'EXT relative paths are transformed'
		);

		$template = '/foo/Resources/Private/Foo/Bar.html';
		$provider->setTemplatePathAndFilename($template);
		$this->assertSame(
			$template,
			$provider->getTemplatePathAndFilename($record),
			'Absolute paths are not transformed'
		);
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
	public function canLoadRecordFromDatabase() {
		$backup = $GLOBALS['TYPO3_DB'];
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\CMS\Core\Database\DatabaseConnection', array('exec_SELECTgetSingleRow'));
		$GLOBALS['TYPO3_DB']->expects($this->atLeastOnce())->method('exec_SELECTgetSingleRow')->will($this->returnValue($row));
		$provider = $this->getConfigurationProviderInstance();
		$result = $this->callInaccessibleMethod($provider, 'loadRecordFromDatabase', $row['uid']);
		$this->assertNotNull($result);
		$GLOBALS['TYPO3_DB'] = $backup;
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
		$result = $provider->preProcessCommand($command, $id, $record, $relativeTo, $reference);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function getFormReturnsEarlyFormInstanceIfClassDefinedAndExists() {
		$mock = $this->getMock($this->createInstanceClassName(), array('resolveFormClassName', 'getTemplateSource'));
		$mock->expects($this->never())->method('getTemplateSource');
		$mock->expects($this->once())->method('resolveFormClassName')->will($this->returnValue('FluidTYPO3\\Flux\\Form'));
		$mock->getForm(array());
	}

}
