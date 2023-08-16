<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyViewHelperNode;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Web\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

abstract class AbstractViewHelperTestCase extends AbstractTestCase
{
    protected ?RenderingContext $renderingContext;
    protected ?ViewHelperResolver $viewHelperResolver;
    protected ?ViewHelperInvoker $viewHelperInvoker;
    protected ?ViewHelperVariableContainer $viewHelperVariableContainer;
    protected ?StandardVariableProvider $templateVariableContainer;
    protected ?ControllerContext $controllerContext;
    protected ?ErrorHandlerInterface $errorHandler;
    protected ?TemplateParser $templateParser;
    protected array $templateProcessors = [];
    protected array $expressionTypes = [];

    protected array $defaultArguments = [
        'name' => 'test'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(Request::class)) {
            $requestClassName = Request::class;
        } else {
            $requestClassName = \TYPO3\CMS\Extbase\Mvc\Request::class;
        }

        $request = $this->getMockBuilder($requestClassName)->disableOriginalConstructor()->getMock();

        $this->viewHelperResolver = $this->getMockBuilder(ViewHelperResolver::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewHelperVariableContainer = $this->getMockBuilder(ViewHelperVariableContainer::class)
            ->setMethods(['dummy'])
            ->getMock();
        $this->templateVariableContainer = $this->getMockBuilder(StandardVariableProvider::class)
            ->setMethods(['dummy'])
            ->getMock();
        $this->controllerContext = $this->getMockBuilder(ControllerContext::class)
            ->setMethods(['getRequest', 'getUriBuilder'])
            ->getMock();
        $this->viewHelperInvoker = $this->getMockBuilder(ViewHelperInvoker::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateParser = new TemplateParser();
        $this->errorHandler = $this->getMockBuilder(ErrorHandlerInterface::class)->getMockForAbstractClass();
        $this->errorHandler->method('handleViewHelperError')->willThrowException(new Exception('dummy'));

        $this->renderingContext->method('buildParserConfiguration')->willReturn(new Configuration());
        $this->renderingContext->method('getViewHelperResolver')->willReturn($this->viewHelperResolver);
        if (method_exists($this->renderingContext, 'getControllerContext')) {
            $this->renderingContext->method('getControllerContext')->willReturn($this->controllerContext);
            $this->controllerContext->method('getRequest')->willReturn($request);
        } else {
            $this->renderingContext->method('getRequest')->willReturn($request);
        }

        $this->renderingContext->method('getViewHelperVariableContainer')->willReturn(
            $this->viewHelperVariableContainer
        );
        $this->renderingContext->method('getVariableProvider')->willReturn($this->templateVariableContainer);
        $this->renderingContext->method('getViewHelperInvoker')->willReturn($this->viewHelperInvoker);
        $this->renderingContext->method('getErrorHandler')->willReturn($this->errorHandler);
        $this->renderingContext->method('getTemplateParser')->willReturn($this->templateParser);
        $this->renderingContext->method('getTemplateProcessors')->willReturn($this->templateProcessors);
        $this->renderingContext->method('getExpressionNodeTypes')->willReturn($this->expressionTypes);

        $this->templateParser->setRenderingContext($this->renderingContext);
    }

    /**
     * @test
     */
    public function canCreateViewHelperInstance(): void
    {
        $instance = $this->createInstance();
        $this->assertInstanceOf($this->getViewHelperClassName(), $instance);
    }

    /**
     * @test
     */
    public function canPrepareArguments(): void
    {
        $instance = $this->createInstance();
        $arguments = $instance->prepareArguments();
        $this->assertIsArray($arguments);
    }

    protected function getViewHelperClassName(): string
    {
        $class = get_class($this);
        $class = str_replace('Tests\\Unit\\', '', $class);
        return substr($class, 0, -4);
    }

    /**
     * @param string $type
     */
    protected function createNode(string $type, $value): NodeInterface
    {
        /** @var NodeInterface $node */
        $className = 'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\' . $type . 'Node';
        if (class_exists($className)) {
            $node = new $className($value);
        } else {
            $className = 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\' . $type . 'Node';
            $node = new $className($value);
        }
        return $node;
    }

    protected function createInstance(): ViewHelperInterface
    {
        $className = $this->getViewHelperClassName();
        /** @var AbstractViewHelper $instance */
        $instance = new $className();
        if (method_exists($instance, 'injectConfigurationManager')) {
            $cObject = $this->getMockBuilder(ContentObjectRenderer::class)->disableOriginalConstructor()->getMock();
            $cObject->start(Records::$contentRecordWithoutParentAndWithoutChildren, 'tt_content');
            /** @var ConfigurationManagerInterface $configurationManager */
            $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)
                ->getMockForAbstractClass();
            $configurationManager->method('getContentObject')->willReturn($cObject);
            $instance->injectConfigurationManager($configurationManager);
        }
        $instance->setRenderingContext($this->renderingContext);
        return $instance;
    }

