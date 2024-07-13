<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field;
use FluidTYPO3\Flux\Form\FieldContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\ViewHelpers\Field\AbstractFieldViewHelper;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * FlexForm field ViewHelper
 *
 * Defines a single field data structure.
 */
class FieldViewHelper extends AbstractFieldViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('type', 'string', 'TCA field type', true);
        $this->registerArgument('name', 'string', 'Name of the attribute, FlexForm XML-valid tag name string', true);
        $this->registerArgument('label', 'string', 'Label for field');
        $this->registerArgument('description', 'string', 'Field description', false);
        $this->registerArgument('exclude', 'bool', 'Set to FALSE if field is not an "exclude" field', false, false);
        $this->registerArgument('config', 'array', 'TCA "config" array', false, []);
        $this->registerArgument(
            'transform',
            'string',
            'Set this to transform your value to this type - integer, array (for csv values), float, DateTime, ' .
            'Vendor\\MyExt\\Domain\\Model\\Object or ObjectStorage with type hint.'
        );
        $this->registerArgument(
            'onChange',
            'string',
            'TCA onChange instruction',
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
            'destroys the current field value all the way down to the stored XML value.',
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
            'extensionName',
            'string',
            'If provided, enables overriding the extension context for this and all child nodes. The extension name ' .
            'is otherwise automatically detected from rendering context.'
        );
    }

    public static function getComponent(
        RenderingContextInterface $renderingContext,
        iterable $arguments
    ): FieldInterface {
        /** @var array $arguments */
        $parent = static::getContainerFromRenderingContext($renderingContext);
        $field = Field::create($arguments instanceof ArgumentCollection ? $arguments->getArrayCopy() : $arguments);
        $field->setClearable($arguments['clear']);
        $field->setProtectable($arguments['protect']);

        if (!$parent instanceof FieldContainerInterface) {
            return $field;
        }

        $parent->add($field);
        return $field;
    }
}
