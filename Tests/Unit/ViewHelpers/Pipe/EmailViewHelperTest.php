<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use FluidTYPO3\Flux\ViewHelpers\Pipe\AbstractPipeViewHelper;
use FluidTYPO3\Flux\ViewHelpers\Pipe\EmailViewHelper;

class EmailViewHelperTest extends AbstractViewHelperTestCase
{
    public function testReadsBodyFromTagContent(): void
    {
        $form = Form::create();
        $closure = function () { return 'Body'; };
        $this->viewHelperVariableContainer->addOrUpdate(
            AbstractFormViewHelper::SCOPE,
            AbstractFormViewHelper::SCOPE_VARIABLE_FORM,
            $form
        );
        $output = EmailViewHelper::renderStatic(
            ['body' => '', 'subject' => 'test', 'direction' => AbstractPipeViewHelper::DIRECTION_OUT],
            $closure,
            $this->renderingContext
        );
        self::assertSame('', $output);
    }

    /**
     * @dataProvider getTestArguments
     * @param array $arguments
     */
    public function testWithArguments(array $arguments)
    {
        $result = $this->executeViewHelper($arguments, array(), null, null, 'FakePlugin');
        $this->assertSame('', $result);
    }

    public function getTestArguments(): array
    {
        return array(
            array(array()),
        );
    }
}