    protected function buildViewHelperInstance(
        array $arguments = [],
        array $variables = [],
        ?NodeInterface $childNode = null,
        ?string $extensionName = null,
        ?string $pluginName = null
    ): ViewHelperInterface {
        $instance = $this->createInstance();
        $arguments = $this->buildViewHelperArguments($instance, $arguments);
        $node = $this->createViewHelperNode(
            $instance,
            $arguments,
            $childNode instanceof NodeInterface ? [$childNode] : []
        );

        $instance->setViewHelperNode($node);
        $instance->setArguments($arguments);

        if (method_exists($instance, 'injectReflectionService')) {
            $instance->injectReflectionService(new ReflectionService());
        }
        return $instance;
    }

    protected function buildViewHelperArguments(ViewHelperInterface $viewHelper, array $arguments): array
    {
        foreach ($viewHelper->prepareArguments() as $argumentName => $argumentDefinition) {
            if (!array_key_exists($argumentName, $arguments)) {
                $arguments[$argumentName] = $argumentDefinition->getDefaultValue();
            }
        }
        return $arguments;
    }

    /**
     * @return mixed
     */
    protected function executeViewHelper(
        array $arguments = [],
        array $variables = [],
        ?NodeInterface $childNode = null,
        ?string $extensionName = null,
        ?string $pluginName = null
    ) {
        $instance = $this->buildViewHelperInstance($arguments, $variables, $childNode, $extensionName, $pluginName);
        $this->renderingContext->getVariableProvider()->setSource($variables);
        return $this->renderingContext->getViewHelperInvoker()->invoke($instance, $arguments, $this->renderingContext);
    }

    /**
     * @param mixed $nodeValue
     * @return mixed
     */
    protected function executeViewHelperUsingTagContent(
        $nodeValue,
        array $arguments = [],
        array $variables = [],
        ?string $extensionName = null,
        ?string $pluginName = null
    ) {
        $node = $this->getMockBuilder(NodeInterface::class)->getMockForAbstractClass();
        $node->method('evaluate')->willReturn($nodeValue);
        $instance = $this->buildViewHelperInstance($arguments, $variables, $node, $extensionName, $pluginName);
        return $this->renderingContext->getViewHelperInvoker()->invoke($instance, $arguments, $this->renderingContext);
    }

    /**
     * @return MockObject|ViewHelperNode
     */
    protected function createViewHelperNode(
        ViewHelperInterface $instance,
        array $arguments,
        array $childNNodes = []
    ): ViewHelperNode {
        $node = new DummyViewHelperNode($instance);

        foreach ($childNNodes as $childNNode) {
            $node->addChildNode($childNNode);
        }

        $instance->setChildNodes($childNNodes);
        $instance->setViewHelperNode($node);

        return $node;
    }

    protected function createObjectAccessorNode(string $accessor): ObjectAccessorNode
    {
        return new ObjectAccessorNode($accessor);
    }

    protected function expectViewHelperException(?string $message = null, ?int $code = null): void
    {
        $this->expectException(\TYPO3Fluid\Fluid\Core\ViewHelper\Exception::class, $message, $code);
    }
}
