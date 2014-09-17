<?php
namespace FluidTYPO3\Flux\Provider;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * @package Flux
 */
class ProviderResolverTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function loadTypoScriptProvidersReturnsEmptyArrayEarlyIfSetupNotFound() {
		/** @var \FluidTYPO3\Flux\Provider\ProviderResolver $instance */
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Provider\ProviderResolver');
		$configurationManager = $this->getMock('TYPO3\CMS\Extbase\Configuration\ConfigurationManager', array('getConfiguration'));
		$configurationManager->expects($this->once())->method('getConfiguration')->will($this->returnValue(array()));
		$objectManager = $this->getMock('TYPO3\CMS\Extbase\Object\ObjectManager', array('get'));
		$objectManager->expects($this->never())->method('get');
		$instance->injectConfigurationManager($configurationManager);
		$providers = $instance->loadTypoScriptConfigurationProviderInstances();
		$this->assertIsArray($providers);
		$this->assertEmpty($providers);
	}

	/**
	 * @test
	 */
	public function loadTypoScriptProvidersSupportsCustomClassName() {
		/** @var \FluidTYPO3\Flux\Provider\ProviderResolver $instance */
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Provider\ProviderResolver');
		$configurationManager = $this->getMock('TYPO3\CMS\Extbase\Configuration\ConfigurationManager', array('getConfiguration'));
		$objectManager = $this->getMock('TYPO3\CMS\Extbase\Object\ObjectManager', array('get'));
		$mockedTypoScript = array(
			'plugin.' => array(
				'tx_flux.' => array(
					'providers.' => array(
						'dummy.' => array(
							'className' => 'FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider'
						)
					)
				)
			)
		);
		$dummyProvider = $this->objectManager->get('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider');
		$configurationManager->expects($this->once())->method('getConfiguration')->will($this->returnValue($mockedTypoScript));
		$objectManager->expects($this->once())->method('get')->with('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider')->will($this->returnValue($dummyProvider));
		$instance->injectConfigurationManager($configurationManager);
		$instance->injectObjectManager($objectManager);
		$providers = $instance->loadTypoScriptConfigurationProviderInstances();
		$this->assertIsArray($providers);
		$this->assertNotEmpty($providers);
		$this->assertContains($dummyProvider, $providers);
		$this->assertInstanceOf('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider', reset($providers));
	}

}
