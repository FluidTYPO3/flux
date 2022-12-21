<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\WizardInterface;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base class for Field Wizard style ViewHelpers
 *
 * @deprecated Will be removed in Flux 10.0
 */
abstract class AbstractWizardViewHelper extends AbstractFormViewHelper
{
    protected ?string $label = null;

    public function initializeArguments(): void
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

    protected static function getPreparedComponent(
        string $type,
        RenderingContextInterface $renderingContext,
        iterable $arguments
    ): WizardInterface {
        /** @var class-string $type */
        /** @var array $arguments */
        $name = (true === isset($arguments['name']) ? $arguments['name'] : 'wizard');
        /** @var WizardInterface $component */
        $component = static::getContainerFromRenderingContext($renderingContext)->createWizard($type, $name);
        $component->setExtensionName(
            static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments)
        );
        $component->setHideParent($arguments['hideParent'] ?? false);
        $component->setLabel($arguments['label'] ?? null);
        $component->setVariables($arguments['variables'] ?? []);
        return $component;
    }
}
