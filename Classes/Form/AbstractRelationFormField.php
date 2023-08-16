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
    protected string $table = '';
    protected ?string $condition = null;

    /**
     * Optional filter - as an [$userFunctionReferenceString, $parameters)
     * - to further condition which records are allowed to be selected in this field.
     */
    protected array $filter = [];

    /**
     * The foreign_field is the field of the child record pointing to the
     * parent record. This defines where to store the uid of the parent record.
     */
    protected ?string $foreignField = null;

    /**
     * The field of the child record pointing to the parent record. This defines
     * where to store the table name of the parent record. On setting this
     * configuration key together with foreign_field, the child record knows what
     * its parent record is – so the child record could also be used on other
     * parent tables.
     */
    protected ?string $foreignTableField = null;
    protected ?string $manyToMany = null;

    /**
     * When using many-to-many mode you can specify an array of field=>value pairs
     * which must also match in the relation table when the relation is resolved.
     *
     * @var array
     */
    protected array $matchFields = [];

    /**
     * If set, it overrides the label set in TCA[foreign_table]['ctrl']['label']
     * for the foreign table view.
     */
    protected ?string $foreignLabel = null;

    /**
     * A selector is used to show all possible child records that could be used
     * to create a relation with the parent record. It will be rendered as a
     * multi-select-box. On clicking on an item inside the selector a new relation
     * is created. The foreign_selector points to a field of the foreign_table that
     * is responsible for providing a selector-box – this field on the foreign_table
     * usually has the type "select" and also has a "foreign_table" defined.
     */
    protected ?string $foreignSelector = null;

    /**
     * Defines a field on the child record (or on the intermediate table) that
     * stores the manual sorting information.
     */
    protected ?string $foreignSortby = null;

    /**
     * If a fieldname for foreign_sortby is defined, then this is ignored. Otherwise
     * this is used as the "ORDER BY" statement to sort the records in the table
     * when listed.
     */
    protected ?string $foreignDefaultSortby = null;

    /**
     * Field which must be uniue for all children of a parent record.
     */
    protected ?string $foreignUnique = null;

    /**
     * In case of bidirectional symmetric relations, this defines the field name on
     * the foreign table which contains the UID of this side of the relation.
     */
    protected ?string $symmetricField = null;

    /**
     * If set, this overrides the default label of the selected symmetric table.
     */
    protected ?string $symmetricLabel = null;

    /**
     * This works like foreign_sortby, but defines the field on foreign_table where
     * the "other" sort order is stored (this order is then used only in the reverse
     * symmetric relation).
     */
    protected ?string $symmetricSortby = null;

    /**
     * Set whether children can be localizable ('select') or just inherit from
     * default language ('keep'). Default is empty, meaning no particular behavior.
     */
    protected ?string $localizationMode = null;

    /**
     * Disables that child records get moved along with their parent records.
     */
    protected bool $disableMovingChildrenWithParent = false;
    protected bool $showThumbnails = false;
    protected ?string $oppositeField = null;

    public function prepareConfiguration(string $type): array
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
        $configuration['disableMovingChildrenWithParent'] =
            (integer) $this->getDisableMovingChildrenWithParent();
        $configuration['showThumbs'] = intval($this->getShowThumbnails());
        $configuration['MM'] = $this->getManyToMany();
        $configuration['MM_match_fields'] = $this->getMatchFields();
        $configuration['MM_opposite_field'] = $this->getOppositeField();
        $configuration['filter'] = $this->getFilter();
        return $configuration;
    }

    public function setCondition(?string $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setFilter(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }

    public function setForeignField(?string $foreignField): self
    {
        $this->foreignField = $foreignField;
        return $this;
    }

    public function getForeignField(): ?string
    {
        return $this->foreignField;
    }

    public function setManyToMany(?string $manyToMany): self
    {
        $this->manyToMany = $manyToMany;
        return $this;
    }

    public function getManyToMany(): ?string
    {
        return $this->manyToMany;
    }

    public function getMatchFields(): array
    {
        return $this->matchFields;
    }

    public function setMatchFields(array $matchFields): self
    {
        $this->matchFields = $matchFields;
        return $this;
    }

    public function getOppositeField(): ?string
    {
        return $this->oppositeField;
    }

    public function setOppositeField(?string $oppositeField): self
    {
        $this->oppositeField = $oppositeField;
        return $this;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setDisableMovingChildrenWithParent(bool $disableMovingChildrenWithParent): self
    {
        $this->disableMovingChildrenWithParent = $disableMovingChildrenWithParent;
        return $this;
    }

    public function getDisableMovingChildrenWithParent(): bool
    {
        return $this->disableMovingChildrenWithParent;
    }

    public function setForeignDefaultSortby(?string $foreignDefaultSortby): self
    {
        $this->foreignDefaultSortby = $foreignDefaultSortby;
        return $this;
    }

    public function getForeignDefaultSortby(): ?string
    {
        return $this->foreignDefaultSortby;
    }

    public function setForeignLabel(?string $foreignLabel): self
    {
        $this->foreignLabel = $foreignLabel;
        return $this;
    }

    public function getForeignLabel(): ?string
    {
        return $this->foreignLabel;
    }

    public function setForeignSelector(?string $foreignSelector): self
    {
        $this->foreignSelector = $foreignSelector;
        return $this;
    }

    public function getForeignSelector(): ?string
    {
        return $this->foreignSelector;
    }

    public function setForeignSortby(?string $foreignSortby): self
    {
        $this->foreignSortby = $foreignSortby;
        return $this;
    }

    public function getForeignSortby(): ?string
    {
        return $this->foreignSortby;
    }

    public function setForeignTableField(?string $foreignTableField): self
    {
        $this->foreignTableField = $foreignTableField;
        return $this;
    }

    public function getForeignTableField(): ?string
    {
        return $this->foreignTableField;
    }

    public function setForeignUnique(?string $foreignUnique): self
    {
        $this->foreignUnique = $foreignUnique;
        return $this;
    }

    public function getForeignUnique(): ?string
    {
        return $this->foreignUnique;
    }

    public function setLocalizationMode(?string $localizationMode): self
    {
        $this->localizationMode = $localizationMode;
        return $this;
    }

    public function getLocalizationMode(): ?string
    {
        return $this->localizationMode;
    }

    public function setSymmetricField(?string $symmetricField): self
    {
        $this->symmetricField = $symmetricField;
        return $this;
    }

    public function getSymmetricField(): ?string
    {
        return $this->symmetricField;
    }

    public function setSymmetricLabel(?string $symmetricLabel): self
    {
        $this->symmetricLabel = $symmetricLabel;
        return $this;
    }

    public function getSymmetricLabel(): ?string
    {
        return $this->symmetricLabel;
    }

    public function setSymmetricSortby(?string $symmetricSortby): self
    {
        $this->symmetricSortby = $symmetricSortby;
        return $this;
    }

    public function getSymmetricSortby(): ?string
    {
        return $this->symmetricSortby;
    }

    public function setShowThumbnails(bool $showThumbnails): self
    {
        $this->showThumbnails = $showThumbnails;
        return $this;
    }

    public function getShowThumbnails(): bool
    {
        return $this->showThumbnails;
    }
}
