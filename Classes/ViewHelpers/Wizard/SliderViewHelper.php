<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Slider;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Field Wizard: Slider
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by arguments.
 *
 * DEPRECATED - use flux:field with custom "config" with renderMode and/or fieldWizard attributes
 * @deprecated Will be removed in Flux 10.0
 */
class SliderViewHelper extends AbstractWizardViewHelper
{
    protected ?string $label = 'Slider';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('step', 'integer', 'Increment slider values by this much for each step', false, 1);
        $this->registerArgument('width', 'integer', 'Width of the popup window', false, 32);
    }

    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments): Slider
    {
        /** @var array $arguments */
        /** @var Slider $component */
        $component = static::getPreparedComponent(Slider::class, $renderingContext, $arguments);
        $component->setWidth($arguments['width']);
        $component->setStep($arguments['step']);
        return $component;
    }
}
