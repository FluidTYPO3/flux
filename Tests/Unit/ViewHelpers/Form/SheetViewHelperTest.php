<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractFormViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

class SheetViewHelperTest extends AbstractFormViewHelperTestCase
{
    public function testUsesExistingSheet(): void
    {
        $form = Form::create();
        $sheet = $this->getMockBuilder(Form\Container\Sheet::class)
            ->setMethods(['setExtensionName', 'setVariables', 'setDescription', 'setShortDescription'])
            ->disableOriginalConstructor()
            ->getMock();
        $sheet->setName('test');

        $form->add($sheet);

        $this->viewHelperVariableContainer->add(
            AbstractFormViewHelper::SCOPE,
            AbstractFormViewHelper::SCOPE_VARIABLE_FORM,
            $form
        );

        $arguments = [
            'name' => 'test',
            'extensionName' => 'test-ext',
            'variables' => ['foo' => 'bar'],
            'description' => 'test-description',
            'shortDescription' => 'test-shortdescription',
        ];

        $sheet->expects(self::once())->method('setExtensionName')->with($arguments['extensionName']);
        $sheet->expects(self::once())->method('setVariables')->with($arguments['variables']);
        $sheet->expects(self::once())->method('setDescription')->with($arguments['description']);
        $sheet->expects(self::once())->method('setShortDescription')->with($arguments['shortDescription']);

        $this->executeViewHelper($arguments);
    }
}
