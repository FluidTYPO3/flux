<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\VersionUtility;

/**
 * VersionUtilityTest
 */
class VersionUtilityTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function canGetExtensionVersionNumbers()
    {
        $version = VersionUtility::assertExtensionVersionIsAtLeastVersion('flux', 6, 0, 0);
        $this->assertIsBoolean($version);
    }

    /**
     * @test
     */
    public function returnsFalseIfExtensionKeyIsNotLoaded()
    {
        $isFalseResponse = VersionUtility::assertExtensionVersionIsAtLeastVersion('void', 1, 0, 0);
        $this->assertFalse($isFalseResponse);
    }
}
