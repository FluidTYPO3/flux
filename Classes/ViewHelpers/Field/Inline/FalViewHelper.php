<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field\Inline;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Inline\Fal;
use FluidTYPO3\Flux\ViewHelpers\Field\AbstractInlineFieldViewHelper;
use TYPO3\CMS\Core\Resource\File;

/**
 * Repeats rendering of children with a typical for loop: starting at index $from it will loop until the index has reached $to.
 *
 * To get the file references, assigned with that field in a flux form, you will have to use EXT:vhs but there are two different ViewHelpers for fluidpages templates and fluidcontent elements.
 *
 * Example how to get the first file reference from a fluidcontent element, for the flux field named "settings.files":
 * 	{v:content.resources.fal(field: 'settings.files')
 * 		-> v:iterator.first()
 * 		-> v:variable.set(name: 'settings.files')}
 *
 * And now the example how to get the first file reference from a fluidpages template, for the flux field named "settings.files":
 * 	{v:page.resources.fal(field: 'settings.files')
 * 		-> v:iterator.first()
 * 		-> v:variable.set(name: 'settings.files')}
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
		$this->registerArgument('createNewRelationLinkTitle', 'string', 'Override label of "Create new relation" button.', FALSE, Fal::DEFAULT_CREATE_NEW_RELATION_LINK_TITLE);
	}

	/**
	 * @return Fal
	 */
	public function getComponent() {
		$allowedExtensions = $this->arguments['allowedExtensions'];
		$disallowedExtensions = $this->arguments['disallowedExtensions'];
		$createNewRelationLinkTitle = $this->arguments['createNewRelationLinkTitle'];

		/** @var Fal $component */
		$component = $this->getPreparedComponent('Inline/Fal');
		if (FALSE === is_array($this->arguments['foreignMatchFields'])) {
			$component->setForeignMatchFields(array(
				// @todo: Retrieve this dynamically
				'fieldname' => 'pi_flexform'
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

		if (FALSE === $this->hasArgument('foreignTypes')) {
			$component->setForeignTypes(array(
				'0' => array(
					'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette'
				),
				File::FILETYPE_IMAGE => array(
					'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette'
				),
			));
		}

		$component->setCreateNewRelationLinkTitle($createNewRelationLinkTitle);

		return $component;
	}

}
