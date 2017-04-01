<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\FormEngine\ProviderProcessor;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * Class ProviderProcessorTest
 */
class ProviderProcessorTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testGetProviderResolver()
    {
        $instance = new ProviderProcessor();
        $result = $this->callInaccessibleMethod($instance, 'getProviderResolver');
        $this->assertInstanceOf(ProviderResolver::class, $result);
    }

    /**
     * @test
     */
    public function testCallsProcessTableConfigurationOnProviders()
    {
        $provider = $this->getMockBuilder(Provider::class)->setMethods(['processTableConfiguration'])->getMock();
        $provider->expects($this->once())->method('processTableConfiguration')->willReturn('test');

        $providerResolver = $this->getMockBuilder(ProviderResolver::class)->setMethods(['resolveConfigurationProviders'])->getMock();
        $providerResolver->expects($this->once())->method('resolveConfigurationProviders')->willReturn([$provider]);

        $instance = $this->getMockBuilder(ProviderProcessor::class)->setMethods(['getProviderResolver'])->getMock();
        $instance->expects($this->once())->method('getProviderResolver')->willReturn($providerResolver);

        $result = $instance->addData(['table' => 'foo', 'databaseRow' => []]);
        $this->assertEquals('test', $result);

    }
}
