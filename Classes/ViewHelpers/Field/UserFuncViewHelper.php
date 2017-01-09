<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\UserFunction;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Flexform Userfunc field ViewHelper
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
            'UserFunc to be called, example "MyExt\\MyVendor\\MySpecialClass->renderField"',
            true
        );
        $this->registerArgument(
            'arguments',
            'array',
            'Optional array of arguments to pass to the UserFunction building this field'
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @return UserFunction
     */
    public static function getComponent(
        RenderingContextInterface $renderingContext,
        array $arguments,
        \Closure $renderChildrenClosure
    ) {
        /** @var UserFunction $user */
        $user = static::getPreparedComponent('UserFunction', $renderingContext, $arguments);
        $user->setFunction($arguments['userFunc']);
        $user->setArguments($arguments['arguments']);
        return $user;
    }
}
