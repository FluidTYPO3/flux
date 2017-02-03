<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Public contract for relation-capable fields
 */
interface RelationFieldInterface extends MultiValueFieldInterface
{

    /**
     * @param string $condition
     * @return RelationFieldInterface
     */
    public function setCondition($condition);

    /**
     * @return string
     */
    public function getCondition();

    /**
     * @param string $foreignField
     * @return RelationFieldInterface
     */
    public function setForeignField($foreignField);

    /**
     * @return string
     */
    public function getForeignField();

    /**
     * @param NULL|string $manyToMany
     * @return RelationFieldInterface
     */
    public function setManyToMany($manyToMany);

    /**
     * @return NULL|string
     */
    public function getManyToMany();

    /**
     * @return array
     */
    public function getMatchFields();

    /**
     * @param array $matchFields
     * @return RelationFieldInterface
     */
    public function setMatchFields(array $matchFields);

    /**
     * @param string $table
     * @return RelationFieldInterface
     */
    public function setTable($table);

    /**
     * @return string
     */
    public function getTable();

    /**
     * @param boolean $disableMovingChildrenWithParent
     * @return RelationFieldInterface
     */
    public function setDisableMovingChildrenWithParent($disableMovingChildrenWithParent);

    /**
     * @return boolean
     */
    public function getDisableMovingChildrenWithParent();

    /**
     * @param string $foreignDefaultSortby
     * @return RelationFieldInterface
     */
    public function setForeignDefaultSortby($foreignDefaultSortby);

    /**
     * @return string
     */
    public function getForeignDefaultSortby();

    /**
     * @param string $foreignLabel
     * @return RelationFieldInterface
     */
    public function setForeignLabel($foreignLabel);

    /**
     * @return string
     */
    public function getForeignLabel();

    /**
     * @param string $foreignSelector
     * @return RelationFieldInterface
     */
    public function setForeignSelector($foreignSelector);

    /**
     * @return string
     */
    public function getForeignSelector();

    /**
     * @param string $foreignSortby
     * @return RelationFieldInterface
     */
    public function setForeignSortby($foreignSortby);

    /**
     * @return string
     */
    public function getForeignSortby();

    /**
     * @param string $foreignTableField
     * @return RelationFieldInterface
     */
    public function setForeignTableField($foreignTableField);

    /**
     * @return string
     */
    public function getForeignTableField();

    /**
     * @param string $foreignUnique
     * @return RelationFieldInterface
     */
    public function setForeignUnique($foreignUnique);

    /**
     * @return string
     */
    public function getForeignUnique();

    /**
     * @param string $localizationMode
     * @return RelationFieldInterface
     */
    public function setLocalizationMode($localizationMode);

    /**
     * @return string
     */
    public function getLocalizationMode();

    /**
     * @param boolean $localizeChildrenAtParentLocalization
     * @return RelationFieldInterface
     */
    public function setLocalizeChildrenAtParentLocalization($localizeChildrenAtParentLocalization);

    /**
     * @return boolean
     */
    public function getLocalizeChildrenAtParentLocalization();

    /**
     * @param string $symmetricField
     * @return RelationFieldInterface
     */
    public function setSymmetricField($symmetricField);

    /**
     * @return string
     */
    public function getSymmetricField();

    /**
     * @param string $symmetricLabel
     * @return RelationFieldInterface
     */
    public function setSymmetricLabel($symmetricLabel);

    /**
     * @return string
     */
    public function getSymmetricLabel();

    /**
     * @param string $symmetricSortby
     * @return RelationFieldInterface
     */
    public function setSymmetricSortby($symmetricSortby);

    /**
     * @return string
     */
    public function getSymmetricSortby();

    /**
     * @param boolean $showThumbnails
     * @return RelationFieldInterface
     */
    public function setShowThumbnails($showThumbnails);

    /**
     * @return boolean
     */
    public function getShowThumbnails();

    /**
     * @param boolean|string $emptyOption
     * @return RelationFieldInterface
     */
    public function setEmptyOption($emptyOption);

    /**
     * @return boolean|string
     */
    public function getEmptyOption();

    /**
     * @return string
     */
    public function getOppositeField();

    /**
     * @param string $oppositeField
     * @return RelationFieldInterface
     */
    public function setOppositeField($oppositeField);
}
