<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\Outlet\ValidateViewHelper;

class ValidateViewHelperTest extends AbstractViewHelperTestCase
{
    public function testAddsArgumentsAsValidatorConfiguration()
    {
        $arguments = ['name' => 'test', 'type' => 'NotEmpty'];
        ValidateViewHelper::renderStatic($arguments, function () {
        }, $this->renderingContext);
        $this->assertSame([$arguments], $this->viewHelperVariableContainer->get(ValidateViewHelper::class, 'validators'));
    }
}
