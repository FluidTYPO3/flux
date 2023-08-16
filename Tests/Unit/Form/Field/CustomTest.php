<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

class CustomTest extends AbstractFieldTest
{
    protected array $chainProperties = [
        'name' => 'test',
        'label' => 'Test field',
        'arguments' => [
            'foo' => 'bar'
        ]
    ];

    /**
     * @test
     */
    public function canUseClosure()
    {
        $self = $this;
        $arguments = [
            'closure' => function ($parameters) use ($self) {
                return 'Hello world';
            }
        ];
        $instance = $this->canChainAllChainableSetters($arguments);
        $closure = $instance->getClosure();
        $this->assertSame($arguments['closure'], $closure);
        $output = $closure($arguments);
        $this->assertNotEmpty($output);
    }
}
