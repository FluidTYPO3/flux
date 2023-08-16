<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Container;

class ContainerTest extends AbstractContainerTest
{
    /**
     * @test
     */
    public function getFieldsGetsFields(): void
    {
        $container = Container::create(['name' => 'test']);
        $container->createField('Input', 'test');
        $this->assertCount(1, $container->getFields());
    }

    /**
     * @test
     */
    public function ifObjectIsFieldContainerItSupportsFetchingFields(): void
    {
        $instance = $this->createInstance();
        $field = $instance->createField('Input', 'test');
        $instance->add($field);
        $fields = $instance->getFields();
        $this->assertNotEmpty(
            $fields,
            'The class ' .
            $this->getObjectClassName() .
            ' does not appear to support the required FieldContainerInterface implementation'
        );
    }
}
