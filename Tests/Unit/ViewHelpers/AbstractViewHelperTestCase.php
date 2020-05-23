<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Development\ProtectedAccess;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\Variables\CmsVariableProvider;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * AbstractViewHelperTestCase
 */
abstract class AbstractViewHelperTestCase extends AbstractTestCase
{
    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    /**
     * @var array
     */
    protected $defaultArguments = array(
        'name' => 'test'
    );

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->renderingContext = new RenderingContext();
    }

    /**
     * @test
     */
    public function canCreateViewHelperInstance()
    {
        $instance = $this->createInstance();
        $this->assertInstanceOf($this->getViewHelperClassName(), $instance);
    }

    /**
     * @test
     */
    public function canPrepareArguments()
    {
        $instance = $this->createInstance();
        $arguments = $instance->prepareArguments();
        self::assertIsArray($arguments);
    }

    /**
     * @return string
     */
    protected function getViewHelperClassName()
    {
        $class = get_class($this);
        $class = str_replace('Tests\\Unit\\', '', $class);
        return substr($class, 0, -4);
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return NodeInterface
     */
    protected function createNode($type, $value)
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

    /**
     * @return AbstractViewHelper
     */
    protected function createInstance()
    {
        $className = $this->getViewHelperClassName();
        /** @var AbstractViewHelper $instance */
        $instance = $this->objectManager->get($className);
        if (true === method_exists($instance, 'injectConfigurationManager')) {
            $cObject = new ContentObjectRenderer();
            $cObject->start(Records::$contentRecordWithoutParentAndWithoutChildren, 'tt_content');
            /** @var ConfigurationManagerInterface $configurationManager */
            $configurationManager = $this->objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
            $configurationManager->setContentObject($cObject);
            $instance->injectConfigurationManager($configurationManager);
        }
        $instance->setRenderingContext($this->objectManager->get(RenderingContext::class));
        $instance->initialize();
        return $instance;
    }

    /**
     * @param array $arguments
     * @param array $variables
     * @param NodeInterface $childNode
     * @param string $extensionName
     * @param string $pluginName
     * @return AbstractViewHelper
     */
    protected function buildViewHelperInstance($arguments = [], $variables = [], $childNode = null, $extensionName = null, $pluginName = null)
    {
        $instance = $this->createInstance();
        foreach ($instance->prepareArguments() as $argumentName => $argumentDefinition) {
            if (!array_key_exists($argumentName, $arguments)) {
                $arguments[$argumentName] = $argumentDefinition->getDefaultValue();
            }
        }
        $node = $this->createViewHelperNode($instance, $arguments);
        /** @var StandardVariableProvider $container */
        $container = $this->objectManager->get(StandardVariableProvider::class);
        if (0 < count($variables)) {
            ProtectedAccess::setProperty($container, 'variables', $variables);
        }

        /** @var ViewHelperVariableContainer $viewHelperContainer */
        $viewHelperContainer = $this->objectManager->get(ViewHelperVariableContainer::class);
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);

        $request->setControllerExtensionName($extensionName ?? 'Flux');
        if (null !== $pluginName) {
            $request->setPluginName($pluginName);
        }
        /** @var Response $response */
        $response = $this->objectManager->get(Response::class);
        /** @var ControllerContext $controllerContext */
        $controllerContext = $this->objectManager->get(ControllerContext::class);
        $controllerContext->setRequest($request);
        $controllerContext->setResponse($response);
        $controllerContext->setUriBuilder($uriBuilder);

        $this->renderingContext->setViewHelperVariableContainer($viewHelperContainer);
        $this->renderingContext->setControllerContext($controllerContext);
        $instance->setRenderingContext($this->renderingContext);
        $instance->setRenderChildrenClosure(function() { return null; });
        $instance->setViewHelperNode($node);
        $instance->setArguments($arguments);
        $renderingContext = $this->renderingContext;
        if ($childNode) {
            $node->addChildNode($childNode);
            if (method_exists($instance, 'setChildNodes')) {
                $instance->setChildNodes([$childNode]);
            }
            $instance->setRenderChildrenClosure(function() use ($renderingContext, $childNode) { return $childNode->evaluate($renderingContext); });
        }
        if (method_exists($instance, 'injectReflectionService')) {
            $instance->injectReflectionService($this->objectManager->get(ReflectionService::class));
        }
        return $instance;
    }

    /**
     * @param array $arguments
     * @param array $variables
     * @param NodeInterface $childNode
     * @param string $extensionName
     * @param string $pluginName
     * @return mixed
     */
    protected function executeViewHelper($arguments = [], $variables = [], $childNode = null, $extensionName = null, $pluginName = null)
    {
        $instance = $this->buildViewHelperInstance($arguments, $variables, $childNode, $extensionName, $pluginName);
        return $instance->initializeArgumentsAndRender();
    }

    /**
     * @param mixed $nodeValue
     * @param array $arguments
     * @param array $variables
     * @param string $extensionName
     * @param string $pluginName
     * @return mixed
     */
    protected function executeViewHelperUsingTagContent($nodeValue, $arguments = [], $variables = [], $extensionName = null, $pluginName = null)
    {
        $context = $this->renderingContext;
        $instance = $this->buildViewHelperInstance($arguments, $variables, null, $extensionName, $pluginName);
        $instance->setRenderChildrenClosure(function() use ($instance, $nodeValue, $context) {
            if (method_exists($instance, 'setChildNodes')
                && (
                    $nodeValue instanceof \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode
                    || $nodeValue instanceof \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface
                )
            ) {
                $instance->setChildNodes([$nodeValue]);
                return $nodeValue->evaluate($context);
            }
            return $nodeValue;
        });
        return $instance->initializeArgumentsAndRender();
    }

    /**
     * @param ViewHelperInterface $instance
     * @param array $arguments
     * @return MockObject|ViewHelperNode
     */
    protected function createViewHelperNode($instance, array $arguments)
    {
        $resolver = $this->getMockBuilder(ViewHelperResolver::class)
            ->setMethods(['getUninitializedViewHelper'])
            ->getMock();
        $this->renderingContext->setViewHelperResolver($resolver);
        $node = $this->getMockBuilder(ViewHelperNode::class)
            ->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->any())->method('getUninitializedViewHelper')->willReturn($instance);
        return $node;
    }

    /**
     * @param string $accessor
     * @return ObjectAccessorNode
     */
    protected function createObjectAccessorNode($accessor) {
        return new ObjectAccessorNode($accessor);
    }

    /**
     * @param null|string $message
     * @param null|integer $code
     */
    protected function expectViewHelperException($message = null, $code = null)
    {
        $this->expectException(Exception::class, $message, $code);
    }
}
