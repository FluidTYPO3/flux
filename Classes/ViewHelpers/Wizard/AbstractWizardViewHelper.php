<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\WizardInterface;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base class for Field Wizard style ViewHelpers
 */
abstract class AbstractWizardViewHelper extends AbstractFormViewHelper
{

    /**
     * @var string
     */
    protected $label = null;

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('label', 'string', 'Optional title of this Wizard', false, $this->label);
        $this->registerArgument('hideParent', 'boolean', 'If TRUE, hides the parent field', false, false);
        $this->registerArgument(
            'variables',
            'array',
            'Freestyle variables which become assigned to the resulting Component - can then be read from that ' .
            'Component outside this Fluid template and in other templates using the Form object from this template',
            false,
            []
        );
        $this->registerArgument(
            'extensionName',
            'string',
            'If provided, enables overriding the extension context for this and all child nodes. The extension name ' .
            'is otherwise automatically detected from rendering context.'
        );
    }

    /**
     * @param string $type
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return WizardInterface
     */
    protected static function getPreparedComponent($type, RenderingContextInterface $renderingContext, array $arguments)
    {
        $name = (true === isset($arguments['name']) ? $arguments['name'] : 'wizard');
        $component = static::getContainerFromRenderingContext($renderingContext)->createWizard($type, $name);
        $component->setExtensionName(
            static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments)
        );
        $component->setHideParent($arguments['hideParent']);
        $component->setLabel($arguments['label']);
        $component->setVariables($arguments['variables']);
        return $component;
    }
}
