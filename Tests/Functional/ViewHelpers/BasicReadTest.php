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
class Tx_Flux_Tests_Functional_ViewHelpers_BasicReadTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canReadTemplateWithSectionObject() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_SECTIONOBJECT);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithInputField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_INPUT);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithTextField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_TEXT);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithCheckboxField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_CHECKBOX);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithControllerActionsField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_CONTROLLERACTIONS);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithFileField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_FILE);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithInlineField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_INLINE);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithRelationField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_RELATION);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithSelectField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_SELECT);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithTreeField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_TREE);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithCustomField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_CUSTOM);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithUserFuncField() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_FIELD_USERFUNC);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithAddWizard() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_WIZARDS_ADD);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithColorPickerWizard() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_WIZARDS_COLORPICKER);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithEditWizard() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_WIZARDS_EDIT);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithLinkWizard() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_WIZARDS_LINK);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithListWizard() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_WIZARDS_LIST);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithSelectWizard() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_WIZARDS_SELECT);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithSliderWizard() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_WIZARDS_SLIDER);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithSuggestWizard() {
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_WIZARDS_SUGGEST);
	}

	/**
	 * @test
	 */
	public function canReadTemplateWithMiscellaneousViewHelpers() {
		$variables = array(
			'record' => Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithParentAndWithoutChildren
		);
		$this->assertFluxTemplateLoadsWithoutErrors(self::FIXTURE_TEMPLATE_MISCELLANEOUS, $variables);
	}

}
