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
		$this->registerArgument('expandAll', 'boolean', 'If TRUE, expands all branches', FALSE, FALSE);
		$this->registerArgument('showHeader', 'boolean', 'If TRUE, displays tree header', FALSE, FALSE);
		$this->registerArgument('width', 'integer', 'Width of TreeView component', FALSE, 400);
	}

	/**
	 * Render method
	 * @return Tree
	 */
	public function getComponent() {
		/** @var Tree $tree */
		$tree = $this->getPreparedComponent('Tree');
		$tree->setParentField($this->arguments['parentField']);
		$tree->setExpandAll($this->arguments['expandAll']);
		$tree->setShowHeader($this->arguments['showHeader']);
		$tree->setWidth($this->arguments['width']);
		return $tree;
	}

}
