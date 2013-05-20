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
abstract class Tx_Vhs_Tests_AbstractFunctionalTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	/**
	 * @var $string
	 */
	const FIXTURE_TEMPLATE_ALLFIELDTYPES = 'EXT:flux/Tests/Fixtures/Templates/AllFieldTypes.html';

	/**
	 * @var string
	 */
	const FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL = 'EXT:flux/Tests/Fixtures/Templates/AbsolutelyMinimal.html';

	/**
	 * @return string
	 */
	protected function getShorthandFixtureTemplatePathAndFilename() {
		return self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL;
	}

	/**
	 * @return string
	 */
	protected function getAbsoluteFixtureTemplatePathAndFilename() {
		return t3lib_div::getFileAbsFileName(self::FIXTURE_TEMPLATE_ABSOLUTELYMINIMAL);
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
