<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Section;

/**
 * SectionTest
 */
class SectionTest extends AbstractContainerTest
{

    /**
     * @test
     */
    public function canCreateFromDefinitionWithObjects()
    {
        $definition = array(
            'name' => 'test',
            'label' => 'Test section',
            'objects' => array(
                'object1' => array(
                    'label' => 'Test object',
                    'fields' => array(
                        'foo' => array(
                            'type' => 'Input',
                            'label' => 'Foo input'
                        )
                    )
                )
            )
        );
        $section = Section::create($definition);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Section', $section);
    }
}
