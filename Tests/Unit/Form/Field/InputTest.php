<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

class InputTest extends AbstractFieldTest
{
    protected array $chainProperties = [
        'name' => 'test',
        'label' => 'Test field',
        'enabled' => true,
        'maxCharacters' => 30,
        'maximum' => 10,
        'minimum' => 0,
        'validate' => 'trim,int',
        'default' => 'test',
        'requestUpdate' => true,
    ];

    /**
     * @test
     */
    public function canUseRequiredProperty()
    {
        $instance = $this->canChainAllChainableSetters();
        $instance->setRequired(true);
        $this->assertEquals('trim,int,required', $instance->getValidate());
    }

    /**
     * @test
     */
    public function canUseRequiredPropertyThroughValidateProperty()
    {
        $instance = $this->canChainAllChainableSetters();
        $instance->setValidate(null);
        $instance->setRequired(true);
        $this->assertEquals('required', $instance->getValidate());
    }
}
