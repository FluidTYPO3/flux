<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * AbstractRelationFormField
 */
abstract class AbstractRelationFormField extends AbstractMultiValueFormField implements RelationFieldInterface
{

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $condition;

    /**
     * Optional filter - as an [$userFunctionReferenceString, $parameters)
     * - to further condition which records are allowed to be selected in this field.
     *
     * @var array
     */
    protected $filter = [];

    /**
     * The foreign_field is the field of the child record pointing to the
     * parent record. This defines where to store the uid of the parent record.
     *
     * @var string
     */
    protected $foreignField;

    /**
     * The field of the child record pointing to the parent record. This defines
     * where to store the table name of the parent record. On setting this
     * configuration key together with foreign_field, the child record knows what
     * its parent record is – so the child record could also be used on other
     * parent tables.
     *
     * @var string
     */
    protected $foreignTableField;

    /**
     * @var string|NULL
     */
    protected $manyToMany = null;

    /**
     * When using many-to-many mode you can specify an array of field=>value pairs
     * which must also match in the relation table when the relation is resolved.
     *
     * @var array
     */
    protected $matchFields = [];

    /**
     * If set, it overrides the label set in TCA[foreign_table]['ctrl']['label']
     * for the foreign table view.
     *
     * @var string
     */
    protected $foreignLabel;

    /**
     * A selector is used to show all possible child records that could be used
     * to create a relation with the parent record. It will be rendered as a
     * multi-select-box. On clicking on an item inside the selector a new relation
     * is created. The foreign_selector points to a field of the foreign_table that
     * is responsible for providing a selector-box – this field on the foreign_table
     * usually has the type "select" and also has a "foreign_table" defined.
     *
     * @var string
     */
    protected $foreignSelector;

    /**
     * Defines a field on the child record (or on the intermediate table) that
     * stores the manual sorting information.
     *
     * @var string
     */
    protected $foreignSortby;

    /**
     * If a fieldname for foreign_sortby is defined, then this is ignored. Otherwise
     * this is used as the "ORDER BY" statement to sort the records in the table
     * when listed.
     *
     * @var string
     */
    protected $foreignDefaultSortby;

    /**
     * Field which must be uniue for all children of a parent record.
     *
     * @var string
     */
    protected $foreignUnique;

    /**
     * In case of bidirectional symmetric relations, this defines the field name on
     * the foreign table which contains the UID of this side of the relation.
     *
     * @var string
     */
    protected $symmetricField;

    /**
     * If set, this overrides the default label of the selected symmetric table.
     *
     * @var string
     */
    protected $symmetricLabel;

    /**
     * This works like foreign_sortby, but defines the field on foreign_table where
     * the "other" sort order is stored (this order is then used only in the reverse
     * symmetric relation).
     *
     * @var string
     */
    protected $symmetricSortby;

    /**
     * Set whether children can be localizable ('select') or just inherit from
     * default language ('keep'). Default is empty, meaning no particular behavior.
     *
     * @var string
     */
    protected $localizationMode;

    /**
     * Defines whether children should be localized when the localization of the
     * parent gets created.
     *
     * @var boolean
     */
    protected $localizeChildrenAtParentLocalization = false;

    /**
     * Disables that child records get moved along with their parent records.
     *
     * @var boolean
     */
    protected $disableMovingChildrenWithParent = false;

    /**
     * @var boolean
     */
    protected $showThumbnails = false;

    /**
     * @var string
     */
    protected $oppositeField;

    /**
     * @param string $type
     * @return array
     */
    public function prepareConfiguration($type)
    {
        $configuration = parent::prepareConfiguration($type);
        $configuration['foreign_table'] = $this->getTable();
        $configuration['foreign_field'] = $this->getForeignField();
        $configuration['foreign_table_where'] = $this->getCondition();
        $configuration['foreign_table_field'] = $this->getForeignTableField();
        $configuration['foreign_unique'] = $this->getForeignUnique();
        $configuration['foreign_label'] = $this->getForeignLabel();
        $configuration['foreign_selector'] = $this->getForeignSelector();
        $configuration['foreign_sortby'] = $this->getForeignSortby();
        $configuration['foreign_default_sortby'] = $this->getForeignDefaultSortby();
        $configuration['symmetricSortBy'] = $this->getSymmetricSortby();
        $configuration['symmetricLabel'] = $this->getSymmetricLabel();
        $configuration['symmetricField'] = $this->getSymmetricField();
        $configuration['localizationMode'] = $this->getLocalizationMode();
        $configuration['localizeChildrenAtParentLocalization'] =
            (integer) $this->getLocalizeChildrenAtParentLocalization();
        $configuration['disableMovingChildrenWithParent'] =
            (integer) $this->getDisableMovingChildrenWithParent();
        $configuration['showThumbs'] = intval($this->getShowThumbnails());
        $configuration['MM'] = $this->getManyToMany();
        $configuration['MM_match_fields'] = $this->getMatchFields();
        $configuration['MM_opposite_field'] = $this->getOppositeField();
        $configuration['filter'] = $this->getFilter();
        return $configuration;
    }

