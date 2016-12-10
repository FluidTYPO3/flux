<?php
namespace FluidTYPO3\Flux\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * ViewHelper to validate Outlet arguments
 *
 * Use `<flux:outlet.validate>` inside the `<flux.outlet.argument>` viewHelper.
 * You can add any number of validations to the arguments. After submission
 * validation errors will be available inside the validationResults variable.
 *
 * ### Example
 *
 *     <f:section name="Configuration">
 *          <flux:outlet>
 *               <flux:outlet.argument name="name">
 *                    <flux:outlet.validate type="NotEmpty" />
 *               </flux:outlet.argument>
 *          </flux:outlet>
 *     </f:section>
 *
 *     <f:section name="Main">
 *         <f:form noCache="1">
 *             <f:form.textfield name="name" value="{name}" />
 *             <f:if condition="{validationResults.name}">
 *                 <f:for each="{validationResults.name}" as="error">
 *                     <span class="error">{error.code}: {error.message}</span>
 *                 </f:for>
 *             </f:if>
 *         </f:form>
 *     </f:section>
 */
class ValidateViewHelper extends AbstractFormViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('type', 'string', 'validator to apply', true);
        $this->registerArgument('options', 'array', 'additional validator arguments');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();

        $validators = (array) $viewHelperVariableContainer->get(ValidateViewHelper::class, 'validators');
        $validators[] = $arguments;
        $viewHelperVariableContainer->addOrUpdate(ValidateViewHelper::class, 'validators', $validators);
    }
}
