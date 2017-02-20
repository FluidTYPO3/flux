<?php
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\FluxPackage;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Package\FluxPackageFactory;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * FlexForm configuration container ViewHelper
 */
class FormViewHelper extends AbstractFormViewHelper
{

    /**
     * Initialize arguments
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'id',
            'string',
            'Identifier of this Flexible Content Element, `/[a-z0-9]/i` allowed',
            true
        );
        $this->registerArgument(
            'label',
            'string',
            'Label for the form, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label ' .
            'named "flux.fluxFormId", in scope of extension rendering the Flux form.'
        );
        $this->registerArgument('description', 'string', 'Short description of the purpose/function of this form');
        $this->registerArgument(
            'enabled',
            'boolean',
            'If FALSE, features which use this form can elect to skip it. Respect for this flag depends on the ' .
            'feature using the form.',
            false,
            true
        );
        $this->registerArgument(
            'compact',
            'boolean',
            'If TRUE, disables sheet usage in the form. WARNING! AVOID DYNAMIC VALUES AT ALL COSTS! Toggling this ' .
            'option is DESTRUCTIVE to variables currently saved in the database!',
            false,
            false
        );
        $this->registerArgument(
            'variables',
            'array',
            'Freestyle variables which become assigned to the resulting Component - can then be read from that ' .
            'Component outside this Fluid template and in other templates using the Form object from this template',
            false,
            []
        );
        $this->registerArgument(
            'options',
            'array',
            'Custom options to be assigned to Form object - valid values depends on the. See docs of extension ' .
            'in which you use this feature. Can also be set using `flux:form.option` as child of `flux:form`.'
        );
        $this->registerArgument(
            'localLanguageFileRelativePath',
            'string',
            'Relative (from extension) path to locallang file containing labels for the LLL values used in this form.',
            false,
            Form::DEFAULT_LANGUAGEFILE
        );
        $this->registerArgument(
            'extensionName',
            'string',
            'If provided, enables overriding the extension context for this and all child nodes. The extension name ' .
            'is otherwise automatically detected from rendering context.'
        );
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
        $templateVariableContainer = $renderingContext->getTemplateVariableContainer();
        $extensionName = static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments);
        $formClassName = FluxPackageFactory::getPackageWithFallback($extensionName)
            ->getImplementation(FluxPackage::IMPLEMENTATION_FORM);
        $form = call_user_func_array([$formClassName, 'create'], []);
        // configure Form instance
        $form->setId($arguments['id']);
        $form->setName($arguments['id']);
        $form->setLabel($arguments['label']);
        $form->setDescription($arguments['description']);
        $form->setEnabled($arguments['enabled']);
        $form->setCompact($arguments['compact']);
        $form->setExtensionName($extensionName);
        $form->setLocalLanguageFileRelativePath($arguments['localLanguageFileRelativePath']);
        $form->setVariables((array) $arguments['variables']);
        $form->setOptions((array) $arguments['options']);
        if (false === $form->hasOption(Form::OPTION_ICON)) {
            $form->setOption(Form::OPTION_ICON, $arguments['icon']);
        }
        if (false === $form->hasOption(Form::OPTION_GROUP)) {
            $form->setOption(Form::OPTION_GROUP, $arguments['wizardTab']);
        }

        // rendering child nodes with Form's last sheet as active container
        $viewHelperVariableContainer->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_FORM, $form);
        $viewHelperVariableContainer->addOrUpdate(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME, $extensionName);
        $templateVariableContainer->add(static::SCOPE_VARIABLE_FORM, $form);

        static::setContainerInRenderingContext($renderingContext, $form);
        static::setExtensionNameInRenderingContext(
            $renderingContext,
            static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments)
        );
        $renderChildrenClosure();

        $viewHelperVariableContainer->remove(static::SCOPE, static::SCOPE_VARIABLE_EXTENSIONNAME);
        $viewHelperVariableContainer->remove(static::SCOPE, static::SCOPE_VARIABLE_CONTAINER);
        $templateVariableContainer->remove(static::SCOPE_VARIABLE_CONTAINER);
    }
}
