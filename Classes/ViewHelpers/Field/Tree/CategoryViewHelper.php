<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field\Tree;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Tree;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use FluidTYPO3\Flux\ViewHelpers\Field\TreeViewHelper;

/**
 * Tree preset for sys_category
 */
class CategoryViewHelper extends TreeViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->overrideArgument('table', 'string', 'Define foreign table name to turn selector into a record selector for that table', FALSE, 'sys_category');
		$this->overrideArgument('parentField', 'string', 'Field containing UID of parent record', FALSE, 'parent');
		$this->overrideArgument('mm', 'string', 'Optional name of MM table to use for record selection', FALSE, 'sys_category_record_mm');
		$this->overrideArgument('size', 'integer', 'Size of the selector box', FALSE, 10);
		$this->overrideArgument('maxItems', 'integer', 'Maxium allowed number of items to be selected', FALSE, 30);
		$this->overrideArgument('matchFields', 'array', 'When using manyToMany you can provide an additional array of field=>value pairs that must match in the relation table', FALSE, NULL);
		$this->overrideArgument('oppositeField', 'string', 'Name of the opposite field related to a proper mm relation', FALSE, 'items');
		$this->overrideArgument('showHeader', 'boolean', 'If TRUE, displays tree header', FALSE, TRUE);
		$this->overrideArgument('expandAll', 'boolean', 'If TRUE, expands all branches', FALSE, TRUE);
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param array $arguments
	 * @return Tree
	 */
	public static function getComponent(RenderingContextInterface $renderingContext, array $arguments) {
		$tree = parent::getComponent($renderingContext, $arguments);
		if (NULL === $arguments['matchFields']) {
			$tree->setMatchFields(array(
				'fieldname' => static::getFormFromRenderingContext($renderingContext)->getId() . '_' . $tree->getName(),
				'tablenames' => 'tt_content'
			));
		}
		return $tree;
	}

}
