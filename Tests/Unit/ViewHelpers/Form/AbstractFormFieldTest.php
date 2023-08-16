<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormField;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class AbstractFormFieldTest extends AbstractTestCase
{
    public function testBuildWithDisplayCondition(): void
    {
        $subject = $this->getMockBuilder(AbstractFormField::class)->getMockForAbstractClass();
        $subject->setDisplayCondition('condition');
        $output = $subject->build();
        self::assertSame('condition', $output['displayCond']);
    }
}