    /**
     * @param string $condition
     * @return RelationFieldInterface
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param array $filter
     * @return RelationFieldInterface
     */
    public function setFilter(array $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param string $foreignField
     * @return RelationFieldInterface
     */
    public function setForeignField($foreignField)
    {
        $this->foreignField = $foreignField;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignField()
    {
        return $this->foreignField;
    }

    /**
     * @param NULL|string $manyToMany
     * @return RelationFieldInterface
     */
    public function setManyToMany($manyToMany)
    {
        $this->manyToMany = $manyToMany;
        return $this;
    }

    /**
     * @return NULL|string
     */
    public function getManyToMany()
    {
        return $this->manyToMany;
    }

    /**
     * @return array
     */
    public function getMatchFields()
    {
        return $this->matchFields;
    }

    /**
     * @param array $matchFields
     * @return RelationFieldInterface
     */
    public function setMatchFields(array $matchFields)
    {
        $this->matchFields = $matchFields;
        return $this;
    }

    /**
     * @return string
     */
    public function getOppositeField()
    {
        return $this->oppositeField;
    }

    /**
     * @param string $oppositeField
     * @return RelationFieldInterface
     */
    public function setOppositeField($oppositeField)
    {
        $this->oppositeField = $oppositeField;
        return $this;
    }

    /**
     * @param string $table
     * @return RelationFieldInterface
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param boolean $disableMovingChildrenWithParent
     * @return RelationFieldInterface
     */
    public function setDisableMovingChildrenWithParent($disableMovingChildrenWithParent)
    {
        $this->disableMovingChildrenWithParent = $disableMovingChildrenWithParent;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisableMovingChildrenWithParent()
    {
        return $this->disableMovingChildrenWithParent;
    }

    /**
     * @param string $foreignDefaultSortby
     * @return RelationFieldInterface
     */
    public function setForeignDefaultSortby($foreignDefaultSortby)
    {
        $this->foreignDefaultSortby = $foreignDefaultSortby;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignDefaultSortby()
    {
        return $this->foreignDefaultSortby;
    }

    /**
     * @param string $foreignLabel
     * @return RelationFieldInterface
     */
    public function setForeignLabel($foreignLabel)
    {
        $this->foreignLabel = $foreignLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignLabel()
    {
        return $this->foreignLabel;
    }

    /**
     * @param string $foreignSelector
     * @return RelationFieldInterface
     */
    public function setForeignSelector($foreignSelector)
    {
        $this->foreignSelector = $foreignSelector;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignSelector()
    {
        return $this->foreignSelector;
    }

    /**
     * @param string $foreignSortby
     * @return RelationFieldInterface
     */
    public function setForeignSortby($foreignSortby)
    {
        $this->foreignSortby = $foreignSortby;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignSortby()
    {
        return $this->foreignSortby;
    }

    /**
     * @param string $foreignTableField
     * @return RelationFieldInterface
     */
    public function setForeignTableField($foreignTableField)
    {
        $this->foreignTableField = $foreignTableField;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignTableField()
    {
        return $this->foreignTableField;
    }

    /**
     * @param string $foreignUnique
     * @return RelationFieldInterface
     */
    public function setForeignUnique($foreignUnique)
    {
        $this->foreignUnique = $foreignUnique;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignUnique()
    {
        return $this->foreignUnique;
    }

    /**
     * @param string $itemListStyle
     * @return RelationFieldInterface
     */
    public function setItemListStyle($itemListStyle)
    {
        $this->itemListStyle = $itemListStyle;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemListStyle()
    {
        return $this->itemListStyle;
    }

    /**
     * @param string $localizationMode
     * @return RelationFieldInterface
     */
    public function setLocalizationMode($localizationMode)
    {
        $this->localizationMode = $localizationMode;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalizationMode()
    {
        return $this->localizationMode;
    }

    /**
     * @param boolean $localizeChildrenAtParentLocalization
     * @return RelationFieldInterface
     */
    public function setLocalizeChildrenAtParentLocalization($localizeChildrenAtParentLocalization)
    {
        $this->localizeChildrenAtParentLocalization = $localizeChildrenAtParentLocalization;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getLocalizeChildrenAtParentLocalization()
    {
        return $this->localizeChildrenAtParentLocalization;
    }

    /**
     * @param string $selectedListStyle
     * @return  RelationFieldInterface
     */
    public function setSelectedListStyle($selectedListStyle)
    {
        $this->selectedListStyle = $selectedListStyle;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelectedListStyle()
    {
        return $this->selectedListStyle;
    }

    /**
     * @param string $symmetricField
     * @return RelationFieldInterface
     */
    public function setSymmetricField($symmetricField)
    {
        $this->symmetricField = $symmetricField;
        return $this;
    }

    /**
     * @return string
     */
    public function getSymmetricField()
    {
        return $this->symmetricField;
    }

    /**
     * @param string $symmetricLabel
     * @return RelationFieldInterface
     */
    public function setSymmetricLabel($symmetricLabel)
    {
        $this->symmetricLabel = $symmetricLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getSymmetricLabel()
    {
        return $this->symmetricLabel;
    }

    /**
     * @param string $symmetricSortby
     * @return RelationFieldInterface
     */
    public function setSymmetricSortby($symmetricSortby)
    {
        $this->symmetricSortby = $symmetricSortby;
        return $this;
    }

    /**
     * @return string
     */
    public function getSymmetricSortby()
    {
        return $this->symmetricSortby;
    }

    /**
     * @param boolean $showThumbnails
     * @return RelationFieldInterface
     */
    public function setShowThumbnails($showThumbnails)
    {
        $this->showThumbnails = $showThumbnails;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getShowThumbnails()
    {
        return $this->showThumbnails;
    }

    /**
     * @param boolean|string $emptyOption
     * @return RelationFieldInterface
     */
    public function setEmptyOption($emptyOption)
    {
        $this->emptyOption = $emptyOption;
        return $this;
    }

    /**
     * @return boolean|string
     */
    public function getEmptyOption()
    {
        return $this->emptyOption;
    }
}
