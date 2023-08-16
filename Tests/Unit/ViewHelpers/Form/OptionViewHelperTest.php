<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractFormViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use FluidTYPO3\Flux\ViewHelpers\Form\OptionViewHelper;

class OptionViewHelperTest extends AbstractFormViewHelperTestCase
{
    public function testThrowsExceptionIfContainerIsNotOptionCarrying(): void
    {
        $this->viewHelperVariableContainer->add(
            AbstractFormViewHelper::SCOPE,
            AbstractFormViewHelper::SCOPE_VARIABLE_CONTAINER,
            $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass()
        );
        $this->expectExceptionCode(1602693000);
        OptionViewHelper::renderStatic(
            ['value' => 'test'],
            function () {
            },
            $this->renderingContext
        );
    }
}
