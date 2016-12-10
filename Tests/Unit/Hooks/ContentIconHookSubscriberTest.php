<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class ContentIconHookSubscriberTest
 */
class ContentIconHookSubscriberTest extends UnitTestCase
{

    /**
     * @return void
     */
    public function testPerformsInjections()
    {
        $instance = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')
            ->get('FluidTYPO3\\Flux\\Hooks\\ContentIconHookSubscriber');
        $this->assertAttributeInstanceOf('FluidTYPO3\\Flux\\Service\\FluxService', 'fluxService', $instance);
        $this->assertAttributeInstanceOf('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'objectManager', $instance);
    }

    /**
     * @test
     */
    public function testAddSubIconUsesCache()
    {
        $cache = $this->getMockBuilder('TYPO3\\CMS\\Core\\Cache\\CacheManager')->setMethods(array('has', 'get'))->getMock();
        $cache->expects($this->once())->method('has')->willReturn(true);
        $cache->expects($this->once())->method('get')->willReturn('icon');
        $instance = new ContentIconHookSubscriber();
        ObjectAccess::setProperty($instance, 'cache', $cache, true);
        $result = $instance->addSubIcon(array(), new PageLayoutView());
        $this->assertEquals('icon', $result);
    }

    /**
     * @dataProvider getAddSubIconTestValues
     * @param array $parameters
     * @param ProviderInterface|NULL
     * @param string|NULL $expected
     */
    public function testAddSubIcon(array $parameters, $provider, $expected)
    {
        $GLOBALS['TCA']['tt_content']['columns']['field']['config']['type'] = 'flex';
        $cache = $this->getMockBuilder('TYPO3\\CMS\\Core\\Cache\\CacheManager')->setMethods(array('has', 'set'))->getMock();
        $cache->expects($this->once())->method('has')->willReturn(false);
        $cache->expects($this->once())->method('set')->willReturn('icon');
        $service = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('resolvePrimaryConfigurationProvider'))->getMock();
        $service->expects($this->any())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
        $instance = new ContentIconHookSubscriber();
        $instance->injectFluxService($service);
        ObjectAccess::setProperty($instance, 'cache', $cache, true);
        $result = $instance->addSubIcon($parameters, new PageLayoutView());
        if (null === $expected) {
            $this->assertNull($result);
        } else {
            $this->assertNotNull($result);
        }
        unset($GLOBALS['TCA']);
    }

    /**
     * @return array
     */
    public function getAddSubIconTestValues()
    {
        $formWithoutIcon = Form::create();
        $formWithIcon = Form::create(array('options' => array('icon' => 'icon')));
        $providerWithoutForm = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('getForm'))->getMock();
        $providerWithoutForm->expects($this->any())->method('getForm')->willReturn(null);
        $providerWithFormWithoutIcon = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('getForm'))->getMock();
        $providerWithFormWithoutIcon->expects($this->any())->method('getForm')->willReturn($formWithoutIcon);
        $providerWithFormWithIcon = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('getForm'))->getMock();
        $providerWithFormWithIcon->expects($this->any())->method('getForm')->willReturn($formWithIcon);
        return array(
            array(array('pages', 1, array()), null, null),
            array(array('tt_content', 1, array()), null, null),
            array(array('tt_content', 1, array()), $providerWithoutForm, null),
            array(array('tt_content', 1, array('field' => 'test')), $providerWithoutForm, null),
            array(array('tt_content', 1, array('field' => 'test')), $providerWithFormWithoutIcon, null),
            array(array('tt_content', 1, array('field' => 'test')), $providerWithFormWithIcon, 'icon'),
        );
    }
}
