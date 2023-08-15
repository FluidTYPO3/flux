<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Conversion;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Conversion\FormToFluidTemplateConverter;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class FormToFluidTemplateConverterTest extends AbstractTestCase
{
    public function testConvertGeneratesExpectedOutput(): void
    {
        $form = Form::create(['id' => 'test-form']);

        $sheet = $form->createContainer(Form\Container\Sheet::class, 'somefields');
        $sheet->createField(Form\Field\Input::class, 'input');
        $sheet->createField(Form\Field\Select::class, 'select');
        $sheet->createField(Form\Field\Checkbox::class, 'checkbox')->setClearable(true);

        $grid = Form\Container\Grid::create();
        $grid->setParent($form);

        $column = $grid->createContainer(Form\Container\Row::class, 'row')
            ->createContainer(Form\Container\Column::class, 'column');
        $column->setColumnPosition(3);

        $subject = new FormToFluidTemplateConverter();
        $output = $subject->convertFormAndGrid($form, $grid, []);

        $expected = trim(file_get_contents(__DIR__ . '/../../../Fixtures/Outputs/GeneratedFluidTemplate.html'));

        self::assertSame($expected, $output);
    }
}
