<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class ContentIconHookSubscriberTest
 */
class ContentIconHookSubscriberTest extends UnitTestCase {

	/**
	 * @return void
	 */
	public function testPerformsInjections() {
		$instance = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')
			->get('FluidTYPO3\\Flux\\Hooks\\ContentIconHookSubscriber');
		$this->assertAttributeInstanceOf('FluidTYPO3\\Flux\\Service\\FluxService', 'fluxService', $instance);
		$this->assertAttributeInstanceOf('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'objectManager', $instance);
	}

	/**
	 * @test
	 */
	public function testAddSubIconUsesCache() {
		$cache = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager', array('has', 'get'));
		$cache->expects($this->once())->method('has')->willReturn(TRUE);
		$cache->expects($this->once())->method('get')->willReturn('icon');
		$instance = new ContentIconHookSubscriber();
		ObjectAccess::setProperty($instance, 'cache', $cache, TRUE);
		$result = $instance->addSubIcon(array(), new PageLayoutView());
		$this->assertEquals('icon', $result);
	}

	/**
	 * @dataProvider getAddSubIconTestValues
	 * @param array $parameters
	 * @param ProviderInterface|NULL
	 * @param string|NULL $expected
	 */
	public function testAddSubIcon(array $parameters, $provider, $expected) {
		$GLOBALS['TCA']['tt_content']['columns']['field']['config']['type'] = 'flex';
		$cache = $this->getMock('TYPO3\\CMS\\Core\\Cache\\CacheManager', array('has', 'set'));
		$cache->expects($this->once())->method('has')->willReturn(FALSE);
		$cache->expects($this->once())->method('set')->willReturn('icon');
		$service = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', array('resolvePrimaryConfigurationProvider'));
		$service->expects($this->any())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
		$instance = new ContentIconHookSubscriber();
		$instance->injectFluxService($service);
		ObjectAccess::setProperty($instance, 'cache', $cache, TRUE);
		$result = $instance->addSubIcon($parameters, new PageLayoutView());
		if (NULL === $expected) {
			$this->assertNull($result);
		} else {
			$this->assertNotNull($result);
		}
		unset($GLOBALS['TCA']);
	}

	/**
	 * @return array
	 */
	public function getAddSubIconTestValues() {
		$formWithoutIcon = Form::create();
		$formWithIcon = Form::create(array('options' => array('icon' => 'icon')));
		$providerWithoutForm = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', array('getForm'));
		$providerWithoutForm->expects($this->any())->method('getForm')->willReturn(NULL);
		$providerWithFormWithoutIcon = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', array('getForm'));
		$providerWithFormWithoutIcon->expects($this->any())->method('getForm')->willReturn($formWithoutIcon);
		$providerWithFormWithIcon = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', array('getForm'));
		$providerWithFormWithIcon->expects($this->any())->method('getForm')->willReturn($formWithIcon);
		return array(
			array(array('pages', 1, array()), NULL, NULL),
			array(array('tt_content', 1, array()), NULL, NULL),
			array(array('tt_content', 1, array()), $providerWithoutForm, NULL),
			array(array('tt_content', 1, array('field' => 'test')), $providerWithoutForm, NULL),
			array(array('tt_content', 1, array('field' => 'test')), $providerWithFormWithoutIcon, NULL),
			array(array('tt_content', 1, array('field' => 'test')), $providerWithFormWithIcon, 'icon'),
		);
	}

}
