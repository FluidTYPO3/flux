<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Container;
use FluidTYPO3\Flux\ViewHelpers\Field\AbstractFieldViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * ### FlexForm Field Container element
 *
 * Use around other Flux fields to make these fields nested visually
 * and in variable scopes (i.e. a field called "name" inside a palette
 * called "person" would end up with "person" being an array containing
 * the "name" property, rendered as {person.name} in Fluid.
 *
 * The field grouping can be hidden or completely removed. In this regard
 * this element is a simpler version of the Section and Object logic.
 *
 * #### Grouping elements with a container
 *
 *     <flux:form.container name="settings.name" label="Name">
 *         <flux:field.input name="firstname" label="First name"/>
 *         <flux:field.input name="lastname" label="Last name"/>
 *     </flux:form.container>
 *
 * #### Accessing values of grouped elements
 *
 *     Name: {settings.name.firstname} {settings.name.lastname}
 */
class ContainerViewHelper extends AbstractFieldViewHelper
{

    /**
     * Initialize arguments
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the attribute, FlexForm XML-valid tag name string', true);
        $this->registerArgument(
            'label',
            'string',
            'Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL ' .
            'label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the ' .
            'Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" ' .
            'is the name of the field.'
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
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Container
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var Container $container */
        $container = static::getFormFromRenderingContext($renderingContext)
            ->createContainer('Container', $arguments['name'], $arguments['label']);
        $container->setExtensionName(
            static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments)
        );
        $container->setVariables($arguments['variables']);
        return $container;
    }
}
