<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;

/**
 * FileTest
 */
class FileTest extends AbstractFieldTest
{

    /**
     * @var array
     */
    protected $chainProperties = array(
        'name' => 'test',
        'label' => 'Test field',
        'enable' => true,
        'maxSize' => 135153542,
        'allowed' => 'jpg,gif',
        'disallowed' => 'doc,docx',
        'uploadFolder' => '',
        'showThumbnails' => true
    );

    /**
     * @test
     */
    public function canSetDefaultValueFromSimpleString()
    {
        $instance = Form::create(array())->createField('File', 'file');
        $defaultValue = 'testfile.jpg';
        $instance->setDefault($defaultValue);
        $this->assertSame($defaultValue . '|' . $defaultValue, $instance->getDefault());
    }

    /**
     * @test
     */
    public function canSetDefaultValueFromAlreadyCorrectString()
    {
        $instance = Form::create(array())->createField('File', 'file');
        $defaultValue = 'testfile.jpg|testfile.jpg';
        $instance->setDefault($defaultValue);
        $this->assertSame($defaultValue, $instance->getDefault());
    }

    /**
     * @test
     */
    public function canSetDefaultValueFromCsvOfSimpleStrings()
    {
        $instance = Form::create(array())->createField('File', 'file');
        $defaultValue = 'testfile1.jpg,testfile2.jpg';
        $expected = 'testfile1.jpg|testfile1.jpg,testfile2.jpg|testfile2.jpg';
        $instance->setDefault($defaultValue);
        $this->assertSame($expected, $instance->getDefault());
    }

    /**
     * @test
     */
    public function canSetDefaultValueFromCsvfAlreadyCorrectStrings()
    {
        $instance = Form::create(array())->createField('File', 'file');
        $defaultValue = 'testfile1.jpg|testfile1.jpg,testfile2.jpg|testfile2.jpg';
        $instance->setDefault($defaultValue);
        $this->assertSame($defaultValue, $instance->getDefault());
    }
}
