<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * AbstractPipeTestCase
 */
abstract class AbstractPipeTestCase extends AbstractTestCase
{

    /**
     * @var array
     */
    protected $defaultData = array();

    /**
     * @test
     */
    public function canConductData()
    {
        $instance = $this->createInstance();
        $output = $instance->conduct($this->defaultData);
        $this->assertNotEmpty($output);
    }

    /**
     * @test
     */
    public function canGetLabel()
    {
        $instance = $this->createInstance();
        $label = $instance->getLabel();
        $this->assertNotEmpty($label);
    }

    /**
     * @test
     */
    public function canGetFormFields()
    {
        $fields = $this->createInstance()->getFormFields();
        $this->assertIsArray($fields);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FieldInterface', $fields['class']);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FieldInterface', $fields['label']);
    }

    /**
     * @test
     */
    public function canLoadSettings()
    {
        $instance = $this->createInstance();
        $instance->loadSettings($this->defaultData);
        foreach ($this->defaultData as $propertyName => $propertyValue) {
            $result = ObjectAccess::getProperty($instance, $propertyName, true);
            $this->assertEquals($propertyValue, $result);
        }
    }
}
