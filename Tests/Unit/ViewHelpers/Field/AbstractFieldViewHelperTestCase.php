<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Development\ProtectedAccess;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractFormViewHelperTestCase;

/**
 * AbstractFieldViewHelperTestCase
 */
abstract class AbstractFieldViewHelperTestCase extends AbstractFormViewHelperTestCase
{

    /**
     * @test
     */
    public function createsValidFieldInterfaceComponents()
    {
        $instance = $this->buildViewHelperInstance($this->defaultArguments);
        $renderingContext = ProtectedAccess::getProperty($instance, 'renderingContext');
        $component = $instance->getComponent($renderingContext, ProtectedAccess::getProperty($instance, 'arguments'), function () { });
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FieldInterface', $component);
    }
}
