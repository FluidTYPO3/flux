<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderResolver;
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
