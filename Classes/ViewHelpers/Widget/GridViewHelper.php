<?php
namespace FluidTYPO3\Flux\ViewHelpers\Widget;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * DEPRECATED
 * Grid Widget for rendering a grid in previews of BE elements
 */
class GridViewHelper extends AbstractViewHelper implements CompilableInterface {

	/**
	 * @return string
	 */
	public function render() {
		GeneralUtility::logDeprecatedFunction();
		return '';
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	static public function renderStatic(
		array $arguments,
		\Closure $renderChildrenClosure,
		RenderingContextInterface $renderingContext
	) {
		GeneralUtility::logDeprecatedFunction();
		return '';
	}

	/**
	 * @param string $argumentsVariableName
	 * @param string $renderChildrenClosureVariableName
	 * @param string $initializationPhpCode
	 * @param AbstractNode $syntaxTreeNode
	 * @param TemplateCompiler $templateCompiler
	 * @return NULL
	 */
	public function compile(
		$argumentsVariableName,
		$renderChildrenClosureVariableName,
		&$initializationPhpCode,
		AbstractNode $syntaxTreeNode,
		TemplateCompiler $templateCompiler
	) {
		return 'NULL';
	}

}
