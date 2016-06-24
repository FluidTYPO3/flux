<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Tests\Unit\Form\AbstractFormTest;

/**
 * AbstractContainerTest
 */
abstract class AbstractContainerTest extends AbstractFormTest
{

    /**
     * @var array
     */
    protected $chainProperties = array('name' => 'test', 'label' => 'Test field', 'transform' => 'string');

    /**
     * @return ContainerInterface
     */
    protected function createInstance()
    {
        $className = $this->getObjectClassName();
        $instance = $this->objectManager->get($className);
        return $instance;
    }

    /**
     * @test
     */
    public function returnsFalseIfChildObjectNameDoesNotExist()
    {
        $instance = $this->createInstance();
        $result = $instance->get('doesNotExist');
        $this->assertSame(false, $result);
    }

    /**
     * @test
     */
    public function canGetAndSetInheritEmpty()
    {
        $instance = $this->createInstance();
        $instance->setInheritEmpty(true);
        $this->assertEquals(true, $instance->getInheritEmpty());
    }

    /**
     * @test
     */
    public function canGetAndSetInherit()
    {
        $instance = $this->createInstance();
        $instance->setInherit(false);
        $this->assertEquals(false, $instance->getInherit());
    }

    /**
     * @test
     */
    public function returnsFalseIfChildObjectNameDoesNotExistRecursively()
    {
        $instance = $this->createInstance();
        $subContainer = $instance->createContainer('Container', 'testcontainer');
        $subField = $instance->createField('Input', 'test');
        $subContainer->add($subField);
        $instance->add($subContainer);
        $result = $instance->get('doesNotExist', true);
        $this->assertSame(false, $result);
    }

    /**
     * @test
     */
    public function canCreateFromDefinitionContainingFields()
    {
        $properties = array($this->chainProperties);
        $properties['fields'] = array(
            'foo' => array(
                'type' => 'Input'
            ),
            'bar' => array(
                'type' => 'Input'
            ),
        );
        $instance = call_user_func_array(array($this->getObjectClassName(), 'create'), array($properties));
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
    }
}
