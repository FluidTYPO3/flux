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
class Tx_Flux_ViewHelpers_Flexform_Field_InlineViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('foreignTable', 'string', 'The table containing the child records. Must be configured in TCA.', TRUE, '');

		// Appearance
		$this->registerArgument('collapseAll', 'boolean', 'If true, all child records are shown as collapsed.', FALSE, FALSE);
		$this->registerArgument('expandSingle', 'boolean', 'Show only one expanded record at any time. If a new record is expanded, all others are collapsed.', FALSE, FALSE);
		$this->registerArgument('newRecordLinkAddTitle', 'boolean', "Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')", FALSE, FALSE);
		$this->registerArgument('newRecordLinkPosition', 'string', "Where to show 'Add new' link. Can be 'top', 'bottom', 'both' or 'none'.", FALSE, 'top');
		$this->registerArgument('useCombination', 'boolean', "For use on bidirectional relations using an intermediary table. In combinations, it's possible to edit attributes and the related child record.", FALSE, FALSE);
		$this->registerArgument('useSortable', 'boolean', "Allow manual sorting of records.", FALSE, FALSE);
		$this->registerArgument('showPossibleLocalizationRecords', 'boolean', "Show unlocalized records which are in the original language, but not yet localized.", FALSE, FALSE);
		$this->registerArgument('showRemovedLocalizationRecords', 'boolean', "Show records which were once localized but do not exist in the original language anymore.", FALSE, FALSE);
		$this->registerArgument('showAllLocalizationLink', 'boolean', "Defines whether to show the 'localize all records' link to fetch untranslated records from the original language.", FALSE, FALSE);
		$this->registerArgument('showSynchronizationLink', 'boolean', "Defines whether to show a 'synchronize' link to update to a 1:1 translation with the original language.", FALSE, FALSE);
		$this->registerArgument('enabledControls', 'array', "Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete' and 'localize'. Set either one to TRUE or FALSE to show or hide it.", FALSE, FALSE);

		// Behaviour
		$this->registerArgument('localizationMode', 'string', "Set whether children can be localizable ('select') or just inherit from default language ('keep').", FALSE, '');
		$this->registerArgument('localizeChildrenAtParentLocalization', 'boolean', "Defines whether children should be localized when the localization of the parent gets created.", FALSE, FALSE);
		$this->registerArgument('disableMovingChildrenWithParent', 'boolean', "Disables that child records get moved along with their parent records.", FALSE, FALSE);

		$this->registerArgument('foreignField', 'string', "The foreign_field is the field of the child record pointing to the parent record. This defines where to store the uid of the parent record.", FALSE, '');
		$this->registerArgument('foreignLabel', 'string', "If set, it overrides the label set in TCA[foreign_table]['ctrl']['label'] for the inline-view.", FALSE, '');
		$this->registerArgument('foreignSelector', 'string', 'A selector is used to show all possible child records that could be used to create a relation with the parent record. It will be rendered as a multi-select-box. On clicking on an item inside the selector a new relation is created. The foreign_selector points to a field of the foreign_table that is responsible for providing a selector-box – this field on the foreign_table usually has the type "select" and also has a "foreign_table" defined.', FALSE, '');
		$this->registerArgument('foreignSortby', 'string', 'Define a field on the child record (or on the intermediate table) that stores the manual sorting information.', FALSE, '');
		$this->registerArgument('foreignDefaultSortby', 'string', 'If a fieldname for foreign_sortby is defined, then this is ignored. Otherwise this is used as the "ORDER BY" statement to sort the records in the table when listed.', FALSE, '');
		$this->registerArgument('foreignTableField', 'string', 'The field of the child record pointing to the parent record. This defines where to store the table name of the parent record. On setting this configuration key together with foreign_field, the child record knows what its parent record is – so the child record could also be used on other parent tables.', FALSE, '');
		$this->registerArgument('foreignUnique', 'string', 'Field which must be uniue for all children of a parent record.', FALSE, '');
		$this->registerArgument('mm', 'string', 'Name of table to use as intermediary between this record and foreign_table.', FALSE, '');
		$this->registerArgument('symmetricField', 'string', 'In case of bidirectional symmetric relations, this defines in which field on the foreign table the uid of the "other" parent is stored.', FALSE, '');
		$this->registerArgument('symmetricLabel', 'string', 'If set, this overrides the default label of the selected symmetric_field.', FALSE, '');
		$this->registerArgument('symmetricSortby', 'string', 'This works like foreign_sortby, but defines the field on foreign_table where the "other" sort order is stored.', FALSE, '');

		$this->registerArgument('size', 'integer', 'Size of the selector box', FALSE, 1);
		$this->registerArgument('minItems', 'integer', 'Minimum required number of items to be selected', FALSE, 0);
		$this->registerArgument('maxItems', 'integer', 'Maximum allowed number of items to be selected', FALSE, 1);
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {

		if (Tx_Flux_Utility_Version::assertCoreVersionIsBelowSixPointZero() === TRUE) {
			throw new Exception("Sorry - the inline-viewhelper is only available from TYPO3 6.0 and up, due to limitations in the flexform setup.");
		}

		$config = $this->getBaseConfig();
		$config['type'] = 'Inline';
		$config['foreign_table'] = $this->arguments['foreignTable'];

		$config['size'] = $this->arguments['size'];
		$config['minitems'] = $this->arguments['minItems'];
		$config['maxitems'] = $this->arguments['maxItems'];

		$config['foreign_field'] = $this->arguments['foreignField'];
		$config['foreign_label'] = $this->arguments['foreignLabel'];
		$config['foreign_selector'] = $this->arguments['foreignSelector'];
		$config['foreign_sortby'] = $this->arguments['foreignSortby'];
		$config['foreign_default_sortby'] = $this->arguments['foreignDefaultSortby'];
		$config['foreign_table_field'] = $this->arguments['foreignTableField'];
		$config['foreign_unique'] = $this->arguments['foreignUnique'];
		$config['mm'] = $this->arguments['mm'];
		$config['symmetric_field'] = $this->arguments['symmetricField'];
		$config['symmetric_label'] = $this->arguments['symmetricLabel'];
		$config['symmetric_sortby'] = $this->arguments['symmetricSortby'];

		$config['appearance'] = array(
			"collapseAll" => $this->arguments['collapseAll'],
			"expandSingle" => $this->arguments['expandSingle'],
			"newRecordLinkAddTitle" => $this->arguments['newRecordLinkAddTitle'],
			"newRecordLinkPosition" => $this->arguments['newRecordLinkPosition'],
			"useCombination" => $this->arguments['useCombination'],
			"useSortable" => $this->arguments['useSortable'],
			"showPossibleLocalizationRecords" => $this->arguments['showPossibleLocalizationRecords'],
			"showRemovedLocalizationRecords" => $this->arguments['showRemovedLocalizationRecords'],
			"showAllLocalizationLink" => $this->arguments['showAllLocalizationLink'],
			"showSynchronizationLink" => $this->arguments['showSynchronizationLink'],
			"enabledControls" => $this->arguments['enabledControls'],
		);
		$config['behaviour'] = array(
			"localizationMode" => $this->arguments['localizationMode'],
			"localizeChildrenAtParentLocalization" => $this->arguments['localizeChildrenAtParentLocalization'],
			"disableMovingChildrenWithParent" => $this->arguments['disableMovingChildrenWithParent'],
		);

		$this->addField($config);
	}

}
