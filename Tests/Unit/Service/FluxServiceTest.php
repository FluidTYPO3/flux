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
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
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
		return array(
			array(
				array(array('foo' => 'b'), array('foo' => 'a')),
				'foo', 'ASC',
				array(array('foo' => 'a'), array('foo' => 'b'))
			),
			array(
				array('a1' => array('foo' => 'b'), 'a2' => array('foo' => 'a')),
				'foo', 'ASC',
				array('a2' => array('foo' => 'a'), 'a1' => array('foo' => 'b')),
			),
		);
	}

	/**
	 * @test
	 */
	public function dispatchesMessageOnInvalidPathsReturned() {
		$className = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$instance = $this->getMock($className, array('getDefaultViewConfigurationForExtensionKey', 'getTypoScriptByPath'));
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
		$paths = array(
			'templateRootPath' => 'EXT:flux/Resources/Private/Templates',
			'partialRootPath' => 'EXT:flux/Resources/Private/Partials',
			'layoutRootPath' => 'EXT:flux/Resources/Private/Layouts'
		);
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
		$paths = array(
			'templateRootPath' => 'EXT:flux/Tests/Fixtures/Templates/',
			'partialRootPath' => 'EXT:flux/Tests/Fixtures/Partials/',
			'layoutRootPath' => 'EXT:flux/Tests/Fixtures/Layouts/'
		);
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
		$paths = array(
			'templateRootPath' => 'EXT:flux/Tests/Fixtures/Templates/',
			'partialRootPath' => 'EXT:flux/Tests/Fixtures/Partials/',
			'layoutRootPath' => 'EXT:flux/Tests/Fixtures/Layouts/'
		);
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
		$expected = array(
			'templateRootPaths' => array('EXT:void/Resources/Private/Templates/'),
			'partialRootPaths' => array('EXT:void/Resources/Private/Partials/'),
			'layoutRootPaths' => array('EXT:void/Resources/Private/Layouts/'),
		);
		$service = $this->createFluxServiceInstance();
		$config = $service->getViewConfigurationForExtensionName('void');
		$this->assertSame($expected, $config);
	}

	/**
	 * @disabledtest
	 */
	public function templateWithErrorReturnsFormWithErrorReporter() {
		$badSource = '<f:layout invalid="TRUE" />';
		$temp = tempnam($_SERVER['TEMPDIR'], 'badtemplate') . '.html';
		// @todo: use vfs
		$viewContext = new ViewContext($temp);
		$form = $this->createFluxServiceInstance()->getFormFromTemplateFile($viewContext);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Field\UserFunction', reset($form->getFields()));
		$this->assertEquals('FluidTYPO3\Flux\UserFunction\ErrorReporter->renderField', reset($form->getFields())->getFunction());
	}

}
