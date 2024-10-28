<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Overrides\BackendLayoutView;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class BackendLayoutViewTest extends AbstractTestCase
{
    private ProviderResolver $providerResolver;

    protected function setUp(): void
    {
        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->singletonInstances[ProviderResolver::class] = $this->providerResolver;

        parent::setUp();
    }

    public function testCanSetProvider()
    {
        $instance = $this->getMockBuilder(BackendLayoutView::class)
            ->addMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider = $this->getMockBuilder(GridProviderInterface::class)->getMockForAbstractClass();
        $instance->setProvider($provider);
        $this->assertSame($provider, $this->getInaccessiblePropertyValue($instance, 'provider'));
    }

    public function testCanSetRecord()
    {
        $instance = $this->getMockBuilder(BackendLayoutView::class)
            ->addMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $record = ['foo' => 'bar'];
        $instance->setRecord($record);
        $this->assertSame($record, $this->getInaccessiblePropertyValue($instance, 'record'));
    }
}
