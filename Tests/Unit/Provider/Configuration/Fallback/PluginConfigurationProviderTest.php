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
class Tx_Flux_Provider_Configuration_Fallback_PluginConfigurationProviderTest extends Tx_Flux_Tests_Provider_AbstractConfigurationProviderTest {

	/**
	 * @return Tx_Flux_Provider_ConfigurationProviderInterface
	 */
	protected function getConfigurationProviderInstance() {
		$potentialClassName = substr(get_class($this), 0, -4);
		/** @var Tx_Flux_Provider_ConfigurationProviderInterface $instance */
		if (TRUE === class_exists($potentialClassName)) {
			$instance = $this->objectManager->get($potentialClassName);
		} else {
			$instance = $this->objectManager->get($this->configurationProviderClassName);
		}
		$instance->setExtensionKey('flux');
		return $instance;
	}

	/**
	 * @test
	 */
	public function canGetAndSetListType() {
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
		/** @var Tx_Flux_Provider_Configuration_Fallback_PluginConfigurationProvider $instance */
		$instance = $this->getConfigurationProviderInstance();
		$instance->setExtensionKey('flux');
		$listType = $instance->getListType($record);
		$this->assertNull($listType);
		$instance->setListType('test');
		$this->assertSame('test', $instance->getListType($record));
	}

}
