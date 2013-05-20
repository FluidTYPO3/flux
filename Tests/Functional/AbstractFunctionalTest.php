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

	/**
	 * @var $string
	 */
	const FIXTURE_TEMPLATE_SHEETS = 'EXT:flux/Tests/Fixtures/Templates/Sheets.html';

	/**
	 * @var $string
	 */
	const FIXTURE_TEMPLATE_CUSTOM_SECTION = 'EXT:flux/Tests/Fixtures/Templates/CustomSection.html';

	/**
	 * @var $string
	 */
	const FIXTURE_TEMPLATE_PREVIEW_EMPTY = 'EXT:flux/Tests/Fixtures/Templates/EmptyPreview.html';

	/**
	 * @var $string
	 */
	const FIXTURE_TEMPLATE_BASICGRID = 'EXT:flux/Tests/Fixtures/Templates/BasicGrid.html';

	/**
	 * @var $string
	 */
	const FIXTURE_TEMPLATE_ALLFIELDTYPES = 'EXT:flux/Tests/Fixtures/Templates/AllFieldTypes.html';

	/**
	 * @var string
	 */
	const FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL = 'EXT:flux/Tests/Fixtures/Templates/AbsolutelyMinimal.html';

	/**
	 * @param mixed $value
	 * @return void
	 */
	protected function assertIsArray($value) {
		$isArrayConstraint = new PHPUnit_Framework_Constraint_IsType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY);
		$this->assertThat($value, $isArrayConstraint);
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
