<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;

class ColumnNumberUtilityTest extends AbstractTestCase
{
    public function testCalculateLocalColumnNumber(): void
    {
        self::assertSame(
            5,
            ColumnNumberUtility::calculateLocalColumnNumber(12305)
        );
    }

    public function testCalculateParentUid(): void
    {
        self::assertSame(
            123,
            ColumnNumberUtility::calculateParentUid(12305)
        );
    }

    public function testCalculateParentUidAndColumnFromVirtualColumnNumber(): void
    {
        self::assertSame(
            [123, 5],
            ColumnNumberUtility::calculateParentUidAndColumnFromVirtualColumnNumber(12305)
        );
    }

    public function testCalculateMinimumAndMaximumColumnNumberWithinParent(): void
    {
        self::assertSame(
            [12300, 12399],
            ColumnNumberUtility::calculateMinimumAndMaximumColumnNumberWithinParent(123)
        );
    }
}
