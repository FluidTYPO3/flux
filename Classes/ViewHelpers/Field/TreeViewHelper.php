<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Tree;

/**
 * Tree (select supertype) FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class TreeViewHelper extends AbstractRelationFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('parentField', 'string', 'Field containing UID of parent record', TRUE);
		$this->registerArgument('allowRecursiveMode', 'boolean', 'If TRUE, the selection of a node will trigger the selection of all child nodes too (recursively)', FALSE, Tree::DEFAULT_ALLOW_RECURSIVE_MODE);
		$this->registerArgument('expandAll', 'boolean', 'If TRUE, expands all branches', FALSE, Tree::DEFAULT_EXPAND_ALL);
		$this->registerArgument('nonSelectableLevels', 'string', 'Comma-separated list of levels that will not be selectable, by default the root node (which is "0") cannot be selected', FALSE, Tree::DEFAULT_NON_SELECTABLE_LEVELS);
		$this->registerArgument('maxLevels', 'integer', ' The maximal amount of levels to be rendered (can be used to stop possible recursions)', FALSE, Tree::DEFAULT_MAX_LEVELS);
		$this->registerArgument('showHeader', 'boolean', 'If TRUE, displays tree header', FALSE, Tree::DEFAULT_SHOW_HEADER);
		$this->registerArgument('width', 'integer', 'Width of TreeView component', FALSE, Tree::DEFAULT_WIDTH);
	}

	/**
	 * Render method
	 * @return Tree
	 */
	public function getComponent() {
		/** @var Tree $tree */
		$tree = $this->getPreparedComponent('Tree');
		$tree->setParentField($this->arguments['parentField']);
		$tree->setAllowRecursiveMode($this->arguments['allowRecursiveMode']);
		$tree->setExpandAll($this->arguments['expandAll']);
		$tree->setNonSelectableLevels($this->arguments['nonSelectableLevels']);
		$tree->setMaxLevels($this->arguments['maxLevels']);
		$tree->setShowHeader($this->arguments['showHeader']);
		$tree->setWidth($this->arguments['width']);
		return $tree;
	}

}
