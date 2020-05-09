<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Select;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Field Wizard: Edit
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by arguments.
 *
 * DEPRECATED - use flux:field with custom "config" with renderMode and/or fieldWizard attributes
 * @deprecated Will be removed in Flux 10.0
 */
class SelectViewHelper extends AbstractWizardViewHelper
{

    /**
     * Initialize arguments
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'mode',
            'string',
            'Selection mode - substitution, append or prepend',
            false,
            'substitution'
        );
        $this->registerArgument(
            'items',
            'mixed',
            'Comma-separated, comma-and-semicolon-separated or array list of possible values',
            true
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param iterable $arguments
     * @return Select
     */
    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments)
    {
        /** @var Select $component */
        $component = static::getPreparedComponent('Select', $renderingContext, $arguments);
        $component->setMode($arguments['mode']);
        $component->setItems($arguments['items']);
        return $component;
    }
}
