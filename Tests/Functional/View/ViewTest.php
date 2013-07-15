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
class Tx_Flux_Tests_Functional_View_ViewTest extends Tx_Flux_Tests_AbstractFunctionalTest {

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
	public function canRenderPreviewSectionWithGrid() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_BASICGRID);
		$service = $this->createFluxServiceInstance();
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Tx_Flux_Tests_Fixtures_Data_Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		$variables = array(
			'row' => $record,
			'grid' => $service->getGridFromTemplateFile($templatePathAndFilename, 'Configuration', 'grid', array(), 'flux', array('record' => $record))
		);
		$view = $this->getPreparedViewWithTemplateFile($templatePathAndFilename);
		$preview = $view->renderStandaloneSection('Preview', $variables, TRUE);
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
	 * @test
	 */
	public function canRenderPreviewSectionWithCollapsedGrid() {
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
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
	 * @param $templatePathAndFilename
	 * @return Tx_Flux_MVC_View_ExposedTemplateView
	 */
	protected function getPreparedViewWithTemplateFile($templatePathAndFilename) {
		$this->assertFileExists($templatePathAndFilename);
		$service = $this->createFluxServiceInstance();
		$view = $service->getPreparedExposedTemplateView('Flux', 'API');
		$view->setTemplatePathAndFilename($templatePathAndFilename);
		return $view;
	}

}
