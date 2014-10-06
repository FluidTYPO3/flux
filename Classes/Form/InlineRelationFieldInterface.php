<?php
namespace FluidTYPO3\Flux\Form;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

/**
 * @package Flux
 * @subpackage Form
 */
interface InlineRelationFieldInterface extends RelationFieldInterface {

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
