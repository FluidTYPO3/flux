<?php
namespace FluidTYPO3\Flux\Tests\Unit\View;
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

use FluidTYPO3\Flux\View\ExposedTemplateView;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class ExposedTemplateViewTest extends AbstractTestCase {

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
	 * @disabledtest
	 */
	public function canRenderPreviewSectionWithGrid() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$service = $this->createFluxServiceInstance();
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		$variables = array(
			'row' => $record,
			'grid' => $service->getGridFromTemplateFile($templatePathAndFilename, 'Configuration', 'grid', array(), 'flux', array('record' => $record))
		);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$preview = $view->renderStandaloneSection('Preview', $variables);
		$preview = trim($preview);
		$this->assertNotEmpty($preview);
		$this->assertStringStartsWith('<', $preview);
		$this->assertStringEndsWith('>', $preview);
		$this->assertContains('flux-grid', $preview); // the class targeted in CSS selectors must be applied at least once
		$this->assertContains('content-grid', $preview); // the ID of the Grid must exist
		$this->assertNotContains('Duplicate variable declarations!', $preview); // the ever-so-dreaded error when variables collide
		$this->assertGreaterThanOrEqual(1000, strlen($preview)); // If Grid template contains (moderately) few characters, assume error
	}

	/**
	 * @disabledtest
	 */
	public function canRenderPreviewSectionWithCollapsedGrid() {
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$_COOKIE['fluxCollapseStates'] = urlencode(json_encode(array($record['uid'])));
		$this->canRenderPreviewSectionWithGrid();
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
	 * @disabledtest
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
	 * @disabledtest
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
	 * @disabledtest
	 */
	public function renderingTemplateTwiceTriggersTemplateCompilerSaving() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$view->render();
		$view->getForm();
		$view->getForm();
	}

	/**
	 * @test
	 */
	public function throwsRuntimeExceptionIfImproperlyInitialized() {
		$view = $this->objectManager->get('FluidTYPO3\Flux\View\ExposedTemplateView');
		$this->setExpectedException('RuntimeException', NULL, 1343521593);
		$this->callInaccessibleMethod($view, 'getStoredVariable', 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper', 'storage');
	}

	/**
	 * @disabledtest
	 */
	public function throwsParserExceptionIfTemplateSourceContainsErrors() {
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
	 * @disabledtest
	 */
	public function canGetStoredVariableWithoutConfigurationSectionName() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$this->callInaccessibleMethod($view, 'getStoredVariable', 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper', 'storage');
	}

	/**
	 * @disabledtest
	 */
	public function canGetStoredVariableImmediatelyAfterRemovingCachedFiles() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$this->callInaccessibleMethod($view, 'getStoredVariable', 'FluidTYPO3\Flux\ViewHelpers\FormViewHelper', 'storage');
	}


	/**
	 * @disabledtest
	 */
	public function canGetStoredVariableImmediatelyAfterRemovingCachedFilesWhenCompilerIsDisabled() {
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler'] = 1;
		$this->canGetStoredVariableImmediatelyAfterRemovingCachedFiles();
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['disableCompiler'] = $backup;
	}

	/**
	 * @test
	 */
	public function canBuildPathOverlayConfiguration() {
		$overlayPaths = $this->getFixtureTemplatePaths();
		$templatePaths = $this->getFixtureTemplatePaths();
		$templatePaths['overlays'] = array(
			'test' => $overlayPaths
		);
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$overlayedPaths = $this->callInaccessibleMethod($view, 'buildPathOverlayConfigurations', $templatePaths);
		$this->assertArrayHasKey(0, $overlayedPaths);
		$this->assertArrayHasKey('test', $overlayedPaths);
		$this->assertContains($templatePaths['templateRootPath'], $overlayedPaths['test']);
		$this->assertContains($templatePaths['partialRootPath'], $overlayedPaths['test']);
		$this->assertContains($templatePaths['layoutRootPath'], $overlayedPaths['test']);
		$this->assertContains($templatePaths['templateRootPath'], $overlayedPaths[0]);
		$this->assertContains($templatePaths['partialRootPath'], $overlayedPaths[0]);
		$this->assertContains($templatePaths['layoutRootPath'], $overlayedPaths[0]);
	}

	/**
	 * @disabledtest
	 */
	public function canGetTemplateByActionName() {
		$templatePaths = $this->getFixtureTemplatePaths();
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux', 'API', $templatePaths);
		$controllerContext = ObjectAccess::getProperty($view, 'controllerContext', TRUE);
		$controllerContext->getRequest()->setControllerActionName('index');
		$controllerContext->getRequest()->setControllerName('Grid');
		$view->setControllerContext($controllerContext);
		$output = $view->getTemplatePathAndFilename('index');
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
	 * @test
	 */
	public function canSetAndThenGetTemplateSource() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux', 'API');
		$view->setTemplateSource('dummy-source');
		$this->assertEquals('dummy-source', $this->callInaccessibleMethod($view, 'getTemplateSource'));
	}

	/**
	 * @param $templatePathAndFilename
	 * @return ExposedTemplateView
	 */
	protected function getPreparedViewWithTemplateFile($templatePathAndFilename) {
		$templatePaths = $this->getFixtureTemplatePaths();
		$this->assertFileExists($templatePathAndFilename);
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux', 'API', $templatePaths);
		$view->setTemplatePathAndFilename($templatePathAndFilename);
		return $view;
	}

	/**
	 * @return array
	 */
	protected function getFixtureTemplatePaths() {
		$templatePaths = array(
			'templateRootPath' => ExtensionManagementUtility::extPath('flux', 'TestsRewrite/Fixtures/Templates'),
			'partialRootPath' => ExtensionManagementUtility::extPath('flux', 'TestsRewrite/Fixtures/Partials'),
			'layoutRootPath' => ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts')
		);
		return $templatePaths;
	}

}
