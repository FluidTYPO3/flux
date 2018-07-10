<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\HookSubscribers\ContentUsedDecision;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;

class ContentUsedDecisionTest extends AbstractTestCase
{
    /**
     * @param array $parameters
     * @param bool $expectedDecision
     * @dataProvider getDecisionTestValues
     */
    public function testDecision(array $parameters, bool $expectedDecision)
    {
        $instance = new ContentUsedDecision();
        $this->assertSame($expectedDecision, $instance->isContentElementUsed($parameters));
    }

    public function getDecisionTestValues(): array
    {
        return [
            'true if already marked used' => [
                [
                    'used' => true,
                    'record' => ['colPos' => 0]
                ],
                true
            ],
            'false if colPos below cutoff and not already marked used' => [
                [
                    'used' => false,
                    'record' => ['colPos' => 0]
                ],
                false
            ],
            'true if colPos above cutoff and not already marked used' => [
                [
                    'used' => false,
                    'record' => ['colPos' => ColumnNumberUtility::MULTIPLIER + 1]
                ],
                true
            ],
        ];
    }
}
