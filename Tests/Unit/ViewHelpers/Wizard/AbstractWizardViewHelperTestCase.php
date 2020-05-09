<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractFormViewHelperTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * AbstractWizardViewHelperTestCase
 */
abstract class AbstractWizardViewHelperTestCase extends AbstractFormViewHelperTestCase
{

    /**
     * @test
     */
    public function createsValidFieldInterfaceComponents()
    {
        $instance = $this->buildViewHelperInstance($this->defaultArguments);
        if (method_exists($instance, 'initializeArgumentsAndRender')) {
            $instance->initializeArgumentsAndRender();
        } elseif (method_exists($instance, 'render')) {
            $instance->render();
        } elseif (method_exists($instance, 'evaluate')) {
            $instance->evaluate(new RenderingContext());
        }
        $component = $instance->getComponent(
            ObjectAccess::getProperty($instance, 'renderingContext', true),
            ObjectAccess::getProperty($instance, 'arguments', true)
        );
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\WizardInterface', $component);
    }
}
