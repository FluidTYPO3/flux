<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

class RenderingContext extends \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext
{
    /**
     * Template Variable Container. Contains all variables available through object accessors in the template
     *
     * @var VariableProviderInterface&StandardVariableProvider
     */
    public $variableProvider;

    /**
     * ViewHelper Variable Container
     *
     * @var ViewHelperVariableContainer
     */
    public $viewHelperVariableContainer;

    /**
     * @var ViewHelperResolver&MockObject
     */
    public $viewHelperResolver;

    /**
     * @var ViewHelperInvoker&MockObject
     */
    public $viewHelperInvoker;

    /**
     * @var TemplatePaths&MockObject
     */
    public $templatePaths;

    /**
     * @var TemplateParser&MockObject
     */
    public $templateParser;

    /**
     * @var TemplateCompiler&MockObject
     */
    public $templateCompiler;

    public function __construct()
    {
        $this->variableProvider = new StandardVariableProvider();
        $this->viewHelperVariableContainer = new ViewHelperVariableContainer();
        $this->viewHelperResolver = $this->createMock(ViewHelperResolver::class);
        $this->viewHelperInvoker = $this->createMock(ViewHelperInvoker::class);
        $this->templatePaths = $this->createMock(TemplatePaths::class);
        $this->templateParser = $this->createMock(TemplateParser::class);
        $this->templateCompiler = $this->createMock(TemplateCompiler::class);
    }

    private function createMock(string $className): object
    {
        return (new Generator())->getMock($className, [], [], '', false);
    }
}
