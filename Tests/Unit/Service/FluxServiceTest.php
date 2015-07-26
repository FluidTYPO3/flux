<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Flux
 */
class FluxServiceTest extends AbstractTestCase {

	/**
	 * Setup
	 */
	public function setup() {
		$providers = Core::getRegisteredFlexFormProviders();
		if (TRUE === in_array('FluidTYPO3\Flux\Service\FluxService', $providers)) {
			Core::unregisterConfigurationProvider('FluidTYPO3\Flux\Service\FluxService');
		}
	}

	/**
	 * @test
	 * @dataProvider getSortObjectsTestValues
	 * @param array $input
	 * @param string $sortBy
	 * @param string $direction
	 * @param array $expectedOutput
	 */
	public function testSortObjectsByProperty($input, $sortBy, $direction, $expectedOutput) {
		$service = new FluxService();
		$sorted = $service->sortObjectsByProperty($input, $sortBy, $direction);
		$this->assertEquals($expectedOutput, $sorted);
	}

	/**
	 * @return array
	 */
	public function getSortObjectsTestValues() {
		return [
			[
				[['foo' => 'b'], ['foo' => 'a']],
				'foo', 'ASC',
				[['foo' => 'a'], ['foo' => 'b']]
			],
			[
				['a1' => ['foo' => 'b'], 'a2' => ['foo' => 'a']],
				'foo', 'ASC',
				['a2' => ['foo' => 'a'], 'a1' => ['foo' => 'b']],
			],
			[
				['a1' => ['foo' => 'b'], 'a2' => ['foo' => 'a']],
				'foo', 'DESC',
				['a1' => ['foo' => 'b'], 'a2' => ['foo' => 'a']],
			],
		];
	}

	/**
	 * @test
	 */
	public function dispatchesMessageOnInvalidPathsReturned() {
		$className = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$instance = $this->getMock($className, ['getDefaultViewConfigurationForExtensionKey', 'getTypoScriptByPath']);
		$instance->expects($this->once())->method('getTypoScriptByPath')->will($this->returnValue(NULL));
		$instance->expects($this->once())->method('getDefaultViewConfigurationForExtensionKey')->will($this->returnValue(NULL));
		$instance->getViewConfigurationForExtensionName('Flux');
	}

	/**
	 * @test
	 */
	public function canInstantiateFluxService() {
		$service = $this->createFluxServiceInstance();
		$this->assertInstanceOf('FluidTYPO3\Flux\Service\FluxService', $service);
	}

