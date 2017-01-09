<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\ViewHelpers\Field\AbstractFieldViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * FlexForm field section ViewHelper
 *
 * #### Using a section to let a user add many elements
 *
 *     <flux:form.section name="settings.numbers" label="Telephone numbers">
 *         <flux:form.object name="mobile" label="Mobile">
 *             <flux:field.input name="number"/>
 *         </flux:form.object>
 *         <flux:form.object name="landline" label="Landline">
 *             <flux:field.input name="number"/>
 *         </flux:form.object>
 *     </flux:form.section>
 *
 * #### Reading section element values
 *
 *     <f:for each="{settings.numbers}" as="obj" key="id">
 *         Number #{id}:
 *         <f:if condition="{obj.landline}">mobile, {obj.landline.number}</f:if>
 *         <f:if condition="{obj.mobile}">landline, {obj.mobile.number}</f:if>
 *         <br/>
 *     </f:for>
 */
class SectionViewHelper extends AbstractFieldViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the attribute, FlexForm XML-valid tag name string', true);
        $this->registerArgument(
            'label',
            'string',
            'Label for section, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label ' .
            'named "flux.fluxFormId.sections.foobar" based on section name, in scope of extension rendering the form.'
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
            'extensionName',
            'string',
            'If provided, enables overriding the extension context for this and all child nodes. The extension name ' .
            'is otherwise automatically detected from rendering context.'
        );
        $this->registerArgument(
            'inherit',
            'boolean',
            'If TRUE, the value for this particular field is inherited - if inheritance is enabled by ' .
            'the ConfigurationProvider',
            false,
            false
        );
        $this->registerArgument(
            'inheritEmpty',
            'boolean',
            'If TRUE, allows empty values (specifically excluding ' .
            'the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider',
            false,
            false
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Section
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        $container = static::getContainerFromRenderingContext($renderingContext);
        /** @var Section $section */
        $section = $container->createContainer('Section', $arguments['name'], $arguments['label']);
        $section->setExtensionName(
            static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments)
        );
        $section->setVariables($arguments['variables']);
        $section->setInherit($arguments['inherit']);
        $section->setInheritEmpty($arguments['inheritEmpty']);
        return $section;
    }
}
