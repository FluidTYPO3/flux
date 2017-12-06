<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\SectionObject;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * FlexForm field section object ViewHelper
 *
 * Use this inside flux:form.section to name and divide the fields
 * into individual objects that can be inserted into the section.
 */
class ObjectViewHelper extends AbstractFormViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'name',
            'string',
            'Name of the section object, FlexForm XML-valid tag name string',
            true
        );
        $this->registerArgument(
            'label',
            'string',
            'Label for section object, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL ' .
            'label named "flux.fluxFormId.objects.foobar" based on object name, in scope of extension rendering ' .
            'the Flux form.'
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
            'If provided, enables overriding the extension context for this and all child nodes. The extension ' .
            'name is otherwise automatically detected from rendering context.'
        );
        $this->registerArgument(
            'inherit',
            'boolean',
            'If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ' .
            'ConfigurationProvider',
            false,
            false
        );
        $this->registerArgument(
            'inheritEmpty',
            'boolean',
            'If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if ' .
            'inheritance is enabled by the ConfigurationProvider',
            false,
            false
        );
        $this->registerArgument(
            'transform',
            'string',
            'Set this to transform your value to this type - integer, array (for csv values), float, DateTime, ' .
            'Vendor\\MyExt\\Domain\\Model\\Object or ObjectStorage with type hint. '
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return SectionObject
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var SectionObject $object */
        $object = static::getFormFromRenderingContext($renderingContext)
            ->createContainer('SectionObject', $arguments['name'], $arguments['label']);
        $object->setExtensionName(
            static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments)
        );
        $object->setVariables($arguments['variables']);
        $object->setInherit($arguments['inherit']);
        $object->setInheritEmpty($arguments['inheritEmpty']);
        $object->setTransform($arguments['transform']);
        return $object;
    }
}
