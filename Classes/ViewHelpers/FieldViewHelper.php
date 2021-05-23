<?php
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field;
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
    public function initializeArguments()
    {
        $this->registerArgument('type', 'string', 'TCA field type', true);
        $this->registerArgument('name', 'string', 'Name of the attribute, FlexForm XML-valid tag name string', true);
        $this->registerArgument('label', 'string', 'Label for field', true);
        $this->registerArgument('exclude', 'bool', 'Set to FALSE if field is not an "exclude" field', false, false);
        $this->registerArgument('config', 'array', 'TCA "config" array', false, []);
        $this->registerArgument(
            'transform',
            'string',
            'Set this to transform your value to this type - integer, array (for csv values), float, DateTime, ' .
            'Vendor\\MyExt\\Domain\\Model\\Object or ObjectStorage with type hint. '
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
            'extensionName',
            'string',
            'If provided, enables overriding the extension context for this and all child nodes. The extension name ' .
            'is otherwise automatically detected from rendering context.'
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param iterable $arguments
     * @return FieldInterface
     */
    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments)
    {
        $parent = static::getContainerFromRenderingContext($renderingContext);
        $field = Field::create($arguments instanceof ArgumentCollection ? $arguments->getArrayCopy() : $arguments);
        $parent->add($field);
        return $field;
    }
}
