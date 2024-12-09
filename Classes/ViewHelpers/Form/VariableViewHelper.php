<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Sets an option in the Form instance
 */
class VariableViewHelper extends AbstractFormViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'Value of the option');
        $this->registerArgument(
            'name',
            'string',
            'Name of the option - valid values and their behaviours depend entirely on the consumer that will ' .
            'handle the Form instance',
            true
        );
    }

    public function render()
    {
        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        /** @var string $variableName */
        $variableName = $arguments['name'];
        static::getContainerFromRenderingContext($renderingContext)
            ->setVariable($variableName, $arguments['value'] ?? $renderChildrenClosure());
        return '';
    }

    public function getContentArgumentName(): ?string
    {
        return 'value';
    }
}
