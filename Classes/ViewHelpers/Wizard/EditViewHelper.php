<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Edit;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Field Wizard: Edit
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by arguments.
 */
class EditViewHelper extends AbstractWizardViewHelper
{

    /**
     * Initialize arguments
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('width', 'integer', 'Width of the popup window', false, 580);
        $this->registerArgument('height', 'integer', 'height of the popup window', false, 580);
        $this->registerArgument(
            'openOnlyIfSelected',
            'boolean',
            'Only open the edit dialog if an item is selected',
            false,
            true
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Edit
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var Edit $component */
        $component = static::getPreparedComponent('Edit', $renderingContext, $arguments);
        $component->setOpenOnlyIfSelected($arguments['openOnlyIfSelected']);
        $component->setHeight($arguments['height']);
        $component->setWidth($arguments['width']);
        return $component;
    }
}
