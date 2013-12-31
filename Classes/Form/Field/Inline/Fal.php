<?php
namespace FluidTYPO3\Flux\Form\Field\Inline;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
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

use FluidTYPO3\Flux\Form\AbstractInlineFormField;
use FluidTYPO3\Flux\Form;

/**
 * @author Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
 * @author Johannes Pieper <pieper@dlrg.de> DLRG e.V.
 * @package Flux
 * @subpackage Form\Field\Inline
 */
class Fal extends AbstractInlineFormField {

	const DEFAULT_TABLE = 'sys_file_reference';
	const DEFAULT_FOREIGN_FIELD = 'uid_foreign';
	const DEFAULT_FOREIGN_TABLE_FIELD = 'tablenames';
	const DEFAULT_FOREIGN_LABEL = 'uid_local';
	const DEFAULT_FOREIGN_SELECTOR = 'uid_local';
	const DEFAULT_FOREIGN_SORTBY = 'sorting_foreign';
	const DEFAULT_USE_SORTABLE = TRUE;
	const DEFAULT_LEVEL_LINKS_POSITION = 'both';
	const DEFAULT_LOCALIZATION_MODE = 'select';
	const DEFAULT_LOCALIZE_CHILDREN_AT_PARENT_LOCALIZATION = TRUE;
	const DEFAULT_NEW_RECORD_LINK_ADD_TITLE = TRUE;

	/**
	 * @var string
	 */
	protected $table = self::DEFAULT_TABLE;

	/**
	 * The foreign_field is the field of the child record pointing to the
	 * parent record. This defines where to store the uid of the parent record.
	 *
	 * @var string
	 */
	protected $foreignField = self::DEFAULT_FOREIGN_FIELD;

	/**
	 * The field of the child record pointing to the parent record. This defines
	 * where to store the table name of the parent record. On setting this
	 * configuration key together with foreign_field, the child record knows what
	 * its parent record is – so the child record could also be used on other
	 * parent tables.
	 *
	 * @var string
	 */
	protected $foreignTableField = self::DEFAULT_FOREIGN_TABLE_FIELD;

	/**
	 * If set, it overrides the label set in TCA[foreign_table]['ctrl']['label']
	 * for the foreign table view.
	 *
	 * @var string
	 */
	protected $foreignLabel = self::DEFAULT_FOREIGN_LABEL;

	/**
	 * A selector is used to show all possible child records that could be used
	 * to create a relation with the parent record. It will be rendered as a
	 * multi-select-box. On clicking on an item inside the selector a new relation
	 * is created. The foreign_selector points to a field of the foreign_table that
	 * is responsible for providing a selector-box – this field on the foreign_table
	 * usually has the type "select" and also has a "foreign_table" defined.
	 *
	 * @var string
	 */
	protected $foreignSelector = self::DEFAULT_FOREIGN_SELECTOR;

	/**
	 * Defines a field on the child record (or on the intermediate table) that
	 * stores the manual sorting information.
	 *
	 * @var string
	 */
	protected $foreignSortby = self::DEFAULT_FOREIGN_SORTBY;

	/**
	 * Allow manual sorting of child objects.
	 *
	 * @var boolean
	 */
	protected $useSortable = self::DEFAULT_USE_SORTABLE;

	/**
	 * Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete'
	 * and 'localize'. Set either one to TRUE or FALSE to show or hide it.
	 *
	 * @var array
	 */
	protected $enabledControls = array(
		Form::CONTROL_INFO => FALSE,
		Form::CONTROL_NEW => FALSE,
		Form::CONTROL_DRAGDROP => TRUE,
		Form::CONTROL_SORT => TRUE,
		Form::CONTROL_HIDE => TRUE,
		Form::CONTROL_DELETE => TRUE,
		Form::CONTROL_LOCALISE => TRUE,
	);

	/**
	 * @var array
	 */
	protected $headerThumbnail = array(
		'field' => 'uid_local',
		'width' => '64',
		'height' => '64',
	);

	/**
	 * @var string
	 */
	protected $levelLinksPosition = self::DEFAULT_LEVEL_LINKS_POSITION;

	/**
	 * Set whether children can be localizable ('select') or just inherit from
	 * default language ('keep'). Default is empty, meaning no particular behavior.
	 *
	 * @var string
	 */
	protected $localizationMode = self::DEFAULT_LOCALIZATION_MODE;

	/**
	 * Defines whether children should be localized when the localization of the
	 * parent gets created.
	 *
	 * @var boolean
	 */
	protected $localizeChildrenAtParentLocalization = self::DEFAULT_LOCALIZE_CHILDREN_AT_PARENT_LOCALIZATION;

	/**
	 * Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')
	 *
	 * @var boolean
	 */
	protected $newRecordLinkAddTitle = self::DEFAULT_NEW_RECORD_LINK_ADD_TITLE;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = $this->prepareConfiguration('inline');
		return $configuration;
	}

}