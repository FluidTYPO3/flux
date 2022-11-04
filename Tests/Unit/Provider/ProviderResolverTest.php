<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ContentProvider;
use FluidTYPO3\Flux\Provider\Interfaces\ContentTypeProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleCore;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyBasicProvider;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\InvalidConfigurationProvider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * ProviderResolverTest
 */
class ProviderResolverTest extends AbstractTestCase
{
    public function testResolveConfigurationProvidersFiltersProviders(): void
    {
        $subject = $this->getMockBuilder(ProviderResolver::class)
            ->setMethods(['loadTypoScriptConfigurationProviderInstances', 'validateAndInstantiateProviders'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('loadTypoScriptConfigurationProviderInstances')->willReturn([]);
        $subject->method('validateAndInstantiateProviders')->willReturnArgument(0);

        $provider1 = new ContentProvider();
        $provider2 = new PageProvider();
        $provider3 = new DummyBasicProvider();

        AccessibleCore::setRegisteredProviders(
            [
                $provider1,
                $provider2,
                $provider3
            ]
        );

        $resolved = $subject->resolveConfigurationProviders('tt_content', null, null, null, [RecordProviderInterface::class]);
        self::assertSame([], $resolved);

        AccessibleCore::setRegisteredProviders([]);
    }

    /**
     * @test
     */
    public function loadTypoScriptProvidersReturnsEmptyArrayEarlyIfSetupNotFound()
    {
        $instance = new ProviderResolver();
        $configurationService = $this->getMockBuilder(FluxService::class)->setMethods(array('getTypoScriptByPath'))->getMock();
        $configurationService->expects($this->once())->method('getTypoScriptByPath')->will($this->returnValue(array()));
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMockForAbstractClass();
        $objectManager->expects($this->never())->method('get');
        $instance->injectConfigurationService($configurationService);
        $providers = $instance->loadTypoScriptConfigurationProviderInstances();
        $this->assertIsArray($providers);
        $this->assertEmpty($providers);
    }

    /**
     * @test
     */
    public function loadTypoScriptProvidersSupportsCustomClassName()
    {
        /** @var \FluidTYPO3\Flux\Provider\ProviderResolver $instance */
        $instance = new ProviderResolver();
        $configurationService = $this->getMockBuilder(FluxService::class)->setMethods(array('getTypoScriptByPath'))->getMock();
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMockForAbstractClass();
        $mockedTypoScript = array(
            'dummy.' => array(
                'className' => DummyConfigurationProvider::class,
            )
        );
        $dummyProvider = new DummyConfigurationProvider();
        $configurationService->expects($this->once())->method('getTypoScriptByPath')->will($this->returnValue($mockedTypoScript));
        $objectManager->expects($this->once())->method('get')->with(DummyConfigurationProvider::class)->will($this->returnValue($dummyProvider));
        $instance->injectConfigurationService($configurationService);
        $instance->injectObjectManager($objectManager);
        $providers = $instance->loadTypoScriptConfigurationProviderInstances();
        $this->assertIsArray($providers);
        $this->assertNotEmpty($providers);
        $this->assertContains($dummyProvider, $providers);
        $this->assertInstanceOf(DummyConfigurationProvider::class, reset($providers));
    }

    /**
     * @test
     * @dataProvider getValidateAndInstantiateProvidersTestValues
     * @param array $providers
     */
    public function validateAndInstantiateProvidersCreatesInstances(array $providers)
    {
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMockForAbstractClass();
        $objectManager->method('get')->willReturn(new Provider());
        $instance = $this->createInstance();
        $instance->injectObjectManager($objectManager);
        $result = $this->callInaccessibleMethod($instance, 'validateAndInstantiateProviders', $providers);
        $this->assertSameSize($providers, $result);
        foreach ($result as $provider) {
            $this->assertInstanceOf(ProviderInterface::class, $provider);
        }
    }

    /**
     * @return array
     */
    public function getValidateAndInstantiateProvidersTestValues()
    {
        return array(
            array(array()),
            array(array(Provider::class)),
            array(array(new Provider())),
        );
    }

    /**
     * @test
     * @dataProvider getValidateAndInstantiateProvidersErrorTestValues
     * @param array $providers
     */
    public function validateAndInstantiateProvidersThrowsExceptionOnInvalidClasses(array $providers)
    {
        $instance = $this->createInstance();
        $this->expectException('RuntimeException');
        $this->callInaccessibleMethod($instance, 'validateAndInstantiateProviders', $providers);
    }

    /**
     * @return array
     */
    public function getValidateAndInstantiateProvidersErrorTestValues()
    {
        return array(
            array(array('FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\InvalidConfigurationProvider')),
            array(array(new InvalidConfigurationProvider()))
        );
    }

    /**
     * @test
     * @dataProvider getProviderTestValues
     * @param array $providers
     */
    public function resolveConfigurationProvidersReturnsExpectedProviders(array $providers)
    {
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getAllRegisteredProviderInstances'))->getMock();
        $instance->expects($this->once())->method('getAllRegisteredProviderInstances')->willReturn($providers);
        $result = $instance->resolveConfigurationProviders('table', 'field');
        $this->assertEquals(array_reverse($providers), $result);
    }

    /**
     * @test
     * @dataProvider getProviderTestValues
     * @param array $providers
     */
    public function resolvePrimaryConfigurationProvidersReturnsExpectedProvider(array $providers)
    {
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getAllRegisteredProviderInstances'))->getMock();
        $instance->expects($this->once())->method('getAllRegisteredProviderInstances')->willReturn($providers);
        $result = $instance->resolvePrimaryConfigurationProvider('table', 'field');
        $this->assertEquals(array_pop($providers), $result);
    }

    /**
     * @return array
     */
    public function getProviderTestValues()
    {
        $priority50 = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('getPriority', 'trigger'))->getMock();
        $priority50->expects($this->atLeastOnce())->method('getPriority')->willReturn(50);
        $priority50->expects($this->atLeastOnce())->method('trigger')->willReturn(true);
        $priority40 = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('getPriority', 'trigger'))->getMock();
        $priority40->expects($this->atLeastOnce())->method('getPriority')->willReturn(40);
        $priority40->expects($this->atLeastOnce())->method('trigger')->willReturn(true);
        return array(
            array(array($priority40, $priority50))
        );
    }
}
