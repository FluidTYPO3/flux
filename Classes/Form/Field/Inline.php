<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * @subpackage Form\Field
 */
class Tx_Flux_Form_Field_Inline extends Tx_Flux_Form_AbstractRelationFormField {

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
	 * Record link position - can be either Tx_Flux_Form::POSITION_TOP,
	 * Tx_Flux_Form::POSITION_BOTTOM, Tx_Flux_Form::POSITION_BOTH or
	 * Tx_Flux_Form::POSITION_NONE.
	 *
	 * @var string
	 */
	protected $newRecordLinkPosition = Tx_Flux_Form::POSITION_TOP;

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
		Tx_Flux_Form::CONTROL_INFO => FALSE,
		Tx_Flux_Form::CONTROL_NEW => TRUE,
		Tx_Flux_Form::CONTROL_DRAGDROP => TRUE,
		Tx_Flux_Form::CONTROL_SORT => TRUE,
		Tx_Flux_Form::CONTROL_HIDE => TRUE,
		Tx_Flux_Form::CONTROL_DELETE => FALSE,
		Tx_Flux_Form::CONTROL_LOCALISE => FALSE,
	);

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = parent::prepareConfiguration('inline');
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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
	 * @return Tx_Flux_Form_Field_Inline
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

}
