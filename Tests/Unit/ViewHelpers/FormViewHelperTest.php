<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;

class FormViewHelperTest extends AbstractViewHelperTestCase
{
    protected array $defaultArguments = [
        'icon' => 'foobar',
    ];

    public function testCreatesFormInstance(): void
    {
        $this->executeViewHelper($this->defaultArguments);

        self::assertInstanceOf(
            Form::class,
            $this->viewHelperVariableContainer->get(FormViewHelper::SCOPE, FormViewHelper::SCOPE_VARIABLE_FORM)
        );
    }
}
