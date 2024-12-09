<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Input;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Input FlexForm field ViewHelper
 */
class InputViewHelper extends AbstractFieldViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'eval',
            'string',
            'FlexForm-type validation configuration for this input',
            false,
            Input::DEFAULT_VALIDATE
        );
        $this->registerArgument('size', 'integer', 'Size of field', false, 32);
        $this->registerArgument('maxCharacters', 'integer', 'Maximum number of characters allowed', false);
        $this->registerArgument('minimum', 'integer', 'Minimum value for integer type fields', false);
        $this->registerArgument('maximum', 'integer', 'Maximum value for integer type fields', false);
        $this->registerArgument(
            'placeholder',
            'string',
            'Placeholder text which vanishes if field is filled and/or field is focused'
        );
    }

    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments): Input
    {
        /** @var array $arguments */
        /** @var Input $input */
        $input = static::getPreparedComponent(Input::class, $renderingContext, $arguments);
        $input->setValidate($arguments['eval']);
        $input->setMaxCharacters($arguments['maxCharacters']);
        $input->setMinimum($arguments['minimum']);
        $input->setMaximum($arguments['maximum']);
        $input->setPlaceholder(is_scalar($arguments['placeholder']) ? (string) $arguments['placeholder'] : null);
        $input->setSize($arguments['size']);
        return $input;
    }
}
