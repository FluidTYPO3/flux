<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * InlineRelationFieldInterface
 */
interface InlineRelationFieldInterface extends RelationFieldInterface
{

    /**
     * @param mixed $foreignTypes
     * @return InlineRelationFieldInterface
     */
    public function setForeignTypes($foreignTypes);

    /**
     * @return array
     */
    public function getForeignTypes();

    /**
     * @param boolean $collapseAll
     * @return InlineRelationFieldInterface
     */
    public function setCollapseAll($collapseAll);

    /**
     * @return boolean
     */
    public function getCollapseAll();

    /**
     * @param array $enabledControls
     * @return InlineRelationFieldInterface
     */
    public function setEnabledControls(array $enabledControls);

    /**
     * @return array
     */
    public function getEnabledControls();

    /**
     * @param boolean $expandSingle
     * @return InlineRelationFieldInterface
     */
    public function setExpandSingle($expandSingle);

    /**
     * @return boolean
     */
    public function getExpandSingle();

    /**
     * @param boolean $newRecordLinkAddTitle
     * @return InlineRelationFieldInterface
     */
    public function setNewRecordLinkAddTitle($newRecordLinkAddTitle);

    /**
     * @return boolean
     */
    public function getNewRecordLinkAddTitle();

    /**
     * @param string $newRecordLinkPosition
     * @return InlineRelationFieldInterface
     */
    public function setNewRecordLinkPosition($newRecordLinkPosition);

    /**
     * @return string
     */
    public function getNewRecordLinkPosition();

    /**
     * @param boolean $showAllLocalizationLink
     * @return InlineRelationFieldInterface
     */
    public function setShowAllLocalizationLink($showAllLocalizationLink);

    /**
     * @return boolean
     */
    public function getShowAllLocalizationLink();

    /**
     * @param boolean $showPossibleLocalizationRecords
     * @return InlineRelationFieldInterface
     */
    public function setShowPossibleLocalizationRecords($showPossibleLocalizationRecords);

    /**
     * @return boolean
     */
    public function getShowPossibleLocalizationRecords();

    /**
     * @param boolean $showRemovedLocalizationRecords
     * @return InlineRelationFieldInterface
     */
    public function setShowRemovedLocalizationRecords($showRemovedLocalizationRecords);

    /**
     * @return boolean
     */
    public function getShowRemovedLocalizationRecords();

    /**
     * @param boolean $showSynchronizationLink
     * @return InlineRelationFieldInterface
     */
    public function setShowSynchronizationLink($showSynchronizationLink);

    /**
     * @return boolean
     */
    public function getShowSynchronizationLink();

    /**
     * @param boolean $useCombination
     * @return InlineRelationFieldInterface
     */
    public function setUseCombination($useCombination);

    /**
     * @return boolean
     */
    public function getUseCombination();

    /**
     * @param boolean $useSortable
     * @return InlineRelationFieldInterface
     */
    public function setUseSortable($useSortable);
    /**
     * @return boolean
     */
    public function getUseSortable();

    /**
     * @param array $foreignMatchFields
     * @return InlineRelationFieldInterface
     */
    public function setForeignMatchFields(array $foreignMatchFields);

    /**
     * @return array
     */
    public function getForeignMatchFields();
}
