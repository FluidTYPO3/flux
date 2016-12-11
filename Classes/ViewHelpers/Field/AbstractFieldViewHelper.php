<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base class for all FlexForm fields.
 */
abstract class AbstractFieldViewHelper extends AbstractFormViewHelper
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
            'Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is ' .
            'the name of the field.'
        );
        $this->registerArgument('default', 'string', 'Default value for this attribute');
        $this->registerArgument(
            'required',
            'boolean',
            'If TRUE, this attribute must be filled when editing the FCE',
            false,
            false
        );
        $this->registerArgument(
            'exclude',
            'boolean',
            'If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)',
            false,
            false
        );
        $this->registerArgument(
            'transform',
            'string',
            'Set this to transform your value to this type - integer, array (for csv values), float, DateTime, ' .
            'Vendor\\MyExt\\Domain\\Model\\Object or ObjectStorage with type hint. '
        );
        $this->registerArgument('enabled', 'boolean', 'If FALSE, disables the field in the FlexForm', false, true);
        $this->registerArgument(
            'requestUpdate',
            'boolean',
            'If TRUE, the form is force-saved and reloaded when field value changes',
            false,
            false
        );
        $this->registerArgument(
            'displayCond',
            'string',
            'Optional "Display Condition" (TCA style) for this particular field. See: ' .
            'https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond'
        );
        $this->registerArgument(
            'inherit',
            'boolean',
            'If TRUE, the value for this particular field is inherited - if inheritance is enabled by ' .
            'the ConfigurationProvider',
            false,
            true
        );
        $this->registerArgument(
            'inheritEmpty',
            'boolean',
            'If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance ' .
            'is enabled by the ConfigurationProvider',
            false,
            true
        );
        $this->registerArgument(
            'clear',
            'boolean',
            'If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely ' .
            'destroys the current field value all the way down to the stored XML value',
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
            'extensionName',
            'string',
            'If provided, enables overriding the extension context for this and all child nodes. The extension name ' .
            'is otherwise automatically detected from rendering context.'
        );
    }

    /**
     * @param string $type
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return FieldInterface
     */
    protected static function getPreparedComponent($type, RenderingContextInterface $renderingContext, array $arguments)
    {
        $component = static::getFormFromRenderingContext($renderingContext)
            ->createField($type, $arguments['name'], $arguments['label']);
        $component->setExtensionName(
            static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments)
        );
        $component->setDefault($arguments['default']);
        $component->setRequired($arguments['required']);
        $component->setExclude($arguments['exclude']);
        $component->setEnable($arguments['enabled']);
        $component->setRequestUpdate($arguments['requestUpdate']);
        $component->setDisplayCondition($arguments['displayCond']);
        $component->setInherit($arguments['inherit']);
        $component->setInheritEmpty($arguments['inheritEmpty']);
        $component->setTransform($arguments['transform']);
        $component->setClearable($arguments['clear']);
        $component->setVariables($arguments['variables']);
        return $component;
    }
}
