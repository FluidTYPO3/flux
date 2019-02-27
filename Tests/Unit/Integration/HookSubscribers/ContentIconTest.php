<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\HookSubscribers\ContentIcon;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class ContentIconHookSubscriberTest
 */
class ContentIconTest extends AbstractTestCase
{
    protected function setUp()
    {
        // Mocking the singleton of IconRegistry is apparently required for unit tests to work on some environments.
        // Since it doesn't matter much what this method actually responds for these tests, we mock it for all envs.
        $iconRegistryMock = $this->getMockBuilder(IconRegistry::class)->setMethods(['isRegistered', 'getIconConfigurationByIdentifier'])->getMock();
        $iconRegistryMock->expects($this->any())->method('isRegistered')->willReturn(true);
        $iconRegistryMock->expects($this->any())->method('getIconConfigurationByIdentifier')->willReturn([
            'provider' => SvgIconProvider::class,
            'options' => [
                'source' => 'EXT:core/Resources/Public/Icons/T3Icons/default/default-not-found.svg'
            ]
        ]);
        GeneralUtility::setSingletonInstance(IconRegistry::class, $iconRegistryMock);
    }

    protected function tearDown()
    {
        GeneralUtility::removeSingletonInstance(IconRegistry::class, GeneralUtility::makeInstance(IconRegistry::class));
    }

    /**
     * @return void
     */
    public function testPerformsInjections()
    {
        $instance = GeneralUtility::makeInstance(ObjectManager::class)->get(ContentIcon::class);
        $this->assertAttributeInstanceOf(FluxService::class, 'fluxService', $instance);
        $this->assertAttributeInstanceOf(ObjectManagerInterface::class, 'objectManager', $instance);
    }

    /**
     * @test
     */
    public function testAddSubIconUsesCache()
    {
        $cache = $this->getMockBuilder(VariableFrontend::class)->disableOriginalConstructor()->setMethods(array('get', 'set'))->getMock();
        $cache->expects($this->once())->method('get')->willReturn('icon');
        $instance = new ContentIcon();
        ObjectAccess::setProperty($instance, 'cache', $cache, true);
        $result = $instance->addSubIcon(array('tt_content', 123, ['foo' => 'bar']), new PageLayoutView());
        $this->assertEquals('icon', $result);
    }

    /**
     * @test
     */
    public function testDrawGridToggle()
    {
        $GLOBALS['LANG'] = $this->getMockBuilder(LanguageService::class)->setMethods(array('sL'))->getMock();
        $GLOBALS['LANG']->expects($this->any())->method('sL')->will($this->returnArgument(0));
        $subject = $this->createInstance();
        $result = $this->callInaccessibleMethod($subject, 'drawGridToggle', ['uid' => 123]);
        $this->assertContains('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:toggle_content', $result);
        $this->assertContains('icon-actions-view-list-expand', $result);
        $this->assertContains('icon-actions-view-list-collapse', $result);
    }

    /**
     * @dataProvider getAddSubIconTestValues
     * @param array $parameters
     * @param ProviderInterface|NULL
     */
    public function testAddSubIcon(array $parameters, $provider)
    {
        $GLOBALS['BE_USER'] = $this->getMockBuilder(BackendUserAuthentication::class)->setMethods(array('calcPerms'))->getMock();
        $GLOBALS['BE_USER']->expects($this->any())->method('calcPerms');
        $GLOBALS['LANG'] = $this->getMockBuilder(LanguageService::class)->setMethods(array('sL'))->getMock();
        $GLOBALS['LANG']->expects($this->any())->method('sL')->will($this->returnArgument(0));

        $GLOBALS['TCA']['tt_content']['columns']['field']['config']['type'] = 'flex';
        $cache = $this->getMockBuilder(VariableFrontend::class)->disableOriginalConstructor()->setMethods(array('get', 'set'))->getMock();
        $cache->expects($this->once())->method('get')->willReturn(false);
        $cache->expects($this->once())->method('set')->with($this->anything());

        $configurationManager = $this->getMockBuilder(ConfigurationManager::class)->getMock();
        $service = $this->getMockBuilder(FluxService::class)->setMethods(array('resolvePrimaryConfigurationProvider','getConfiguration'))->getMock();
        $service->injectConfigurationManager($configurationManager);
        $service->expects($this->any())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
        $instance = new ContentIcon();
        $instance->injectFluxService($service);
        ObjectAccess::setProperty($instance, 'cache', $cache, true);
        if ($provider !== null) {
            $configurationServiceMock = $this->getMockBuilder(FluxService::class)->setMethods(['resolveConfigurationProviders'])->getMock();
            ObjectAccess::setProperty($configurationServiceMock, 'configurationManager', $configurationManager, true);
            ObjectAccess::setProperty($provider, 'configurationService', $configurationServiceMock, true);
        }

        $instance->addSubIcon($parameters, new PageLayoutView());
    }

    /**
     * @return array
     */
    public function getAddSubIconTestValues()
    {
        $formWithoutIcon = Form::create();
        $formWithIcon = Form::create(array('options' => array('icon' => 'icon')));
        $providerWithoutForm = $this->getMockBuilder(Provider::class)->setMethods(array('getForm', 'getGrid'))->getMock();
        $providerWithoutForm->expects($this->any())->method('getForm')->willReturn(null);
        $providerWithoutForm->expects($this->any())->method('getGrid')->willReturn(Form\Container\Grid::create());
        $providerWithFormWithoutIcon = $this->getMockBuilder(Provider::class)->setMethods(array('getForm', 'getGrid'))->getMock();
        $providerWithFormWithoutIcon->expects($this->any())->method('getForm')->willReturn($formWithoutIcon);
        $providerWithFormWithoutIcon->expects($this->any())->method('getGrid')->willReturn(Form\Container\Grid::create());
        $providerWithFormWithIcon = $this->getMockBuilder(Provider::class)->setMethods(array('getForm', 'getGrid'))->getMock();
        $providerWithFormWithIcon->expects($this->any())->method('getForm')->willReturn($formWithIcon);
        $providerWithFormWithIcon->expects($this->any())->method('getGrid')->willReturn(Form\Container\Grid::create());
        return array(
            'no provider' => array(array('tt_content', 1, array()), null),
            'provider without form without field' => array(array('tt_content', 1, array()), $providerWithoutForm),
            'provider without form with field' => array(array('tt_content', 1, array('field' => 'test')), $providerWithoutForm),
            'provider with form without icon' => array(array('tt_content', 1, array('field' => 'test')), $providerWithFormWithoutIcon),
            'provider with form with icon' => array(array('tt_content', 1, array('field' => 'test')), $providerWithFormWithIcon),
        );
    }
}
