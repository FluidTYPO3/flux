<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Suggest;

/**
 * Field Wizard: Suggest
 *
 * @package Flux
 * @subpackage ViewHelpers/Wizard
 */
class SuggestViewHelper extends AbstractWizardViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('table', 'string', 'Table to search. If left out will use the table defined by the parent field', FALSE, NULL);
		$this->registerArgument('pidList', 'string', 'List of storage page UIDs', FALSE, '0');
		$this->registerArgument('pidDepth', 'integer', 'Depth of recursive storage page UID lookups', FALSE, 99);
		$this->registerArgument('minimumCharacters', 'integer', 'Minimum number of characters that must be typed before search begins', FALSE, 1);
		$this->registerArgument('maxPathTitleLength', 'integer', 'Maximum path segment length - crops titles over this length', FALSE, 15);
		$this->registerArgument('searchWholePhrase', 'boolean', 'A match requires a full word that matches the search value', FALSE, FALSE);
		$this->registerArgument('searchCondition', 'string', 'Search condition - for example, if table is pages "doktype = 1" to only allow standard pages', FALSE, '');
		$this->registerArgument('cssClass', 'string', 'Use this CSS class for all list items', FALSE, '');
		$this->registerArgument('receiverClass', 'string', 'Class reference, target class should be derived from "t3lib_tceforms_suggest_defaultreceiver"', FALSE, '');
		$this->registerArgument('renderFunc', 'string', 'Reference to function which processes all records displayed in results', FALSE, '');
	}

	/**
	 * @return Suggest
	 */
	public function getComponent() {
		/** @var Suggest $component */
		$component = $this->getPreparedComponent('Suggest');
		$component->setTable($this->arguments['table']);
		$component->setStoragePageUids($this->arguments['pidList']);
		$component->setStoragePageRecursiveDepth($this->arguments['pidDepth']);
		$component->setMinimumCharacters($this->arguments['minimumCharacters']);
		$component->setMaxPathTitleLength($this->arguments['maxPathTitleLength']);
		$component->setSearchWholePhrase($this->arguments['setSearchWholePhrase']);
		$component->setSearchCondition($this->arguments['searchCondition']);
		$component->setCssClass($this->arguments['cssClass']);
		$component->setReceiverClass($this->arguments['receiverClass']);
		$component->setRenderFunction($this->arguments['renderFunc']);
		return $component;
	}

}
