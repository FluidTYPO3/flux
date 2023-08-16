<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Tree;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Tree (select supertype) FlexForm field ViewHelper
 */
class TreeViewHelper extends AbstractRelationFieldViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('parentField', 'string', 'Field containing UID of parent record', true);
        $this->registerArgument(
            'allowRecursiveMode',
            'boolean',
            'If TRUE, the selection of a node will trigger the selection of all child nodes too (recursively)',
            false,
            Tree::DEFAULT_ALLOW_RECURSIVE_MODE
        );
        $this->registerArgument(
            'expandAll',
            'boolean',
            'If TRUE, expands all branches',
            false,
            Tree::DEFAULT_EXPAND_ALL
        );
        $this->registerArgument(
            'nonSelectableLevels',
            'string',
            'Comma-separated list of levels that will not be selectable, by default the root node (which is "0") ' .
            'cannot be selected',
            false,
            Tree::DEFAULT_NON_SELECTABLE_LEVELS
        );
        $this->registerArgument(
            'maxLevels',
            'integer',
            'The maximal amount of levels to be rendered (can be used to stop possible recursions)',
            false,
            Tree::DEFAULT_MAX_LEVELS
        );
        $this->registerArgument(
            'showHeader',
            'boolean',
            'If TRUE, displays tree header',
            false,
            Tree::DEFAULT_SHOW_HEADER
        );
        $this->registerArgument('width', 'integer', 'Width of TreeView component', false, Tree::DEFAULT_WIDTH);
    }

    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments): Tree
    {
        /** @var array $arguments */
        /** @var Tree $tree */
        $tree = static::getPreparedComponent(Tree::class, $renderingContext, $arguments);
        $tree->setParentField($arguments['parentField']);
        $tree->setAllowRecursiveMode($arguments['allowRecursiveMode']);
        $tree->setExpandAll($arguments['expandAll']);
        $tree->setNonSelectableLevels($arguments['nonSelectableLevels']);
        $tree->setMaxLevels($arguments['maxLevels']);
        $tree->setShowHeader($arguments['showHeader']);
        $tree->setWidth($arguments['width']);
        return $tree;
    }
}
