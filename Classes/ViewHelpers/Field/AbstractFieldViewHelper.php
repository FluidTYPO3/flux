<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base class for all FlexForm fields.
 */
abstract class AbstractFieldViewHelper extends AbstractFormViewHelper
{
    public function initializeArguments(): void
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
            'native',
            'boolean',
            'If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" ' .
            'of this field is an already existing field, that original field will be replaced by this field. If the ' .
            'field is a new field (which doesn\'t already exist in TCA). You can control where this field visually ' .
            'appears in the editing form by specifying the "position" argument, which supports the same syntax as ' .
            '\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). ' .
            'Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm ' .
            'where Flux fields are normally rendered.',
            false,
            false
        );
        $this->registerArgument(
            'position',
            'string',
            'Only applies if native=1. Specify where in the editing form this field should be, using the syntax of ' .
            '\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). ' .
            'Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated ' .
            'sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"'
        );
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
            'protect',
            'boolean',
            'If TRUE, a "protect value" checkbox is displayed next to the field which when checked, protects the ' .
            'value from being changed if the (normally inherited) field value is changed in a parent record. Has no ' .
            'effect if "inherit" is disabled on the field.',
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
        $this->registerArgument(
            'config',
            'array',
            'Raw TCA options - passed directly to "config" section of created field and overrides anything generated ' .
            'by the component itself. Can be used to provide options that Flux itself does not support, and can be ' .
            'used to pass root-level arguments for a "userFunc"',
            false,
            []
        );
    }

    /**
     * @template T
     * @param class-string<T> $type
     * @return T&FormInterface
     */
    protected static function getPreparedComponent(
        $type,
        RenderingContextInterface $renderingContext,
        iterable $arguments
    ): FormInterface {
        /** @var array $arguments */
        $component = static::getContainerFromRenderingContext($renderingContext)
            ->createField($type, $arguments['name'], $arguments['label']);
        $component->setConfig((array)$arguments['config']);
        $component->setExtensionName(
            static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments)
        );
        $component->setDefault($arguments['default']);
        $component->setRequired($arguments['required']);
        $component->setExclude($arguments['exclude']);
        $component->setEnabled($arguments['enabled']);
        $component->setRequestUpdate($arguments['requestUpdate']);
        $component->setDisplayCondition($arguments['displayCond']);
        $component->setInherit($arguments['inherit']);
        $component->setInheritEmpty($arguments['inheritEmpty']);
        $component->setTransform($arguments['transform']);
        $component->setClearable($arguments['clear']);
        $component->setProtectable($arguments['protect']);
        $component->setVariables($arguments['variables']);
        $component->setNative($arguments['native']);
        $component->setPosition($arguments['position']);
        return $component;
    }
}
