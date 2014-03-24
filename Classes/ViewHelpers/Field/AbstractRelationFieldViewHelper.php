<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Form\RelationFieldInterface;

/**
 * Base class for all FlexForm fields.
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
abstract class AbstractRelationFieldViewHelper extends AbstractMultiValueFieldViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('table', 'string', 'Define foreign table name to turn selector into a record selector for that table', FALSE, NULL);
		$this->registerArgument('condition', 'string', 'Condition to use when selecting from "foreignTable", supports FlexForm "foregin_table_where" markers', FALSE, NULL);
		$this->registerArgument('mm', 'string', 'Optional name of MM table to use for record selection', FALSE, NULL);
		$this->registerArgument('foreignField', 'string', 'The foreign_field is the field of the child record pointing to the parent record. This defines where to store the uid of the parent record.', FALSE, '');
		$this->registerArgument('foreignLabel', 'string', "If set, it overrides the label set in TCA[foreign_table]['ctrl']['label'] for the inline-view.", FALSE, '');
		$this->registerArgument('foreignSelector', 'string', 'A selector is used to show all possible child records that could be used to create a relation with the parent record. It will be rendered as a ' .
			'multi-select-box. On clicking on an item inside the selector a new relation is created. The foreign_selector points to a field of the foreign_table that is responsible for providing a selector-box – ' .
			'this field on the foreign_table usually has the type "select" and also has a "foreign_table" defined.', FALSE, '');
		$this->registerArgument('foreignSortby', 'string', 'Define a field on the child record (or on the intermediate table) that stores the manual sorting information.', FALSE, '');
		$this->registerArgument('foreignDefaultSortby', 'string', 'If a fieldname for foreign_sortby is defined, then this is ignored. Otherwise this is used as the "ORDER BY" statement to sort the records in the table when listed.', FALSE, '');
		$this->registerArgument('foreignTableField', 'string', 'The field of the child record pointing to the parent record. This defines where to store the table name of the parent record. On setting this configuration key together with foreign_field, the child record knows what its parent record is - so the child record could also be used on other parent tables.', FALSE, '');
		$this->registerArgument('foreignUnique', 'string', 'Field which must be uniue for all children of a parent record.', FALSE, '');
		$this->registerArgument('symmetricField', 'string', 'In case of bidirectional symmetric relations, this defines in which field on the foreign table the uid of the "other" parent is stored.', FALSE, '');
		$this->registerArgument('symmetricLabel', 'string', 'If set, this overrides the default label of the selected symmetric_field.', FALSE, '');
		$this->registerArgument('symmetricSortby', 'string', 'This works like foreign_sortby, but defines the field on foreign_table where the "other" sort order is stored.', FALSE, '');
		$this->registerArgument('localizationMode', 'string', "Set whether children can be localizable ('select') or just inherit from default language ('keep').", FALSE, '');
		$this->registerArgument('localizeChildrenAtParentLocalization', 'boolean', 'Defines whether children should be localized when the localization of the parent gets created.', FALSE, FALSE);
		$this->registerArgument('disableMovingChildrenWithParent', 'boolean', 'Disables that child records get moved along with their parent records.', FALSE, FALSE);
		$this->registerArgument('showThumbs', 'boolean', 'If TRUE, adds thumbnail display when editing in BE', FALSE, TRUE);
	}

	/**
	 * @param string $type
	 * @return RelationFieldInterface
	 */
	public function getComponent($type = 'Relation') {
		$component = $this->getPreparedComponent($type);
		return $component;
	}

	/**
	 * @param string $type
	 * @return RelationFieldInterface
	 */
	protected function getPreparedComponent($type) {
		/** @var RelationFieldInterface $component */
		$component = parent::getPreparedComponent($type);
		$component->setTable($this->arguments['table']);
		$component->setCondition($this->arguments['condition']);
		$component->setManyToMany($this->arguments['mm']);
		$component->setForeignField($this->arguments['foreignField']);
		$component->setForeignSelector($this->arguments['foreignSelector']);
		$component->setForeignLabel($this->arguments['foreignLabel']);
		$component->setForeignSortby($this->arguments['foreignSortby']);
		$component->setForeignDefaultSortby($this->arguments['foreignDefaultSortby']);
		$component->setForeignTableField($this->arguments['foreignTableField']);
		$component->setForeignUnique($this->arguments['foreignUnique']);
		$component->setSymmetricField($this->arguments['symmetricField']);
		$component->setSymmetricLabel($this->arguments['symmetricLabel']);
		$component->setSymmetricSortby($this->arguments['symmetricSortby']);
		$component->setLocalizationMode($this->arguments['localizationMode']);
		$component->setLocalizeChildrenAtParentLocalization($this->arguments['localizeChildrenAtParentLocalization']);
		$component->setDisableMovingChildrenWithParent($this->arguments['disableMovingChildrenWithParent']);
		$component->setShowThumbnails($this->arguments['showThumbs']);
		return $component;
	}

}
