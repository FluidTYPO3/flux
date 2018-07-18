<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ContextUtility;
use TYPO3\CMS\Core\Core\ApplicationContext;

/**
 * ContextUtilityTest
 */
class ContextUtilityTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function canGetContext()
    {
        $context = ContextUtility::getApplicationContext();
        $this->assertInstanceOf(ApplicationContext::class, $context);
    }
}
