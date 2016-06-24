<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;

/**
 * SheetTest
 */
class SheetTest extends AbstractContainerTest
{

    /**
     * @test
     */
    public function testDescriptionPropertyWorks()
    {
        $this->assertGetterAndSetterWorks('description', 'foobardescription', 'foobardescription', true);
    }

    /**
     * @test
     */
    public function testShortDescriptionPropertyWorks()
    {
        $this->assertGetterAndSetterWorks('shortDescription', 'foobarshortdescription', 'foobarshortdescription', true);
    }

    /**
     * @test
     */
    public function modifyCreatesFields()
    {
        $form = Form::create();
        $sheet = $form->createContainer('Sheet', 'testsheet');
        $form->modify(array('fields' => array('test' => array('name' => 'test', 'label' => 'Test', 'type' => 'Input'))));
        $fields  = $sheet->getFields();
        $this->assertArrayHasKey('test', $fields);
    }

    /**
     * @test
     */
    public function modifyModifiesFields()
    {
        $form = Form::create();
        $sheet = $form->createContainer('Sheet', 'testsheet');
        $field = $sheet->createField('Input', 'testfield', 'Testfield');
        $sheet->modify(array('fields' => array('testfield' => array('label' => 'Test'))));
        $fields = $sheet->getFields();
        $this->assertEquals('Test', reset($fields)->getLabel());
    }
}
