<?php
namespace FluidTYPO3\Flux\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\OutletArgument;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * ViewHelper to define Outlet arguments
 *
 * Use `<flux:outlet.argument>` in conjunction with the `<flux:outlet>` and `<flux.outlet.validate>` viewHelpers.
 * You can define any number of arguments including validations that will be applied to the outlet action.
 * To call the outlet action use the action "outlet" in your form action.
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
 *         <f:form action="outlet" noCache="1">
 *             <f:form.textfield name="name" value="{name}" />
 *         </f:form>
 *     </f:section>
 */
class ArgumentViewHelper extends AbstractFormViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'name of the argument', true);
        $this->registerArgument('type', 'string', 'type of the argument', false, 'string');
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
        $outlet = static::getFormFromRenderingContext($renderingContext)->getOutlet();
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $argument = $objectManager->get(OutletArgument::class, $arguments['name'], $arguments['type']);

        $viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
        $viewHelperVariableContainer->addOrUpdate(ValidateViewHelper::class, 'validators', []);
        $renderChildrenClosure();
        $validators = $viewHelperVariableContainer->get(ValidateViewHelper::class, 'validators');
        foreach ($validators as $validator) {
            $argument->addValidator($validator['type'], (array)$validator['options']);
        }
        $outlet->addArgument($argument);
    }
}
