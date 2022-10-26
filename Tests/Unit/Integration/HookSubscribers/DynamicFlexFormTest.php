<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\HookSubscribers\DynamicFlexForm;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * DynamicFlexFormTest
 */
class DynamicFlexFormTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testReturnsEmptyDataStructureIdentifierForNonMatchingTableAndField()
    {
        $fluxService = $this->getMockBuilder(FluxService::class)->setMethods(['resolvePrimaryConfigurationProvider'])->getMock();
        $fluxService->method('resolvePrimaryConfigurationProvider')->willReturn(null);

        $subject = $this->getMockBuilder(DynamicFlexForm::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $subject->injectConfigurationService($fluxService);

        $result = $subject->getDataStructureIdentifierPreProcess(['foo' => 'bar'], 'sometable', 'somefield', ['uid' => 123]);
        $this->assertSame([], $result);
    }

    /**
     * @param array $identifier
     * @dataProvider getEmptyDataStructureIdentifierTestValues
     */
    public function testReturnsEmptyDataStructureForIdentifier(array $identifier)
    {
        $subject = $this->getMockBuilder(DynamicFlexForm::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $result = $subject->parseDataStructureByIdentifierPreProcess($identifier);
        $this->assertSame([], $result);
    }

    /**
     * @return array
     */
    public function getEmptyDataStructureIdentifierTestValues()
    {
        return [
            [
                ['type' => 'unsupported']
            ],
            [
                ['type' => 'flux', 'record' => null]
            ],
        ];
    }
}
