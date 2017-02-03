<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Input;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Input FlexForm field ViewHelper
 */
class InputViewHelper extends AbstractFieldViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
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

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Input
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var Input $input */
        $input = static::getPreparedComponent('Input', $renderingContext, $arguments);
        $input->setValidate($arguments['eval']);
        $input->setMaxCharacters($arguments['maxCharacters']);
        $input->setMinimum($arguments['minimum']);
        $input->setMaximum($arguments['maximum']);
        $input->setPlaceholder($arguments['placeholder']);
        $input->setSize($arguments['size']);
        return $input;
    }
}
