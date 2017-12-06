<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\SectionObject;

/**
 * ObjectTest
 */
class SectionObjectTest extends AbstractContainerTest
{

    /**
     * @test
     */
    public function getFieldsGetsFields()
    {
        $container = SectionObject::create(array('name' => 'test'));
        $container->createField('Input', 'test');
        $this->assertCount(1, $container->getFields());
    }
}
