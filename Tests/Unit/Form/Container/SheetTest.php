<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;

class SheetTest extends AbstractContainerTest
{
    public function testDescriptionPropertyWorks(): void
    {
        $this->assertGetterAndSetterWorks('description', 'foobardescription', 'foobardescription', true);
    }

    public function testShortDescriptionPropertyWorks(): void
    {
        $this->assertGetterAndSetterWorks('shortDescription', 'foobarshortdescription', 'foobarshortdescription', true);
    }

    public function testAddTogglesTransformOnIfChildHasTransformProperty(): void
    {
        $form = Form::create();
        $sheet = $form->createContainer(Form\Container\Sheet::class, 'sheet');
        $child = new Form\Field\Input();
        $child->setTransform('string');
        self::assertFalse($form->hasOption(FormOption::TRANSFORM));
        $sheet->add($child);
        self::assertTrue($form->hasOption(FormOption::TRANSFORM));
    }

    /**
     * @test
     */
    public function modifyCreatesFields(): void
    {
        $form = $this->getMockBuilder(Form::class)->addMethods(['dummy'])->getMock();
        $sheet = $form->createContainer('Sheet', 'testsheet');
        $form->modify(
            ['fields' => ['test' => ['name' => 'test', 'label' => 'Test', 'type' => Form\Field\Input::class]]]
        );
        $fields  = $sheet->getFields();
        $this->assertArrayHasKey('test', $fields);
    }

    /**
     * @test
     */
    public function modifyModifiesFields(): void
    {
        $form = $this->getMockBuilder(Form::class)->addMethods(['dummy'])->getMock();
        $sheet = $form->createContainer('Sheet', 'testsheet');
        $field = $sheet->createField('Input', 'testfield', 'Testfield');
        $sheet->modify(['fields' => ['testfield' => ['label' => 'Test']]]);
        $fields = $sheet->getFields();
        $this->assertEquals('Test', reset($fields)->getLabel());
    }
}
