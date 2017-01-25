<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ContentProvider;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\InvalidConfigurationProvider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * ProviderResolverTest
 */
class ProviderResolverTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function loadTypoScriptProvidersReturnsEmptyArrayEarlyIfSetupNotFound()
    {
        /** @var \FluidTYPO3\Flux\Provider\ProviderResolver $instance */
        $instance = $this->objectManager->get('FluidTYPO3\Flux\Provider\ProviderResolver');
        $configurationService = $this->getMockBuilder('FluidTYPO3\Flux\Service\FluxService')->setMethods(array('getTypoScriptByPath'))->getMock();
        $configurationService->expects($this->once())->method('getTypoScriptByPath')->will($this->returnValue(array()));
        $objectManager = $this->getMockBuilder('TYPO3\CMS\Extbase\Object\ObjectManager')->setMethods(array('get'))->getMock();
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
        $instance = $this->objectManager->get('FluidTYPO3\Flux\Provider\ProviderResolver');
        $configurationService = $this->getMockBuilder('FluidTYPO3\Flux\Service\FluxService')->setMethods(array('getTypoScriptByPath'))->getMock();
        $objectManager = $this->getMockBuilder('TYPO3\CMS\Extbase\Object\ObjectManager')->setMethods(array('get'))->getMock();
        $mockedTypoScript = array(
            'dummy.' => array(
                'className' => 'FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider'
            )
        );
        $dummyProvider = $this->objectManager->get('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider');
        $configurationService->expects($this->once())->method('getTypoScriptByPath')->will($this->returnValue($mockedTypoScript));
        $objectManager->expects($this->once())->method('get')->with('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider')->will($this->returnValue($dummyProvider));
        $instance->injectConfigurationService($configurationService);
        $instance->injectObjectManager($objectManager);
        $providers = $instance->loadTypoScriptConfigurationProviderInstances();
        $this->assertIsArray($providers);
        $this->assertNotEmpty($providers);
        $this->assertContains($dummyProvider, $providers);
        $this->assertInstanceOf('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider', reset($providers));
    }

    /**
     * @test
     * @dataProvider getValidateAndInstantiateProvidersTestValues
     * @param array $providers
     */
    public function validateAndInstantiateProvidersCreatesInstances(array $providers)
    {
        $instance = $this->createInstance();
        $instance->injectObjectManager($this->objectManager);
        $result = $this->callInaccessibleMethod($instance, 'validateAndInstantiateProviders', $providers);
        $this->assertSameSize($providers, $result);
        foreach ($result as $provider) {
            $this->assertInstanceOf('FluidTYPO3\\Flux\\Provider\\ProviderInterface', $provider);
        }
    }

    /**
     * @return array
     */
    public function getValidateAndInstantiateProvidersTestValues()
    {
        return array(
            array(array()),
            array(array('FluidTYPO3\\Flux\\Provider\\Provider')),
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
        $this->setExpectedException('RuntimeException');
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
