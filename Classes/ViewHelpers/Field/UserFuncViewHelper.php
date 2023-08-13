<?php
declare(strict_types=1);
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
 */
class UserFuncViewHelper extends AbstractFieldViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'userFunc',
            'string',
            'UserFunc to be called, example "MyExt\\MyVendor\\MySpecialClass->renderField". Ignored on TYPO3 9.5 '
            . 'and above - use renderType instead.'
        );
        $this->registerArgument(
            'renderType',
            'string',
            'Render type (TCA renderType) - required on TYPO3 9.5 and above. Render type must be registered as '
            . 'FormEngine node type. See '
            // @codingStandardsIgnoreStart
            . 'https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/FormEngine/Rendering/Index.html'
            // @codingStandardsIgnoreEnd
        );
        $this->registerArgument(
            'arguments',
            'array',
            'Optional array of arguments to pass to the UserFunction building this field'
        );
    }

    public static function getComponent(
        RenderingContextInterface $renderingContext,
        iterable $arguments
    ): UserFunction {
        /** @var array $arguments */
        /** @var UserFunction $user */
        $user = static::getPreparedComponent(UserFunction::class, $renderingContext, $arguments);
        $user->setFunction((string) $arguments['userFunc']);
        $user->setRenderType($arguments['renderType'] ?? '');
        $user->setArguments($arguments['arguments'] ?? []);
        return $user;
    }
}
