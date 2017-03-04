<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class ContentIconHookSubscriberTest
 */
class ContentIconHookSubscriberTest extends AbstractTestCase
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
        $cache = $this->getMockBuilder('TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend')->disableOriginalConstructor()->setMethods(array('get', 'set'))->getMock();
        $cache->expects($this->once())->method('get')->willReturn('icon');
        $instance = new ContentIconHookSubscriber();
        ObjectAccess::setProperty($instance, 'cache', $cache, true);
        $result = $instance->addSubIcon(array('tt_content', 123, ['foo' => 'bar']), new PageLayoutView());
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
        $GLOBALS['BE_USER'] = $this->getMockBuilder('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication')->setMethods(array('calcPerms'))->getMock();
        $GLOBALS['BE_USER']->expects($this->any())->method('calcPerms');
        $GLOBALS['LANG'] = $this->getMockBuilder('TYPO3\\CMS\\Lang\\LanguageService')->setMethods(array('sL'))->getMock();
        $GLOBALS['LANG']->expects($this->any())->method('sL')->will($this->returnArgument(0));

        $GLOBALS['TCA']['tt_content']['columns']['field']['config']['type'] = 'flex';
        $cache = $this->getMockBuilder('TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend')->disableOriginalConstructor()->setMethods(array('get', 'set'))->getMock();
        $cache->expects($this->once())->method('get')->willReturn(null);
        $cache->expects($this->once())->method('set')->with($this->anything());

        $configurationManager = $this->getMockBuilder('FluidTYPO3\Flux\Configuration\ConfigurationManager')->getMock();
        $service = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('resolvePrimaryConfigurationProvider','getConfiguration'))->getMock();
        $service->injectConfigurationManager($configurationManager);
        $service->expects($this->any())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
        $instance = new ContentIconHookSubscriber();
        $instance->injectFluxService($service);
        ObjectAccess::setProperty($instance, 'cache', $cache, true);
        if ($provider !== null) {
            $configurationServiceMock = $this->getMockBuilder('FluidTYPO3\Flux\Service\FluxService')->setMethods(['resolveConfigurationProviders'])->getMock();
            ObjectAccess::setProperty($configurationServiceMock, 'configurationManager', $configurationManager, true);
            ObjectAccess::setProperty($provider, 'configurationService', $configurationServiceMock, true);
        }

        $result = $instance->addSubIcon($parameters, new PageLayoutView());
        if (null === $expected) {
            $this->assertEmpty($result);
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
            array(array('tt_content', 1, array()), null, null),
            array(array('tt_content', 1, array()), $providerWithoutForm, null),
            array(array('tt_content', 1, array('field' => 'test')), $providerWithoutForm, null),
            array(array('tt_content', 1, array('field' => 'test')), $providerWithFormWithoutIcon, null),
            array(array('tt_content', 1, array('field' => 'test')), $providerWithFormWithIcon, null),
        );
    }
}
