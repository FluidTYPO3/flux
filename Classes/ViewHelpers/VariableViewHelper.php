<?php
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\AbstractNode;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Fetches a single variable from the template variables
 */
class VariableViewHelper extends AbstractViewHelper implements CompilableInterface
{

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name (dotted path supported) of template variable to get', true);
    }

    /**
     * @return string
     */
    public function render()
    {
        return static::renderStatic($this->arguments, $this->renderChildrenClosure, $this->renderingContext);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        return ObjectAccess::getPropertyPath(
            $renderingContext->getTemplateVariableContainer()->getAll(),
            $arguments['name']
        );
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
        return sprintf(
            '%s::getPropertyPath($renderingContext->getTemplateVariableContainer()->getAll(), %s[\'name\'])',
            ObjectAccess::class,
            $argumentsVariableName
        );
    }
}
