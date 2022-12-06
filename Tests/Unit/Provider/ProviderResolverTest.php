<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ContentProvider;
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        $provider1 = $this->getMockBuilder(ContentProvider::class)->disableOriginalConstructor()->getMock();
        $provider2 = $this->getMockBuilder(PageProvider::class)->disableOriginalConstructor()->getMock();
        $provider3 = $this->getMockBuilder(DummyBasicProvider::class)->disableOriginalConstructor()->getMock();

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
        $configurationService = $this->getMockBuilder(FluxService::class)
            ->setMethods(array('getTypoScriptByPath'))
            ->disableOriginalConstructor()
            ->getMock();
        $configurationService->expects($this->once())->method('getTypoScriptByPath')->will($this->returnValue(array()));
        GeneralUtility::setSingletonInstance(FluxService::class, $configurationService);

        $instance = new ProviderResolver();

        $providers = $instance->loadTypoScriptConfigurationProviderInstances();
        $this->assertIsArray($providers);
        $this->assertEmpty($providers);
    }

    /**
     * @test
     */
    public function loadTypoScriptProvidersSupportsCustomClassName()
    {
        $mockedTypoScript = array(
            'dummy.' => array(
                'className' => DummyConfigurationProvider::class,
            )
        );

        $configurationService = $this->getMockBuilder(FluxService::class)
            ->setMethods(array('getTypoScriptByPath'))
            ->disableOriginalConstructor()
            ->getMock();
        $configurationService->expects($this->once())
            ->method('getTypoScriptByPath')
            ->willReturn($mockedTypoScript);

        GeneralUtility::setSingletonInstance(FluxService::class, $configurationService);

        /** @var \FluidTYPO3\Flux\Provider\ProviderResolver $instance */
        $instance = new ProviderResolver();

        $dummyProvider = new DummyConfigurationProvider();
        GeneralUtility::addInstance(DummyConfigurationProvider::class, $dummyProvider);

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
        GeneralUtility::addInstance(Provider::class, $this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock());
        $instance = $this->createInstance();
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
            array(array($this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock())),
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
            array(array(InvalidConfigurationProvider::class)),
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
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(array('getAllRegisteredProviderInstances'))
            ->disableOriginalConstructor()
            ->getMock();
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
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(array('getAllRegisteredProviderInstances'))
            ->disableOriginalConstructor()
            ->getMock();
        $instance->expects($this->once())->method('getAllRegisteredProviderInstances')->willReturn($providers);
        $result = $instance->resolvePrimaryConfigurationProvider('table', 'field');
        $this->assertEquals(array_pop($providers), $result);
    }

    /**
     * @return array
     */
    public function getProviderTestValues()
    {
        $priority50 = $this->getMockBuilder(Provider::class)
            ->setMethods(array('getPriority', 'trigger'))
            ->disableOriginalConstructor()
            ->getMock();
        $priority50->expects($this->atLeastOnce())->method('getPriority')->willReturn(50);
        $priority50->expects($this->atLeastOnce())->method('trigger')->willReturn(true);
        $priority40 = $this->getMockBuilder(Provider::class)
            ->setMethods(array('getPriority', 'trigger'))
            ->disableOriginalConstructor()
            ->getMock();
        $priority40->expects($this->atLeastOnce())->method('getPriority')->willReturn(40);
        $priority40->expects($this->atLeastOnce())->method('trigger')->willReturn(true);
        return array(
            array(array($priority40, $priority50))
        );
    }
}
