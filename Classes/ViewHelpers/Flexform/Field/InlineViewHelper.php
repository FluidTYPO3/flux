<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Anders Gissel <anders@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Inline-style FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_InlineViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractRelationFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('collapseAll', 'boolean', 'If true, all child records are shown as collapsed.', FALSE, FALSE);
		$this->registerArgument('expandSingle', 'boolean', 'Show only one expanded record at any time. If a new record is expanded, all others are collapsed.', FALSE, FALSE);
		$this->registerArgument('newRecordLinkAddTitle', 'boolean', "Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')", FALSE, FALSE);
		$this->registerArgument('newRecordLinkPosition', 'string', "Where to show 'Add new' link. Can be 'top', 'bottom', 'both' or 'none'.", FALSE, 'top');
		$this->registerArgument('useCombination', 'boolean', "For use on bidirectional relations using an intermediary table. In combinations, it's possible to edit attributes and the related child record.", FALSE, FALSE);
		$this->registerArgument('useSortable', 'boolean', 'Allow manual sorting of records.', FALSE, FALSE);
		$this->registerArgument('showPossibleLocalizationRecords', 'boolean', 'Show unlocalized records which are in the original language, but not yet localized.', FALSE, FALSE);
		$this->registerArgument('showRemovedLocalizationRecords', 'boolean', 'Show records which were once localized but do not exist in the original language anymore.', FALSE, FALSE);
		$this->registerArgument('showAllLocalizationLink', 'boolean', "Defines whether to show the 'localize all records' link to fetch untranslated records from the original language.", FALSE, FALSE);
		$this->registerArgument('showSynchronizationLink', 'boolean', "Defines whether to show a 'synchronize' link to update to a 1:1 translation with the original language.", FALSE, FALSE);
		$this->registerArgument('enabledControls', 'array', "Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete' and 'localize'. Set either one to TRUE or FALSE to show or hide it.", FALSE, FALSE);
	}

	/**
	 * @return Tx_Flux_Form_Field_Inline
	 */
	public function getComponent() {
		/** @var Tx_Flux_Form_Field_Inline $component */
		$component = parent::getComponent('Inline');
		$component->setCollapseAll($this->arguments['collapseAll']);
		$component->setExpandSingle($this->arguments['expandSingle']);
		$component->setNewRecordLinkAddTitle($this->arguments['newRecordLinkAddTitle']);
		$component->setNewRecordLinkPosition($this->arguments['newRecordLinkPosition']);
		$component->setUseCombination($this->arguments['useCombination']);
		$component->setUseSortable($this->arguments['useSortable']);
		$component->setShowPossibleLocalizationRecords($this->arguments['showPossibleLocalizationRecords']);
		$component->setShowRemovedLocalizationRecords($this->arguments['showRemovedLocalizationRecords']);
		$component->setShowAllLocalizationLink($this->arguments['showAllLocalizationLink']);
		$component->setShowSynchronizationLink($this->arguments['showSynchronizationLink']);
		if (TRUE === is_array($this->arguments['enabledControls'])) {
			$component->setEnabledControls($this->arguments['enabledControls']);
		}
		return $component;
	}

}
