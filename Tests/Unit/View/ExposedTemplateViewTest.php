<?php
namespace FluidTYPO3\Flux\Tests\Unit\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\View\ExposedTemplateView;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * ExposedTemplateViewTest
 */
class ExposedTemplateViewTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function getParsedTemplateReturnsCompiledTemplateIfFound() {
		$instance = $this->getMock($this->createInstanceClassName(), array('getTemplateIdentifier'));
		$instance->expects($this->once())->method('getTemplateIdentifier');
		$compiler = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\Compiler\\TemplateCompiler', array('has', 'get'));
		$compiler->expects($this->once())->method('has')->willReturn(TRUE);
		$compiler->expects($this->once())->method('get')->willReturn('foobar');
		ObjectAccess::setProperty($instance, 'templateCompiler', $compiler, TRUE);
		$result = $this->callInaccessibleMethod($instance, 'getParsedTemplate');
		$this->assertEquals('foobar', $result);
	}

	/**
	 * @test
	 */
	public function previewSectionIsOptional() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$preview = $view->renderStandaloneSection('Preview', array(), TRUE);
		$this->assertStringMatchesFormat('', $preview);
	}

	/**
	 * @test
	 */
	public function canRenderEmptyPreviewSection() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_PREVIEW_EMPTY);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$preview = $view->renderStandaloneSection('Preview', array(), TRUE);
		$preview = trim($preview);
		$this->assertEmpty($preview);
	}

	/**
	 * @test
	 */
	public function canRenderCustomSection() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_CUSTOM_SECTION);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$sectionContent = $view->renderStandaloneSection('Custom', array(), TRUE);
		$sectionContent = trim($sectionContent);
		$this->assertEquals('This is a custom section. Do not change this placeholder text.', $sectionContent);
	}

	/**
	 * @test
	 */
	public function canRenderRaw() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_CUSTOM_SECTION);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$sectionContent = $view->render();
		$sectionContent = trim($sectionContent);
		$this->assertEmpty($sectionContent);
		$this->assertNotContains('<', $sectionContent);
		$this->assertNotContains('>', $sectionContent);
	}

	/**
	 * @test
	 */
	public function canRenderWithDisabledCompiler() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_CUSTOM_SECTION);
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler'] = 1;
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$sectionContent = $view->render();
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler'] = $backup;
		$sectionContent = trim($sectionContent);
		$this->assertEmpty($sectionContent);
		$this->assertNotContains('<', $sectionContent);
		$this->assertNotContains('>', $sectionContent);
	}

	/**
	 * @test
	 */
	public function createsDefaultFormFromInvalidTemplate() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_WITHOUTFORM);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$form = $view->getForm('Configuration');
		$this->assertIsValidAndWorkingFormObject($form);
	}

	/**
	 * @test
	 */
	public function renderingTemplateTwiceTriggersTemplateCompilerSaving() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$view->render();
		$form1 = $view->getForm();
		$form2 = $view->getForm();
		$this->assertSame($form1, $form2);
	}

	/**
	 * @test
	 */
	public function throwsRuntimeExceptionIfImproperlyInitialized() {
		$view = $this->objectManager->get('FluidTYPO3\Flux\View\ExposedTemplateView');
		$this->setExpectedException('RuntimeException', '', 1343521593);
		$this->callInaccessibleMethod($view, 'getStoredVariable', 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper', 'storage');
	}

	/**
	 * @disabledtest
	 */
	public function throwsParserExceptionIfTemplateSourceContainsErrors() {
		// @TODO: use vfs
		$validTemplatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$validTemplateSource = file_get_contents($validTemplatePathAndFilename);
		$invalidTemplateSource = $validTemplateSource . LF . LF . '</f:section>' . LF;
		$temporaryFilePathAndFilename = GeneralUtility::getFileAbsFileName('typo3temp/flux-temp-' . uniqid() . '.html');
		GeneralUtility::writeFile($temporaryFilePathAndFilename, $invalidTemplateSource);
		$view = $this->getPreparedViewWithTemplateFile($temporaryFilePathAndFilename);
		$this->setExpectedException('Tx_Fluid_Core_Parser_Exception');
		$this->callInaccessibleMethod($view, 'getStoredVariable', 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper', 'storage');
	}

	/**
	 * @test
	 */
	public function canGetStoredVariableWithoutConfigurationSectionName() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$result = $this->callInaccessibleMethod($view, 'getStoredVariable', 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper', 'storage');
		$this->assertEmpty($result);
	}

	/**
	 * @test
	 */
	public function canGetTemplateByActionName() {
		$templatePaths = $this->getFixtureTemplatePaths();
		$service = $this->createFluxServiceInstance();
		$viewContext = new ViewContext(NULL, 'Flux', 'Content');
		$viewContext->setTemplatePaths(new TemplatePaths($templatePaths));
		$view = $service->getPreparedExposedTemplateView($viewContext);
		$controllerContext = ObjectAccess::getProperty($view, 'controllerContext', TRUE);
		$controllerContext->getRequest()->setControllerActionName('dummy');
		$controllerContext->getRequest()->setControllerName('Content');
		$view->setControllerContext($controllerContext);
		$output = $view->getTemplatePathAndFilename('dummy');
		$this->assertNotEmpty($output);
		$this->assertFileExists($output);
	}

	/**
	 * @test
	 */
	public function getTemplatePathAndFilenameCallsExpectedMethodSequenceInStandardTemplateViewMode() {
		$request = new Request();
		$controllerContext = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ControllerContext', array('getRequest'));
		$controllerContext->expects($this->once())->method('getRequest')->will($this->returnValue($request));
		$mock = $this->getMock($this->createInstanceClassName(), array('expandGenericPathPattern'), array(), '', FALSE);
		$mock->expects($this->any())->method('expandGenericPathPattern')->will($this->returnValue(array('/dev/null/')));
		$mock->setControllerContext($controllerContext);
		$this->setExpectedException('TYPO3\\CMS\\Fluid\\View\\Exception\\InvalidTemplateResourceException');
		$mock->getTemplatePathAndFilename();
	}

	/**
	 * @param $templatePathAndFilename
	 * @return ExposedTemplateView
	 */
	protected function getPreparedViewWithTemplateFile($templatePathAndFilename) {
		$templatePaths = $this->getFixtureTemplatePaths();
		$service = $this->createFluxServiceInstance();
		$viewContext = new ViewContext($templatePathAndFilename, 'Flux', 'API');
		$viewContext->setTemplatePaths(new TemplatePaths($templatePaths));
		$view = $service->getPreparedExposedTemplateView($viewContext);
		return $view;
	}

	/**
	 * @return array
	 */
	protected function getFixtureTemplatePaths() {
		$templatePaths = array(
			'templateRootPaths' => array(ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Templates')),
			'partialRootPaths' => array(ExtensionManagementUtility::extPath('flux', 'Tests/Fixtures/Partials')),
			'layoutRootPaths' => array(ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts'))
		);
		return $templatePaths;
	}

}
