<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Form option ViewHelper
 */
class OptionViewHelper extends AbstractFormViewHelper {

	/**
	 * @var string
	 */
	public static $option;

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the option to be set', TRUE, NULL);
		$this->registerArgument('value', 'string', 'Option value', FALSE, NULL);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return void
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$option = isset($arguments['name']) ? $arguments['name'] : static::$option;
		$value = NULL === $arguments['value'] ? $renderChildrenClosure() : $arguments['value'];
		static::getFormFromRenderingContext($renderingContext)->setOption($option, $value);
	}

	/**
	 * @param string $argumentsVariableName
	 * @param string $renderChildrenClosureVariableName
	 * @param string $initializationPhpCode
	 * @param AbstractNode $syntaxTreeNode
	 * @param TemplateCompiler $templateCompiler
	 * @return string
	 */
	public function compile(
		$argumentsVariableName,
		$renderChildrenClosureVariableName,
		&$initializationPhpCode,
		AbstractNode $syntaxTreeNode,
		TemplateCompiler $templateCompiler
	) {
		$className = get_class($this);
		$initializationPhpCode .= sprintf(
			'%s::getFormFromRenderingContext($renderingContext)->setOption(' .
			'isset(%s[\'name\']) ? %s[\'name\'] : %s::$option,' .
			'isset(%s[\'value\']) ? %s[\'value\'] : %s()' .
			');',
			$className,
			$argumentsVariableName,
			$argumentsVariableName,
			$className,
			$argumentsVariableName,
			$argumentsVariableName,
			$renderChildrenClosureVariableName
		);
		return '\'\'';
	}

}
