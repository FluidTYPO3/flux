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

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_View_ExposedTemplateViewTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function previewSectionIsOptional() {
		$this->truncateFluidCodeCache();
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$preview = $view->renderStandaloneSection('Preview', array(), TRUE);
		$this->assertStringMatchesFormat('', $preview);
	}

	/**
	 * @test
	 */
	public function canRenderEmptyPreviewSection() {
		$this->truncateFluidCodeCache();
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
		$this->truncateFluidCodeCache();
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$service = $this->createFluxServiceInstance();
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Tx_Flux_Tests_Fixtures_Data_Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
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
		$this->truncateFluidCodeCache();
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$_COOKIE['fluxCollapseStates'] = urlencode(json_encode(array($record['uid'])));
		$this->canRenderPreviewSectionWithGrid();
	}

	/**
	 * @test
	 */
	public function canRenderCustomSection() {
		$this->truncateFluidCodeCache();
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
		$this->truncateFluidCodeCache();
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
		$this->truncateFluidCodeCache();
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
		$this->truncateFluidCodeCache();
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_WITHOUTFORM);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$form = $view->getForm('Configuration');
		$this->assertIsValidAndWorkingFormObject($form);
	}

	/**
	 * @test
	 */
	public function renderingTemplateTwiceTriggersTemplateCompilerSaving() {
		$this->truncateFluidCodeCache();
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
		$this->truncateFluidCodeCache();
		$view = $this->objectManager->get('Tx_Flux_View_ExposedTemplateView');
		$this->setExpectedException('RuntimeException', NULL, 1343521593);
		$this->callInaccessibleMethod($view, 'getStoredVariable', 'Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage');
	}

	/**
	 * @test
	 */
	public function throwsParserExceptionIfTemplateSourceContainsErrors() {
		$this->truncateFluidCodeCache();
		$validTemplatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$validTemplateSource = file_get_contents($validTemplatePathAndFilename);
		$invalidTemplateSource = $validTemplateSource . LF . LF . '</f:section>' . LF;
		$temporaryFilePathAndFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('typo3temp/flux-temp-' . uniqid() . '.html');
		\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($temporaryFilePathAndFilename, $invalidTemplateSource);
		$view = $this->getPreparedViewWithTemplateFile($temporaryFilePathAndFilename);
		$this->setExpectedException('Tx_Fluid_Core_Parser_Exception');
		$this->callInaccessibleMethod($view, 'getStoredVariable', 'Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage');
	}

	/**
	 * @test
	 */
	public function canGetStoredVariableWithoutConfigurationSectionName() {
		$this->truncateFluidCodeCache();
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$this->callInaccessibleMethod($view, 'getStoredVariable', 'Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage');
	}

	/**
	 * @test
	 */
	public function canGetStoredVariableImmediatelyAfterRemovingCachedFiles() {
		$this->truncateFluidCodeCache();
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$this->callInaccessibleMethod($view, 'getStoredVariable', 'Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage');
	}


	/**
	 * @test
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
		$overlayPaths = array(
			'templateRootPath' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('extbase', 'Resources/Private/Templates'),
			'partialRootPath' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('extbase', 'Resources/Private/Partials'),
			'layoutRootPath' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('extbase', 'Resources/Private/Layouts'),
		);
		$templatePaths = array(
			'templateRootPath' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates'),
			'partialRootPath' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials'),
			'layoutRootPath' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts'),
			'overlays' => array(
				'test' => $overlayPaths
			)
		);
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$overlayedPaths = $this->callInaccessibleMethod($view, 'buildPathOverlayConfigurations', $templatePaths);
		$this->assertArrayHasKey(0, $overlayedPaths);
		$this->assertArrayHasKey('test', $overlayedPaths);
		$this->assertContains($overlayPaths['templateRootPath'], $overlayedPaths['test']);
		$this->assertContains($overlayPaths['partialRootPath'], $overlayedPaths['test']);
		$this->assertContains($overlayPaths['layoutRootPath'], $overlayedPaths['test']);
		$this->assertContains($templatePaths['templateRootPath'], $overlayedPaths[0]);
		$this->assertContains($templatePaths['partialRootPath'], $overlayedPaths[0]);
		$this->assertContains($templatePaths['layoutRootPath'], $overlayedPaths[0]);
	}

	/**
	 * @test
	 */
	public function canGetTemplateByActionName() {
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux', 'API');
		$controllerContext = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($view, 'controllerContext', TRUE);
		$controllerContext->getRequest()->setControllerActionName('index');
		$controllerContext->getRequest()->setControllerName('Grid');
		$view->setControllerContext($controllerContext);
		$view->setTemplateRootPath(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/ViewHelpers/Widget/'));
		$output = $view->getTemplatePathAndFilename('index');
		$this->assertNotEmpty($output);
		$this->assertFileExists($output);
	}

	/**
	 * @param $templatePathAndFilename
	 * @return Tx_Flux_View_ExposedTemplateView
	 */
	protected function getPreparedViewWithTemplateFile($templatePathAndFilename) {
		$this->assertFileExists($templatePathAndFilename);
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux', 'API');
		$view->setTemplatePathAndFilename($templatePathAndFilename);
		return $view;
	}

}
