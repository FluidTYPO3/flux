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
class Tx_Vhs_Tests_Functional_Provider_StructureProviderTest extends Tx_Vhs_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function ifNoFieldsInStorageThenUseFallbackStructureProvider() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
		$service = $this->createFluxServiceInstance();
		$stored = $service->getFlexFormConfigurationFromFile($templatePathAndFilename, array());
		$dataStructure = $service->convertFlexFormConfigurationToDataStructure($stored);
		$this->assertArrayHasKey('ROOT', $dataStructure); // processing has worked in some way
		$this->assertArrayHasKey('el', $dataStructure['ROOT']); // processing has yielded at least some fields
		$this->assertEquals(1, count($dataStructure['ROOT']['el'])); // resulting structure has one field only
		$this->assertArrayHasKey('func', $dataStructure['ROOT']['el']); // the only field is a user function field type
		$this->assertEquals('user', $dataStructure['ROOT']['el']['func']['TCEforms']['config']['type']); // field is the right type
		// final check: field must use the NoFields UserFunction to render a message
		$this->assertEquals('Tx_Flux_UserFunction_NoFields->renderField', $dataStructure['ROOT']['el']['func']['TCEforms']['config']['userFunc']);
	}

	/**
	 * @test
	 */
	public function canReadSheetsFromTemplateUsingServiceToConvertConfigurationToDataStructure() {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename(self::FIXTURE_TEMPLATE_SHEETS);
		$service = $this->createFluxServiceInstance();
		$stored = $service->getStoredVariable($templatePathAndFilename, 'storage');
		$structure = $service->convertFlexFormConfigurationToDataStructure($stored);
		$isArrayConstraint = new PHPUnit_Framework_Constraint_IsType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY);
		$this->assertThat($structure['sheets'], $isArrayConstraint);
		$this->assertNotEmpty($structure['sheets']);
		$this->assertArrayHasKey('options', $structure['sheets']);
		$this->assertArrayHasKey('another', $structure['sheets']);
	}

}
