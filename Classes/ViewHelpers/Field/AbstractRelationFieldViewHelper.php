<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\RelationFieldInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base class for all FlexForm fields.
 */
abstract class AbstractRelationFieldViewHelper extends AbstractMultiValueFieldViewHelper
{

    /**
     * Initialize arguments
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'table',
            'string',
            'Define foreign table name to turn selector into a record selector for that table'
        );
        $this->registerArgument(
            'condition',
            'string',
            'Condition to use when selecting from "foreignTable", supports FlexForm "foregin_table_where" markers'
        );
        $this->registerArgument('mm', 'string', 'Optional name of MM table to use for record selection');
        $this->registerArgument(
            'foreignField',
            'string',
            'The foreign_field is the field of the child record pointing to the parent record. This defines where to ' .
            'store the uid of the parent record.',
            false,
            ''
        );
        $this->registerArgument(
            'foreignLabel',
            'string',
            "If set, it overrides the label set in TCA[foreign_table]['ctrl']['label'] for the inline-view.",
            false,
            ''
        );
        $this->registerArgument(
            'foreignSelector',
            'string',
            'A selector is used to show all possible child records that could be used to create a relation with ' .
            'the parent record. It will be rendered as a multi-select-box. On clicking on an item inside the ' .
            'selector a new relation is created. The foreign_selector points to a field of the foreign_table ' .
            'that is responsible for providing a selector-box – this field on the foreign_table usually has the ' .
            'type "select" and also has a "foreign_table" defined.'
        );
        $this->registerArgument(
            'foreignSortby',
            'string',
            'Field on the child record (or on the intermediate table) that stores the manual sorting information.',
            false,
            ''
        );
        $this->registerArgument(
            'foreignDefaultSortby',
            'string',
            'If a fieldname for foreign_sortby is defined, then this is ignored. Otherwise this is used as the ' .
            '"ORDER BY" statement to sort the records in the table when listed.',
            false,
            ''
        );
        $this->registerArgument(
            'foreignTableField',
            'string',
            'The field of the child record pointing to the parent record. This defines where to store the table ' .
            'name of the parent record. On setting this configuration key together with foreign_field, the child ' .
            'record knows what its parent record is - so the child record could also be used on other parent tables.',
            false,
            ''
        );
        $this->registerArgument(
            'foreignUnique',
            'string',
            'Field which must be uniue for all children of a parent record.'
        );
        $this->registerArgument(
            'symmetricField',
            'string',
            'In case of bidirectional symmetric relations, this defines in which field on the foreign table the ' .
            'uid of the "other" parent is stored.',
            false,
            ''
        );
        $this->registerArgument(
            'symmetricLabel',
            'string',
            'If set, this overrides the default label of the selected symmetric_field.',
            false,
            ''
        );
        $this->registerArgument(
            'symmetricSortby',
            'string',
            'Works like foreign_sortby, but defines the field on foreign_table where the "other" sort order is stored.',
            false,
            ''
        );
        $this->registerArgument(
            'localizationMode',
            'string',
            "Set whether children can be localizable ('select') or just inherit from default language ('keep').",
            false,
            ''
        );
        $this->registerArgument(
            'localizeChildrenAtParentLocalization',
            'boolean',
            'Defines whether children should be localized when the localization of the parent gets created.',
            false,
            false
        );
        $this->registerArgument(
            'disableMovingChildrenWithParent',
            'boolean',
            'Disables that child records get moved along with their parent records.',
            false,
            false
        );
        $this->registerArgument(
            'showThumbs',
            'boolean',
            'If TRUE, adds thumbnail display when editing in BE',
            false,
            true
        );
        $this->registerArgument(
            'matchFields',
            'array',
            'When using manyToMany you can provide an additional array of field=>value pairs that must match in ' .
            'the relation table',
            false,
            []
        );
        $this->registerArgument(
            'oppositeField',
            'string',
            'Name of the opposite field related to a proper mm relation',
            false,
            ''
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return RelationFieldInterface
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        return static::getPreparedComponent('Relation', $renderingContext, $arguments);
    }

    /**
     * @param string $type
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return RelationFieldInterface
     */
    protected static function getPreparedComponent($type, RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var RelationFieldInterface $component */
        $component = parent::getPreparedComponent($type, $renderingContext, $arguments);
        $component->setTable($arguments['table']);
        $component->setCondition($arguments['condition']);
        $component->setManyToMany($arguments['mm']);
        $component->setForeignField($arguments['foreignField']);
        $component->setForeignSelector($arguments['foreignSelector']);
        $component->setForeignLabel($arguments['foreignLabel']);
        $component->setForeignSortby($arguments['foreignSortby']);
        $component->setForeignDefaultSortby($arguments['foreignDefaultSortby']);
        $component->setForeignTableField($arguments['foreignTableField']);
        $component->setForeignUnique($arguments['foreignUnique']);
        $component->setSymmetricField($arguments['symmetricField']);
        $component->setSymmetricLabel($arguments['symmetricLabel']);
        $component->setSymmetricSortby($arguments['symmetricSortby']);
        $component->setLocalizationMode($arguments['localizationMode']);
        $component->setLocalizeChildrenAtParentLocalization($arguments['localizeChildrenAtParentLocalization']);
        $component->setDisableMovingChildrenWithParent($arguments['disableMovingChildrenWithParent']);
        $component->setShowThumbnails($arguments['showThumbs']);
        $component->setMatchFields((array) $arguments['matchFields']);
        $component->setOppositeField($arguments['oppositeField']);
        $component->setEmptyOption($arguments['emptyOption']);
        return $component;
    }
}
