<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractFormViewHelperTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

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
        $component = $instance->getComponent(ObjectAccess::getProperty($instance, 'renderingContext', true), $this->defaultArguments, function () {
        });
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FieldInterface', $component);
    }
}
