<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Utility\VersionUtility;
use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentProcessorInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
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

    public ArgumentProcessorInterface $argumentProcessor;

    /**
     * @var string
     */
    public $controllerName = 'Default';

    /**
     * @var string
     */
    public $controllerAction = 'Default';

    /**
     * @var Configuration
     */
    public $configuration = null;

    public function __construct()
    {
        $this->variableProvider = new StandardVariableProvider();
        $this->viewHelperVariableContainer = new ViewHelperVariableContainer();
        $this->viewHelperResolver = new ViewHelperResolver();
        $this->viewHelperInvoker = new ViewHelperInvoker();
        $this->templatePaths = new TemplatePaths();
        $this->templateParser = new TemplateParser();
        $this->templateCompiler = new TemplateCompiler();

        $this->templateParser->setRenderingContext($this);
        $this->templateCompiler->setRenderingContext($this);
        $this->viewHelperResolver->addNamespace(
            'f',
            ['TYPO3\\CMS\\Fluid\\ViewHelpers', 'TYPO3Fluid\\Fluid\\ViewHelpers']
        );
        $this->viewHelperResolver->addNamespace('flux', 'FluidTYPO3\\Flux\\ViewHelpers');

        if (VersionUtility::isCoreAtLeast14()) {
            $this->argumentProcessor = new PassthroughArgumentProcessor();
        }

        $this->setTemplateProcessors(
            [
                new NamespaceDetectionTemplateProcessor(),
            ]
        );

        $root = realpath(__DIR__ . '/../../../');
        $this->templatePaths->setTemplateRootPaths([$root . '/Tests/Fixtures/Templates/']);
        $this->templatePaths->setPartialRootPaths([$root . '/Tests/Fixtures/Partials/']);
        $this->templatePaths->setLayoutRootPaths([$root . '/Tests/Fixtures/Layouts/']);
    }

    public function buildParserConfiguration(): Configuration
    {
        return $this->configuration ?? new Configuration();
    }

    private function createMock(string $className): object
    {
        return (new Generator())->getMock($className, [], [], '', false);
    }

    public function __clone(): void
    {
    }
}
