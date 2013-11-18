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
class Tx_Flux_Utility_AutoloadTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canCreateAutoloadRegistry() {
		$registry = Tx_Flux_Utility_Autoload::getAutoloadRegistryForExtension('flux');
		$this->assertIsArray($registry);
		$this->assertGreaterThan(0, count($registry));
	}

	/**
	 * @test
	 */
	public function canGetCachedAutoloadRegistry() {
		Tx_Flux_Utility_Autoload::getAutoloadRegistryForExtension('flux');
		$this->assertFileExists(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('typo3temp/flux-manifest.cache'));
		$registry = Tx_Flux_Utility_Autoload::getAutoloadRegistryForExtension('flux');
		$this->assertIsArray($registry);
		$this->assertGreaterThan(0, count($registry));
	}

	/**
	 * @test
	 */
	public function canResetAutoloadRegistry() {
		Tx_Flux_Utility_Autoload::resetAutoloadingForExtension('flux');
	}

}
