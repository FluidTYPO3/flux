<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\WizardInterface;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractFormViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\Wizard\AbstractWizardViewHelper;

abstract class AbstractWizardViewHelperTestCase extends AbstractFormViewHelperTestCase
{
    /**
     * @test
     */
    public function createsValidFieldInterfaceComponents()
    {
        /** @var AbstractWizardViewHelper $instance */
        $instance = $this->buildViewHelperInstance($this->defaultArguments);
        $this->renderingContext->getViewHelperInvoker()->invoke($instance, [], $this->renderingContext);
        $component = $instance->getComponent(
            $this->renderingContext,
            $this->buildViewHelperArguments($instance, $this->defaultArguments)
        );
        $this->assertInstanceOf(WizardInterface::class, $component);
    }
}
