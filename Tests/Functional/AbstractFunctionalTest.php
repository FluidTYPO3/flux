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

require_once t3lib_extMgm::extPath('flux', 'Tests/Fixtures/Data/Xml.php');
require_once t3lib_extMgm::extPath('flux', 'Tests/Fixtures/Data/Records.php');

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
abstract class Tx_Flux_Tests_AbstractFunctionalTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	const FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL = 'EXT:flux/Tests/Fixtures/Templates/AbsolutelyMinimal.html';
	const FIXTURE_TEMPLATE_SHEETS = 'EXT:flux/Tests/Fixtures/Templates/Sheets.html';
	const FIXTURE_TEMPLATE_CUSTOM_SECTION = 'EXT:flux/Tests/Fixtures/Templates/CustomSection.html';
	const FIXTURE_TEMPLATE_PREVIEW_EMPTY = 'EXT:flux/Tests/Fixtures/Templates/EmptyPreview.html';
	const FIXTURE_TEMPLATE_BASICGRID = 'EXT:flux/Tests/Fixtures/Templates/BasicGrid.html';
	const FIXTURE_TEMPLATE_SECTIONOBJECT = 'EXT:flux/Tests/Fixtures/Templates/SectionObject.html';
	const FIXTURE_TEMPLATE_FIELD_INPUT = 'EXT:flux/Tests/Fixtures/Templates/Fields/Input.html';
	const FIXTURE_TEMPLATE_FIELD_TEXT = 'EXT:flux/Tests/Fixtures/Templates/Fields/Text.html';
	const FIXTURE_TEMPLATE_FIELD_CHECKBOX = 'EXT:flux/Tests/Fixtures/Templates/Fields/Checkbox.html';
	const FIXTURE_TEMPLATE_FIELD_FILE = 'EXT:flux/Tests/Fixtures/Templates/Fields/File.html';
	const FIXTURE_TEMPLATE_FIELD_GROUP = 'EXT:flux/Tests/Fixtures/Templates/Fields/Group.html';
	const FIXTURE_TEMPLATE_FIELD_INLINE = 'EXT:flux/Tests/Fixtures/Templates/Fields/Inline.html';
	const FIXTURE_TEMPLATE_FIELD_SELECT = 'EXT:flux/Tests/Fixtures/Templates/Fields/Select.html';
	const FIXTURE_TEMPLATE_FIELD_TREE = 'EXT:flux/Tests/Fixtures/Templates/Fields/Tree.html';
	const FIXTURE_TEMPLATE_FIELD_CUSTOM = 'EXT:flux/Tests/Fixtures/Templates/Fields/Custom.html';
	const FIXTURE_TEMPLATE_FIELD_USERFUNC = 'EXT:flux/Tests/Fixtures/Templates/Fields/UserFunc.html';
	const FIXTURE_TEMPLATE_WIZARDS_ADD = 'EXT:flux/Tests/Fixtures/Templates/Wizards/Add.html';
	const FIXTURE_TEMPLATE_WIZARDS_COLORPICKER = 'EXT:flux/Tests/Fixtures/Templates/Wizards/ColorPicker.html';
	const FIXTURE_TEMPLATE_WIZARDS_EDIT = 'EXT:flux/Tests/Fixtures/Templates/Wizards/Edit.html';
	const FIXTURE_TEMPLATE_WIZARDS_LINK = 'EXT:flux/Tests/Fixtures/Templates/Wizards/Link.html';
	const FIXTURE_TEMPLATE_WIZARDS_LIST = 'EXT:flux/Tests/Fixtures/Templates/Wizards/List.html';
	const FIXTURE_TEMPLATE_WIZARDS_SELECT = 'EXT:flux/Tests/Fixtures/Templates/Wizards/Select.html';
	const FIXTURE_TEMPLATE_WIZARDS_SLIDER = 'EXT:flux/Tests/Fixtures/Templates/Wizards/Slider.html';
	const FIXTURE_TEMPLATE_WIZARDS_SUGGEST = 'EXT:flux/Tests/Fixtures/Templates/Wizards/Suggest.html';

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function assertIsArray($value) {
		$isArrayConstraint = new PHPUnit_Framework_Constraint_IsType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY);
		$this->assertThat($value, $isArrayConstraint);
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function assertIsString($value) {
		$isStringConstraint = new PHPUnit_Framework_Constraint_IsType(PHPUnit_Framework_Constraint_IsType::TYPE_STRING);
		$this->assertThat($value, $isStringConstraint);
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function assertIsInteger($value) {
		$isIntegerConstraint = new PHPUnit_Framework_Constraint_IsType(PHPUnit_Framework_Constraint_IsType::TYPE_INT);
		$this->assertThat($value, $isIntegerConstraint);
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function assertIsBoolean($value) {
		$isBooleanConstraint = new PHPUnit_Framework_Constraint_IsType(PHPUnit_Framework_Constraint_IsType::TYPE_BOOL);
		$this->assertThat($value, $isBooleanConstraint);
	}

	/**
	 * @param string $templateName
	 * @param array $variables
	 */
	protected function assertFluxTemplateLoadsWithoutErrors($templateName, $variables = array()) {
		if (0 === count($variables)) {
			$variables = array('row' => Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren);
		}
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename($templateName);
		$service = $this->createFluxServiceInstance();
		$stored = $service->getStoredVariable($templatePathAndFilename, 'storage', 'Configuration', array(), 'Flux', $variables);
		$this->assertIsArray($stored);
		$this->assertArrayHasKey('fields', $stored);
		$this->assertArrayHasKey('label', $stored);
		$this->assertNotEmpty($stored['label']);
		$this->assertArrayHasKey('id', $stored);
		$this->assertNotEmpty($stored['id']);
	}

	/**
	 * @return string
	 */
	protected function getShorthandFixtureTemplatePathAndFilename() {
		return self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
	}

	/**
	 * @param string $shorthandTemplatePath
	 * @return string
	 */
	protected function getAbsoluteFixtureTemplatePathAndFilename($shorthandTemplatePath) {
		return t3lib_div::getFileAbsFileName($shorthandTemplatePath);
	}

	/**
	 * @return Tx_Flux_Service_FluxService
	 */
	protected function createFluxServiceInstance() {
		/** @var $fluxService Tx_Flux_Service_FluxService */
		$fluxService = $this->objectManager->get('Tx_Flux_Service_FluxService');
		return $fluxService;
	}

}
