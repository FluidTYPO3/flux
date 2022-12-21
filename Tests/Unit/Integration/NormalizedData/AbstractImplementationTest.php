<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\NormalizedData\Converter;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\NormalizedData\AbstractImplementation;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class AbstractImplementationTest extends AbstractTestCase
{
    public function testAppliesToRecord(): void
    {
        $subject = $this->getMockBuilder(AbstractImplementation::class)
            ->setConstructorArgs([])
            ->getMockForAbstractClass();
        self::assertTrue($subject->appliesToRecord(['uid' => 123]));
    }
}
