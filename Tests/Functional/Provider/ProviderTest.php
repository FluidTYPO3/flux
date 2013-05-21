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
class Tx_Flux_Tests_Functional_Provider_ProviderTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canDetectDefaultFluxContentConfigurationProvider() {
		$service = $this->createFluxServiceInstance();
		$provider = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', array(), 'flux');
		$this->assertInstanceOf('Tx_Flux_Provider_Configuration_ContentObjectConfigurationProvider', $provider);
	}

	/**
	 * @test
	 */
	public function canDetectConfigurationProviderWithoutFieldName() {
		$service = $this->createFluxServiceInstance();
		$providers = $service->resolveConfigurationProviders('tt_content', NULL, array(), 'flux');
		$this->assertArrayHasKey(0, $providers);
	}

	/**
	 * @test
	 */
	public function canDetectConfigurationProviderWithFieldName() {
		$service = $this->createFluxServiceInstance();
		$providers = $service->resolveConfigurationProviders('tt_content', 'pi_flexform', array(), 'flux');
		$this->assertArrayHasKey(0, $providers);
	}

	/**
	 * @test
	 */
	public function canReturnExtensionKey() {
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$service = $this->createFluxServiceInstance();
		$provider = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', array(), 'flux');
		$this->assertInstanceOf('Tx_Flux_Provider_Configuration_ContentObjectConfigurationProvider', $provider);
		$extensionKey = $provider->getExtensionKey($record);
		$this->assertNotEmpty($extensionKey);
		$this->assertRegExp('/[a-z_]+/', $extensionKey);
	}

	/**
	 * @test
	 */
	public function canGetFlexFormValues() {
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Tx_Flux_Tests_Fixtures_Data_Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		$service = $this->createFluxServiceInstance();
		$provider = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', array(), 'flux');
		$this->assertInstanceOf('Tx_Flux_Provider_Configuration_ContentObjectConfigurationProvider', $provider);
		$values = $provider->getFlexFormValues($record);
		$this->assertSame($values, array('settings' => array('input' => '0')));
	}

	/**
	 * @test
	 */
	public function canReturnPathSetByRecordWithoutParentAndWithoutChildren() {
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$service = $this->createFluxServiceInstance();
		$provider = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', $row);
		$this->assertInstanceOf('Tx_Flux_Provider_ConfigurationProviderInterface', $provider);
		$paths = $provider->getTemplatePaths($row);
		$this->assertIsArray($paths);
	}

}
