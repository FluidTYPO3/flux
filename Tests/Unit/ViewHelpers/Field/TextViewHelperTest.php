<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * TextViewHelperTest
 */
class TextViewHelperTest extends AbstractFieldViewHelperTestCase
{

    /**
     * @test
     */
    public function supportsPlaceholders()
    {
        $arguments = ['placeholder' => 'test'];
        $instance = $this->buildViewHelperInstance($arguments);
        $component = $instance->getComponent(
            $this->getInaccessiblePropertyValue($instance, 'renderingContext'),
            $this->getInaccessiblePropertyValue($instance, 'arguments')
        );
        $this->assertSame($arguments['placeholder'], $component->getPlaceholder());
    }
}
