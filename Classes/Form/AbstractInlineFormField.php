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

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Field\Inline;

/**
 * @package Flux
 * @subpackage Form\Field
 */
abstract class AbstractInlineFormField extends AbstractRelationFormField {

	/**
	 * If true, all child records are shown as collapsed.
	 *
	 * @var boolean
	 */
	protected $collapseAll = FALSE;

	/**
	 * Show only one expanded record at any time. If a new record is expanded,
	 * all others are collapsed.
	 *
	 * @var boolean
	 */
	protected $expandSingle = FALSE;

	/**
	 * Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')
	 *
	 * @var boolean
	 */
	protected $newRecordLinkAddTitle = FALSE;

	/**
	 * Record link position - can be either \FluidTYPO3\Flux\Form::POSITION_TOP,
	 * \FluidTYPO3\Flux\Form::POSITION_BOTTOM, \FluidTYPO3\Flux\Form::POSITION_BOTH or
	 * \FluidTYPO3\Flux\Form::POSITION_NONE.
	 *
	 * @var string
	 */
	protected $newRecordLinkPosition = Form::POSITION_TOP;

	/**
	 * For use on bidirectional relations using an intermediary table.
	 * In combinations, it's possible to edit attributes and the related child record.
	 *
	 * @var boolean
	 */
	protected $useCombination = FALSE;

	/**
	 * Allow manual sorting of child objects.
	 *
	 * @var boolean
	 */
	protected $useSortable = FALSE;

	/**
	 * Show unlocalized records which are in the original language, but not yet localized.
	 *
	 * @var boolean
	 */
	protected $showPossibleLocalizationRecords = FALSE;

	/**
	 * Show records which were once localized but do not exist in the original
	 * language anymore.
	 *
	 * @var boolean
	 */
	protected $showRemovedLocalizationRecords = FALSE;

	/**
	 * Defines whether to show the 'localize all records' link to fetch untranslated
	 * records from the original language.
	 *
	 * @var boolean
	 */
	protected $showAllLocalizationLink = FALSE;

	/**
	 * Defines whether to show a 'synchronize' link to update to a 1:1 translation with
	 * the original language.
	 *
	 * @var boolean
	 */
	protected $showSynchronizationLink = FALSE;

	/**
	 * Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete'
	 * and 'localize'. Set either one to TRUE or FALSE to show or hide it.
	 *
	 * @var array
	 */
	protected $enabledControls = array(
		Form::CONTROL_INFO => FALSE,
		Form::CONTROL_NEW => TRUE,
		Form::CONTROL_DRAGDROP => TRUE,
		Form::CONTROL_SORT => TRUE,
		Form::CONTROL_HIDE => TRUE,
		Form::CONTROL_DELETE => FALSE,
		Form::CONTROL_LOCALISE => FALSE,
	);

	/**
	 * Array of field=>value pairs which are always used in conditions as well as inserted into new
	 * records created through this form component.
	 *
	 * @var array
	 */
	protected $foreignMatchFields = array();

	/**
	 * @var array
	 */
	protected $headerThumbnail = NULL;

	/**
	 * @var string
	 */
	protected $levelLinksPosition = NULL;

	/**
	 * @var string
	 */
	protected $foreignSelectorFieldTcaOverride;

	/**
	 * @var array
	 */
	protected $foreignTypes = NULL;

	/**
	 * @param string $type
	 * @return array
	 */
	public function prepareConfiguration($type) {
		$configuration = parent::prepareConfiguration($type);
		$configuration['foreign_match_fields'] = $this->getForeignMatchFields();
		$configuration['foreign_selector_fieldTcaOverride'] = $this->getForeignSelectorFieldTcaOverride();
		$configuration['foreign_types'] = $this->getForeignTypes();
		$configuration['appearance'] = array(
			'collapseAll' => $this->getCollapseAll(),
			'expandSingle' => $this->getExpandSingle(),
			'newRecordLinkAddTitle' => $this->getNewRecordLinkAddTitle(),
			'newRecordLinkPosition' => $this->getNewRecordLinkPosition(),
			'useCombination' => $this->getUseCombination(),
			'useSortable' => $this->getUseSortable(),
			'showPossibleLocalizationRecords' => $this->getShowPossibleLocalizationRecords(),
			'showRemovedLocalizationRecords' => $this->getShowRemovedLocalizationRecords(),
			'showAllLocalizationLink' => $this->getShowAllLocalizationLink(),
			'showSynchronizationLink' => $this->getShowSynchronizationLink(),
			'enabledControls' => $this->getEnabledControls(),
			'headerThumbnail' => $this->getHeaderThumbnail(),
			'levelLinksPosition' => $this->getLevelLinksPosition(),
		);
		$configuration['behaviour'] = array(
			'localizationMode' => $this->getLocalizationMode(),
			'localizeChildrenAtParentLocalization' => $this->getLocalizeChildrenAtParentLocalization(),
			'disableMovingChildrenWithParent' => $this->getDisableMovingChildrenWithParent(),
		);
		return $configuration;
	}