	/**
	 * @test
	 */
	public function canFlushCache() {
		$service = $this->createFluxServiceInstance();
		$result = $service->flushCache();
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithoutExtensionNameAndControllerName() {
		$service = $this->createFluxServiceInstance();
		$viewContext = new ViewContext();
		$view = $service->getPreparedExposedTemplateView($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\View\ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithExtensionNameWithoutControllerName() {
		$service = $this->createFluxServiceInstance();
		$viewContext = new ViewContext(NULL, 'Flux');
		$view = $service->getPreparedExposedTemplateView($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\View\ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithExtensionNameAndControllerName() {
		$service = $this->createFluxServiceInstance();
		$viewContext = new ViewContext(NULL, 'Flux', 'API');
		$view = $service->getPreparedExposedTemplateView($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\View\ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canCreateExposedViewWithoutExtensionNameWithControllerName() {
		$service = $this->createFluxServiceInstance();
		$viewContext = new ViewContext(NULL, NULL, 'API');
		$view = $service->getPreparedExposedTemplateView($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\View\ExposedTemplateView', $view);
	}

	/**
	 * @test
	 */
	public function canResolvePrimaryConfigurationProviderWithEmptyArray() {
		$service = $this->createFluxServiceInstance();
		$service->injectProviderResolver($this->objectManager->get('FluidTYPO3\\Flux\\Provider\\ProviderResolver'));
		$result = $service->resolvePrimaryConfigurationProvider('tt_content', NULL);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canGetFormWithPaths() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$service = $this->createFluxServiceInstance();
		$paths = [
			'templateRootPath' => 'EXT:flux/Resources/Private/Templates',
			'partialRootPath' => 'EXT:flux/Resources/Private/Partials',
			'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts'
		];
		$viewContext = new ViewContext($templatePathAndFilename, 'Flux');
		$viewContext->setSectionName('Configuration');
		$viewContext->setTemplatePaths(new TemplatePaths($paths));
		$form1 = $service->getFormFromTemplateFile($viewContext);
		$form2 = $service->getFormFromTemplateFile($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form1);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form2);
	}

	/**
	 * @test
	 */
	public function getFormReturnsNullOnInvalidFile() {
		$templatePathAndFilename = '/void/nothing';
		$service = $this->createFluxServiceInstance();
		$viewContext = new ViewContext($templatePathAndFilename);
		$form = $service->getFormFromTemplateFile($viewContext);
		$this->assertNull($form);
	}

	/**
	 * @test
	 */
	public function canGetFormWithPathsAndTriggerCache() {
		$templatePathAndFilename = GeneralUtility::getFileAbsFileName(self::FIXTURE_TEMPLATE_BASICGRID);
		$service = $this->createFluxServiceInstance();
		$paths = [
			'templateRootPath' => 'EXT:flux/Tests/Fixtures/Templates/',
			'partialRootPath' => 'EXT:flux/Tests/Fixtures/Partials/',
			'layoutRootPath' => 'EXT:flux/Tests/Fixtures/Layouts/'
		];
		$viewContext = new ViewContext($templatePathAndFilename, 'Flux');
		$viewContext->setTemplatePaths(new TemplatePaths($paths));
		$viewContext->setSectionName('Configuration');
		$form = $service->getFormFromTemplateFile($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
		$readAgain = $service->getFormFromTemplateFile($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $readAgain);
	}

	/**
	 * @test
	 */
	public function canReadGridFromTemplateWithoutConvertingToDataStructure() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$form = $this->performBasicTemplateReadTest($templatePathAndFilename);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
	}

	/**
	 * @test
	 */
	public function canRenderTemplateWithCompactingSwitchedOn() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = '1';
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_COMPACTED);
		$service = $this->createFluxServiceInstance();
		$viewContext = new ViewContext($templatePathAndFilename);
		$paths = [
			'templateRootPath' => 'EXT:flux/Tests/Fixtures/Templates/',
			'partialRootPath' => 'EXT:flux/Tests/Fixtures/Partials/',
			'layoutRootPath' => 'EXT:flux/Tests/Fixtures/Layouts/'
		];
		$viewContext->setTemplatePaths(new TemplatePaths($paths));
		$viewContext->setSectionName('Configuration');
		$form = $service->getFormFromTemplateFile($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
		$stored = $form->build();
		$this->assertIsArray($stored);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = $backup;
	}

	/**
	 * @test
	 */
	public function canRenderTemplateWithCompactingSwitchedOff() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = '0';
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_SHEETS);
		$this->performBasicTemplateReadTest($templatePathAndFilename);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'] = $backup;
	}

	/**
	 * @test
	 */
	public function canGetBackendViewConfigurationForExtensionName() {
		$service = $this->createFluxServiceInstance();
		$config = $service->getBackendViewConfigurationForExtensionName('noname');
		$this->assertEmpty($config);
	}

	/**
	 * @test
	 */
	public function canGetViewConfigurationForExtensionNameWhichDoesNotExistAndConstructDefaults() {
		$expected = [
			'templateRootPaths' => [0 => 'EXT:void/Resources/Private/Templates/'],
			'partialRootPaths' => [0 => 'EXT:void/Resources/Private/Partials/'],
			'layoutRootPaths' => [0 => 'EXT:void/Resources/Private/Layouts/'],
		];
		$service = $this->createFluxServiceInstance();
		$config = $service->getViewConfigurationForExtensionName('void');
		$this->assertSame($expected, $config);
	}

	/**
	 * @test
	 */
	public function testGetSettingsForExtensionName() {
		$instance = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['getTypoScriptByPath']);
		$instance->expects($this->once())->method('getTypoScriptByPath')
			->with('plugin.tx_underscore.settings')
			->willReturn(['test' => 'test']);
		$result = $instance->getSettingsForExtensionName('under_score');
		$this->assertEquals(['test' => 'test'], $result);
	}

	/**
	 * @test
	 */
	public function templateWithErrorReturnsFormWithErrorReporter() {
		$viewContext = new ViewContext($this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW));
		$instance = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['getPreparedExposedTemplateView']);
		$instance->expects($this->once())->method('getPreparedExposedTemplateView')->willThrowException(new \RuntimeException());
		$instance->injectObjectManager($this->objectManager);
		$form = $instance->getFormFromTemplateFile($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Field\UserFunction', reset($form->getFields()));
		$this->assertEquals('FluidTYPO3\Flux\UserFunction\ErrorReporter->renderField', reset($form->getFields())->getFunction());
	}

	/**
	 * @test
	 */
	public function createFlashMessageCreatesFlashMessage() {
		$instance = $this->createInstance();
		$result = $this->callInaccessibleMethod($instance, 'createFlashMessage', 'Message', 'Title', 2);
		$this->assertAttributeEquals('Message', 'message', $result);
		$this->assertAttributeEquals('Title', 'title', $result);
		$this->assertAttributeEquals(2, 'severity', $result);
	}

	/**
	 * @test
	 */
	public function messageIgnoresRepeatedMessages() {
		$instance = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['createFlashMessage']);
		$instance->expects($this->once())->method('createFlashMessage')->willReturn(new FlashMessage('Test', 'Test', 2));
		$instance->message('Test', 'Test', 2);
		$instance->message('Test', 'Test', 2);
	}

	/**
	 * @test
	 */
	public function testDebug() {
		$exception = new \RuntimeException('Test');
		$instance = $this->createInstance();
		$result = $instance->debug($exception);
		$this->assertNull($result);
	}

	/**
	 * @test
	 * @dataProvider getConvertFlexFormContentToArrayTestValues
	 * @param string $flexFormContent
	 * @param Form|NULL $form
	 * @param string|NULL $languagePointer
	 * @param string|NULL $valuePointer
	 * @param array $expected
	 */
	public function testConvertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer, $expected) {
		$instance = $this->createInstance();
		$result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getConvertFlexFormContentToArrayTestValues() {
		return [
			['', NULL, '', '', []],
			['', Form::create(), '', '', []],
			[Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD, Form::create(), '', '', ['settings' => ['input' => 0]]]
		];
	}

}
