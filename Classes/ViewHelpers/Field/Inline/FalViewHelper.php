<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field\Inline;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
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
 ***************************************************************/

use FluidTYPO3\Flux\Form\Field\Inline\Fal;
use FluidTYPO3\Flux\ViewHelpers\Field\AbstractInlineFieldViewHelper;
use TYPO3\CMS\Core\Resource\File;

/**
 * Repeats rendering of children with a typical for loop: starting at index $from it will loop until the index has reached $to.
 *
 * @author Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
 * @author Johannes Pieper <pieper@dlrg.de> DLRG e.V.
 * @package Flux
 * @subpackage ViewHelpers/Field/Inline
 */
class FalViewHelper extends AbstractInlineFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();

		$this->overrideArgument('table', 'string', 'Define foreign table name to turn selector into a record selector for that table', FALSE, Fal::DEFAULT_TABLE);
		$this->overrideArgument('foreignField', 'string', 'The foreign_field is the field of the child record pointing to the parent record. This defines where to store the uid of the parent record.', FALSE, Fal::DEFAULT_FOREIGN_FIELD);
		$this->overrideArgument('foreignLabel', 'string', "If set, it overrides the label set in TCA[foreign_table]['ctrl']['label'] for the inline-view.", FALSE, Fal::DEFAULT_FOREIGN_LABEL);
		$this->overrideArgument('foreignSelector', 'string', 'A selector is used to show all possible child records that could be used to create a relation with the parent record. It will be rendered as a ' .
			'multi-select-box. On clicking on an item inside the selector a new relation is created. The foreign_selector points to a field of the foreign_table that is responsible for providing a selector-box – ' .
			'this field on the foreign_table usually has the type "select" and also has a "foreign_table" defined.', FALSE, Fal::DEFAULT_FOREIGN_SELECTOR);
		$this->overrideArgument('foreignSortby', 'string', 'Define a field on the child record (or on the intermediate table) that stores the manual sorting information.', FALSE, Fal::DEFAULT_FOREIGN_SORTBY);
		$this->overrideArgument('foreignTableField', 'string', 'The field of the child record pointing to the parent record. This defines where to store the table name of the parent record. On setting this ' .
			'configuration key together with foreign_field, the child record knows what its parent record is - so the child record could also be used on other parent tables.', FALSE, Fal::DEFAULT_FOREIGN_TABLE_FIELD);
		$this->overrideArgument('localizationMode', 'string', "Set whether children can be localizable ('select') or just inherit from default language ('keep').", FALSE, Fal::DEFAULT_LOCALIZATION_MODE);
		$this->overrideArgument('localizeChildrenAtParentLocalization', 'boolean', 'Defines whether children should be localized when the localization of the parent gets created.', FALSE, Fal::DEFAULT_LOCALIZE_CHILDREN_AT_PARENT_LOCALIZATION);

		$this->overrideArgument('newRecordLinkAddTitle', 'boolean', "Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')", FALSE, Fal::DEFAULT_NEW_RECORD_LINK_ADD_TITLE);
		$this->overrideArgument('useSortable', 'boolean', 'Allow manual sorting of records.', FALSE, Fal::DEFAULT_USE_SORTABLE);
		$this->overrideArgument('levelLinksPosition', 'string', 'Level links position.', FALSE, Fal::DEFAULT_LEVEL_LINKS_POSITION);

		$this->registerArgument('allowedExtensions', 'string', 'Allowed File Extensions .', FALSE, $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
		$this->registerArgument('disallowedExtensions', 'string', 'Disallowed File Extensions .', FALSE, '');
	}

	/**
	 * @return Fal
	 */
	public function getComponent() {
		$allowedExtensions = $this->arguments['allowedExtensions'];
		$disallowedExtensions = $this->arguments['disallowedExtensions'];

		$component = $this->getPreparedComponent('Inline/Fal');
		if (FALSE === is_array($this->arguments['foreignMatchFields'])) {
			$component->setForeignMatchFields(array(
				'fieldname' => $this->arguments['name']
			));
		}
		$component->setForeignSelectorFieldTcaOverride(array(
			'config' => array(
				'appearance' => array(
					'elementBrowserType' => 'file',
					'elementBrowserAllowed' => $allowedExtensions
				)
			)
		));
		$component->setFilter(array(array(
				'userFunc' => 'TYPO3\\CMS\\Core\\Resource\\Filter\\FileExtensionFilter->filterInlineChildren',
				'parameters' => array(
					'allowedFileExtensions' => $allowedExtensions,
					'disallowedFileExtensions' => $disallowedExtensions
				)
			))
		);
		$component->setForeignTypes(array(
			'0' => array(
				'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette'
			),
			File::FILETYPE_IMAGE => array(
				'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette'
			),
		));

		return $component;
	}

}
