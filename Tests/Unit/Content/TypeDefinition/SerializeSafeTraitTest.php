<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content\TypeDefinition;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\SerializeSafeTrait;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class SerializeSafeTraitTest extends AbstractTestCase
{
    public function testReturnsPropertiesFromSleepMethod(): void
    {
        $subject = $this->getMockBuilder(SerializeSafeTrait::class)->getMockForTrait();
        $subject->expects(self::once())->method('getForm');
        $subject->expects(self::once())->method('getGrid');

        $subject->__sleep();
    }
}