	/**
	 * @param boolean $collapseAll
	 * @return AbstractInlineFormField
	 */
	public function setCollapseAll($collapseAll) {
		$this->collapseAll = $collapseAll;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getCollapseAll() {
		return $this->collapseAll;
	}

	/**
	 * @param array $enabledControls
	 * @return AbstractInlineFormField
	 */
	public function setEnabledControls(array $enabledControls) {
		$this->enabledControls = $enabledControls;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getEnabledControls() {
		return $this->enabledControls;
	}

	/**
	 * @param boolean $expandSingle
	 * @return AbstractInlineFormField
	 */
	public function setExpandSingle($expandSingle) {
		$this->expandSingle = $expandSingle;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getExpandSingle() {
		return $this->expandSingle;
	}

	/**
	 * @param boolean $newRecordLinkAddTitle
	 * @return AbstractInlineFormField
	 */
	public function setNewRecordLinkAddTitle($newRecordLinkAddTitle) {
		$this->newRecordLinkAddTitle = $newRecordLinkAddTitle;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getNewRecordLinkAddTitle() {
		return $this->newRecordLinkAddTitle;
	}

	/**
	 * @param string $newRecordLinkPosition
	 * @return AbstractInlineFormField
	 */
	public function setNewRecordLinkPosition($newRecordLinkPosition) {
		$this->newRecordLinkPosition = $newRecordLinkPosition;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNewRecordLinkPosition() {
		return $this->newRecordLinkPosition;
	}

	/**
	 * @param boolean $showAllLocalizationLink
	 * @return AbstractInlineFormField
	 */
	public function setShowAllLocalizationLink($showAllLocalizationLink) {
		$this->showAllLocalizationLink = $showAllLocalizationLink;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getShowAllLocalizationLink() {
		return $this->showAllLocalizationLink;
	}

	/**
	 * @param boolean $showPossibleLocalizationRecords
	 * @return AbstractInlineFormField
	 */
	public function setShowPossibleLocalizationRecords($showPossibleLocalizationRecords) {
		$this->showPossibleLocalizationRecords = $showPossibleLocalizationRecords;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getShowPossibleLocalizationRecords() {
		return $this->showPossibleLocalizationRecords;
	}

	/**
	 * @param boolean $showRemovedLocalizationRecords
	 * @return AbstractInlineFormField
	 */
	public function setShowRemovedLocalizationRecords($showRemovedLocalizationRecords) {
		$this->showRemovedLocalizationRecords = $showRemovedLocalizationRecords;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getShowRemovedLocalizationRecords() {
		return $this->showRemovedLocalizationRecords;
	}

	/**
	 * @param boolean $showSynchronizationLink
	 * @return AbstractInlineFormField
	 */
	public function setShowSynchronizationLink($showSynchronizationLink) {
		$this->showSynchronizationLink = $showSynchronizationLink;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getShowSynchronizationLink() {
		return $this->showSynchronizationLink;
	}

	/**
	 * @param boolean $useCombination
	 * @return AbstractInlineFormField
	 */
	public function setUseCombination($useCombination) {
		$this->useCombination = $useCombination;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getUseCombination() {
		return $this->useCombination;
	}

	/**
	 * @param boolean $useSortable
	 * @return AbstractInlineFormField
	 */
	public function setUseSortable($useSortable) {
		$this->useSortable = $useSortable;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getUseSortable() {
		return $this->useSortable;
	}

	/**
	 * @param array $foreignMatchFields
	 * @return Inline
	 */
	public function setForeignMatchFields(array $foreignMatchFields) {
		$this->foreignMatchFields = $foreignMatchFields;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getForeignMatchFields() {
		return $this->foreignMatchFields;
	}

	/**
	 * @param array $headerThumbnail
	 * @return AbstractInlineFormField
	 */
	public function setHeaderThumbnail(array $headerThumbnail) {
		$this->headerThumbnail = $headerThumbnail;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaderThumbnail() {
		return $this->headerThumbnail;
	}

	/**
	 * @param string $levelLinksPosition
	 * @return AbstractInlineFormField
	 */
	public function setLevelLinksPosition($levelLinksPosition) {
		$this->levelLinksPosition = $levelLinksPosition;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getLevelLinksPosition() {
		return $this->levelLinksPosition;
	}

	/**
	 * @param string $foreignSelectorFieldTcaOverride
	 * @return RelationFieldInterface
	 */
	public function setForeignSelectorFieldTcaOverride($foreignSelectorFieldTcaOverride) {
		$this->foreignSelectorFieldTcaOverride = $foreignSelectorFieldTcaOverride;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getForeignSelectorFieldTcaOverride() {
		return $this->foreignSelectorFieldTcaOverride;
	}

	/**
	 * @param string $foreignTypes
	 * @return RelationFieldInterface
	 */
	public function setForeignTypes($foreignTypes) {
		$this->foreignTypes = $foreignTypes;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getForeignTypes() {
		return $this->foreignTypes;
	}

}
