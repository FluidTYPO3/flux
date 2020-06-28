<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\UserFunction;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Flexform Userfunc field ViewHelper
 *
 * DEPRECATED - use flux:field instead
 * @deprecated Will be removed in Flux 10.0
 */
class UserFuncViewHelper extends AbstractFieldViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'userFunc',
            'string',
            'UserFunc to be called, example "MyExt\\MyVendor\\MySpecialClass->renderField". Ignored on TYPO3 9.5 and above - use renderType instead.'
        );
        $this->registerArgument(
            'renderType',
            'string',
            'Render type (TCA renderType) - required on TYPO3 9.5 and above. Render type must be registered as FormEngine node type. '
            . 'See https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/FormEngine/Rendering/Index.html'
        );
        $this->registerArgument(
            'arguments',
            'array',
            'Optional array of arguments to pass to the UserFunction building this field'
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param iterable $arguments
     * @param \Closure $renderChildrenClosure
     * @return UserFunction
     */
    public static function getComponent(
        RenderingContextInterface $renderingContext,
        iterable $arguments,
        \Closure $renderChildrenClosure
    ) {
        /** @var UserFunction $user */
        $user = static::getPreparedComponent('UserFunction', $renderingContext, $arguments);
        $user->setFunction($arguments['userFunc']);
        $user->setRenderType($arguments['renderType'] ?? '');
        $user->setArguments($arguments['arguments']);
        return $user;
    }
}
