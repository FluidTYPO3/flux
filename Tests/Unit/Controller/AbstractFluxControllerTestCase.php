<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\RenderingContextBuilder;
use FluidTYPO3\Flux\Builder\RequestBuilder;
use FluidTYPO3\Flux\Controller\AbstractFluxController;
use FluidTYPO3\Flux\Controller\ContentController;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Integration\Resolver;
use FluidTYPO3\Flux\Outlet\StandardOutlet;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Http\ResponseFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * Test case for Flux-enabled controllers
 */
abstract class AbstractFluxControllerTestCase extends AbstractTestCase
{
    protected string $extensionName = 'FluidTYPO3.Flux';
    protected string $defaultAction = 'render';
    protected string $extensionKey = 'flux';
    protected string $shortExtensionName = 'Flux';

    /**
     * @var RenderingContextBuilder&MockObject
     */
    protected RenderingContextBuilder $renderingContextBuilder;

    /**
     * @var RequestBuilder&MockObject
     */
    protected RequestBuilder $requestBuilder;

    protected TypoScriptService $typoScriptService;

    protected ProviderResolver $providerResolver;

    protected Resolver $resolver;

    protected function setUp(): void
    {
        $renderingContext = new RenderingContext();

        $this->renderingContextBuilder = $this->getMockBuilder(RenderingContextBuilder::class)
            ->setMethods(['buildRenderingContextFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderingContextBuilder->method('buildRenderingContextFor')->willReturn($renderingContext);

        $this->requestBuilder = $this->getMockBuilder(RequestBuilder::class)
            ->setMethods(['getEnvironmentVariable', 'buildRequestFor'])
            ->getMock();
        $this->requestBuilder->method('getEnvironmentVariable')->willReturn('env');

        $this->typoScriptService = $this->getMockBuilder(TypoScriptService::class)
            ->onlyMethods(['getSettingsForExtensionName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = new Resolver();

        parent::setUp();
    }

    protected function getConstructorArguments(): array
    {
        return [
            $this->renderingContextBuilder,
            $this->requestBuilder,
            $this->getMockBuilder(WorkspacesAwareRecordService::class)->disableOriginalConstructor()->getMock(),
            $this->typoScriptService,
            $this->providerResolver,
            $this->resolver,
        ];
    }

    protected function assertSimpleActionCallsRenderOnView(string $action): void
    {
        $className = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = new $className();
        $view = $this->getMockBuilder(TemplateView::class)->setMethods(['render', 'assign'])->getMock();
        $response = new Response();
        $view->expects($this->once())->method('render')->will($this->returnValue('rendered'));
        $this->setInaccessiblePropertyValue($instance, 'view', $view);
        $this->setInaccessiblePropertyValue($instance, 'response', $response);
        $this->setInaccessiblePropertyValue($instance, 'actionMethodName', $action . 'Action');
        $this->callInaccessibleMethod($instance, 'callActionMethod');
        $output = $response->getcontent();
        $this->assertEquals('rendered', $output);
    }

    protected function getControllerName(): string
    {
        $parts = explode('\\', get_class($this));
        $name = substr(array_pop($parts), 0, -13);
        return $name;
    }

    public function testDefaultActionForwardsToRenderAction(): void
    {
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(['renderAction'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $instance->expects($this->once())->method('renderAction');
        $instance->defaultAction();
    }

    public function testInitializeViewHelperVariableContainer()
    {
        $variableProvider = $this->getMockBuilder(ViewHelperVariableContainer::class)
            ->setMethods(['add'])
            ->getMock();
        $variableProvider->expects(self::atLeastOnce())->method('add');
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(['getRecord'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $instance->expects($this->once())->method('getRecord');
        $this->setInaccessiblePropertyValue(
            $instance,
            'request',
            $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock()
        );
        $this->callInaccessibleMethod($instance, 'initializeViewHelperVariableContainer', $variableProvider);
    }

    public function testCanCreateInstanceOfCustomRegisteredController(): AbstractFluxController
    {
        $instance = $this->createAndTestDummyControllerInstance();
        $this->assertInstanceOf(AbstractFluxController::class, $instance);
        return $instance;
    }

    protected function performDummyRegistration(): void
    {
        Core::registerProviderExtensionKey($this->extensionName, $this->getControllerName());
        $this->assertContains(
            $this->extensionName,
            Core::getRegisteredProviderExtensionKeys($this->getControllerName())
        );
    }

    protected function createAndTestDummyControllerInstance(): AbstractFluxController
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        return $this->getMockBuilder($controllerClassName)
            ->onlyMethods(['getContentObject', 'getServerRequest'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
    }

    protected function createDummyRequestAndResponseForFluxController(string $controllerName = 'Content'): array
    {
        $request = new Request();
        $request->setControllerExtensionName('Flux');
        $request->setControllerActionName($this->defaultAction);
        $request->setControllerName($controllerName);
        $request->setControllerObjectName(ContentController::class);
        $request->setFormat('html');
        $response = new Response();
        return [$request, $response];
    }

    public function testCanGetData(): void
    {
        $instance = $this->testCanCreateInstanceOfCustomRegisteredController();
        $data = $this->callInaccessibleMethod($instance, 'getData');
        $this->assertIsArray($data);
    }

    public function testCanGetRecord(): void
    {
        $contentObjectRenderer = $this->getMockBuilder(ContentObjectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contentObjectRenderer->data = [];

        $instance = $this->testCanCreateInstanceOfCustomRegisteredController();
        $instance->method('getContentObject')->willReturn($contentObjectRenderer);

        $record = $this->callInaccessibleMethod($instance, 'getRecord');
        $this->assertIsArray($record);
    }

    public function testCanGetFluxRecordField(): void
    {
        $instance = $this->testCanCreateInstanceOfCustomRegisteredController();
        $field = $this->callInaccessibleMethod($instance, 'getFluxRecordField');
        $this->assertSame('pi_flexform', $field);
    }

    public function testCanGetFluxTableName(): void
    {
        $instance = $this->testCanCreateInstanceOfCustomRegisteredController();
        $table = $this->callInaccessibleMethod($instance, 'getFluxTableName');
        $this->assertSame('tt_content', $table);
    }

    public function testInitializeActionCallsExpectedMethods(): void
    {
        $subject = $this->getMockBuilder(AbstractFluxController::class)
            ->setMethods(['initializeProvider', 'initializeSettings', 'initializeOverriddenSettings'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $subject->expects(self::once())->method('initializeProvider');
        $subject->expects(self::once())->method('initializeSettings');
        $subject->expects(self::once())->method('initializeOverriddenSettings');
        $this->callInaccessibleMethod($subject, 'initializeAction');
    }

    public function testCanPerformSubRenderingWithNotMatchingExtensionName(): void
    {
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();
        $controllerName = $this->getControllerName();
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();

        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->setMethods(['fillDefaultsByPackageName'])
            ->disableOriginalConstructor()
            ->getMock();

        $renderingContext = $this->getMockBuilder(RenderingContextInterface::class)
            ->setMethods(['getTemplatePaths'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $renderingContext->method('getTemplatePaths')->willReturn($templatePaths);

        $view = $this->getMockBuilder(TemplateView::class)
            ->setMethods(['getRenderingContext', 'render'])
            ->disableOriginalConstructor()
            ->getMock();
        $view->method('getRenderingContext')->willReturn($renderingContext);
        $view->method('render')->willReturn('rendered');

        $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass();

        $instance = $this->getMockBuilder($controllerClassName)
            ->setMethods(['hasSubControllerActionOnForeignController', 'getRecord', 'createHtmlResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $instance->method('hasSubControllerActionOnForeignController')->willReturn(false);
        $instance->method('getRecord')->willReturn(['uid' => 123]);
        $instance->method('createHtmlResponse')->willReturn($response);
        $this->setInaccessiblePropertyValue($instance, 'provider', $provider);
        $this->setInaccessiblePropertyValue($instance, 'extensionName', $this->extensionName);
        $this->setInaccessiblePropertyValue($instance, 'view', $view);
        $this->setInaccessiblePropertyValue(
            $instance,
            'request',
            $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock()
        );
        $instance->injectConfigurationManager($configurationManager);

        $output = $this->callInaccessibleMethod(
            $instance,
            'performSubRendering',
            $this->extensionName,
            $controllerName,
            $this->defaultAction,
            'Content',
            'tx_flux_content'
        );
        self::assertSame($response, $output);
    }

    public function testCanPerformSubRenderingWithWithoutRelay(): void
    {
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();
        $controllerName = $this->getControllerName();
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)
            ->setMethods(['hasSubControllerActionOnForeignController', 'callSubControllerAction', 'createHtmlResponse'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->expects($this->once())
            ->method('hasSubControllerActionOnForeignController')
            ->will($this->returnValue(true));
        $instance->expects($this->once())->method('callSubControllerAction');
        $instance->method('createHtmlResponse')->willReturn($response);
        $this->setInaccessiblePropertyValue($instance, 'extensionName', $this->extensionName);
        $this->callInaccessibleMethod(
            $instance,
            'performSubRendering',
            $this->extensionName,
            $controllerName,
            $this->defaultAction,
            'Content',
            'tx_flux_content'
        );
    }

    public function testResolveView(): void
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $view = $this->getMockBuilder(TemplateView::class)
            ->addMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $view->setRenderingContext(new RenderingContext());
        $instance = $this->getMockBuilder($controllerClassName)
            ->onlyMethods(
                [
                    'initializeProvider',
                    'initializeSettings',
                    'initializeOverriddenSettings',
                    'initializeViewVariables',
                    'initializeViewHelperVariableContainer',
                    'getRecord'
                ]
            )
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->injectConfigurationManager($this->getMockBuilder(ConfigurationManagerInterface::class)->getMock());
        $instance->expects(self::once())->method('initializeViewHelperVariableContainer');
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMock();
        $this->setInaccessiblePropertyValue($instance, 'provider', $provider);
        if (class_exists(ControllerContext::class)) {
            $controllerContext = new ControllerContext();
            $controllerContext->setRequest(new Request());
            $this->setInaccessiblePropertyValue($instance, 'controllerContext', $controllerContext);
        }
        $instance->method('getRecord')->willReturn(['uid' => 1]);

        GeneralUtility::addInstance(TemplateView::class, $view);

        $this->callInaccessibleMethod($instance, 'resolveView');
    }

    public function testResolveViewWithTemplateSource(): void
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $view = $this->getMockBuilder(TemplateView::class)
            ->addMethods(['setTemplateSource'])
            ->disableOriginalConstructor()
            ->getMock();
        $view->setRenderingContext(new RenderingContext());
        $instance = $this->getMockBuilder($controllerClassName)
            ->onlyMethods(
                [
                    'initializeProvider',
                    'initializeSettings',
                    'initializeOverriddenSettings',
                    'initializeViewVariables',
                    'initializeViewHelperVariableContainer',
                    'getRecord'
                ]
            )
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->injectConfigurationManager($this->getMockBuilder(ConfigurationManagerInterface::class)->getMock());
        $instance->expects(self::once())->method('initializeViewHelperVariableContainer');
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMock();
        $this->setInaccessiblePropertyValue($instance, 'provider', $provider);
        if (class_exists(ControllerContext::class)) {
            $controllerContext = new ControllerContext();
            $controllerContext->setRequest(new Request());
            $this->setInaccessiblePropertyValue($instance, 'controllerContext', $controllerContext);
        }
        $instance->method('getRecord')->willReturn(['uid' => 1]);

        GeneralUtility::addInstance(TemplatePaths::class, new TemplatePaths());
        GeneralUtility::addInstance(TemplateView::class, $view);

        $this->callInaccessibleMethod($instance, 'resolveView');
    }

    public function testCanInitializeSettings(): void
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)
            ->setMethods(['getRecord'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
        $provider = $this->getMockBuilder(Provider::class)
            ->setMethods(['getControllerExtensionKeyFromRecord', 'getFlexFormValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->atLeastOnce())
            ->method('getControllerExtensionKeyFromRecord')
            ->with($row)
            ->will($this->returnValue($this->extensionKey));
        $provider->expects($this->once())->method('getFlexFormValues')->with($row)->will($this->returnValue([]));
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getPluginName'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
        $this->setInaccessiblePropertyValue($instance, 'request', $request);
        $this->setInaccessiblePropertyValue($instance, 'provider', $provider);
        $this->setInaccessiblePropertyValue(
            $instance,
            'configurationManager',
            $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass()
        );
        $this->callInaccessibleMethod($instance, 'initializeSettings');
    }

    /**
     * @dataProvider getInitializeOverriddenSettingsTestValues
     */
    public function testInitializeOverriddenSettings(array $data, array $settings)
    {
        $record = ['uid' => 1];
        $provider = $this->getMockBuilder(Provider::class)
            ->setMethods(['getControllerExtensionKeyFromRecord'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->once())->method('getControllerExtensionKeyFromRecord')->with($record);
        $mock = $this->getMockBuilder(AbstractFluxController::class)
            ->setMethods(['getRecord'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $mock->expects($this->once())->method('getRecord')->willReturn($record);

        if (($settings['useTypoScript'] ?? false) || ($data['settings']['useTypoScript'] ?? false)) {
            $this->typoScriptService->expects($this->once())
                ->method('getSettingsForExtensionName');
        } else {
            $this->typoScriptService->expects($this->never())
                ->method('getSettingsForExtensionName');
        }
        $this->setInaccessiblePropertyValue($mock, 'data', $data);
        $this->setInaccessiblePropertyValue($mock, 'settings', $settings);
        $this->setInaccessiblePropertyValue($mock, 'provider', $provider);
        $this->callInaccessibleMethod($mock, 'initializeOverriddenSettings');
    }

    public function getInitializeOverriddenSettingsTestValues(): array
    {
        return [
            [['settings' => []], [], []],
            [['settings' => []], ['useTypoScript' => 1]],
            [['settings' => ['useTypoScript' => 1]], []],
        ];
    }

    public function testInitializeProvider(): void
    {
        $provider = $this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock();
        $this->providerResolver->expects($this->once())
            ->method('resolvePrimaryConfigurationProvider')
            ->willReturn($provider);
        $mock = $this->getMockBuilder(AbstractFluxController::class)
            ->setMethods(
                ['getRecord', 'getFluxTableName', 'getFluxRecordField']
            )
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $mock->expects($this->once())->method('getRecord')->willReturn([]);
        $mock->expects($this->once())->method('getFluxTableName')->willReturn('table');
        $mock->expects($this->once())->method('getFluxRecordField')->willReturn('field');
        $this->callInaccessibleMethod($mock, 'initializeProvider');
    }

    public function testInitializeProviderThrowsExceptionIfNoProviderResolved(): void
    {
        $this->providerResolver->expects($this->once())
            ->method('resolvePrimaryConfigurationProvider')
            ->willReturn(null);
        $mock = $this->getMockBuilder(AbstractFluxController::class)
            ->setMethods(
                ['getRecord', 'getFluxTableName', 'getFluxRecordField']
            )
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $mock->expects($this->once())->method('getRecord')->willReturn([]);
        $mock->expects($this->once())->method('getFluxTableName')->willReturn('table');
        $mock->expects($this->once())->method('getFluxRecordField')->willReturn('field');

        $this->expectException('RuntimeException');
        $this->callInaccessibleMethod($mock, 'initializeProvider');
    }

    public function testCallingRenderActionExecutesExpectedMethodsOnNestedObjects(): void
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)
            ->setMethods(['getRecord', 'performSubRendering', 'getServerRequest'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->method('getServerRequest')->willReturn(
            $this->getMockBuilder(ServerRequestInterface::class)->getMockForAbstractClass()
        );
        $instance->expects($this->once())->method('getRecord')->willReturn($row);
        $instance->expects($this->once())
            ->method('performSubRendering')
            ->with($this->extensionKey, 'Void', 'default', 'void', 'tx_flux_void')
            ->willReturn('test');
        $provider = $this->getMockBuilder(Provider::class)
            ->setMethods(['getControllerExtensionKeyFromRecord'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->atLeastOnce())
            ->method('getControllerExtensionKeyFromRecord')
            ->with($row)
            ->willReturn($this->extensionKey);
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getPluginName', 'getControllerName'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
        $request->expects($this->once())->method('getControllerName')->will($this->returnValue('Void'));
        $this->setInaccessiblePropertyValue($instance, 'request', $request);
        $this->setInaccessiblePropertyValue($instance, 'provider', $provider);
        $result = $instance->renderAction();
        $this->assertEquals($result, 'test');
    }

    public function testPerformSubRenderingCallsViewRenderOnNativeTarget(): void
    {
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();
        $controllerName = $this->getControllerName();
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)
            ->setMethods(['callSubControllerAction', 'createHtmlResponse'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->expects($this->never())->method('callSubControllerAction');
        $instance->method('createHtmlResponse')->willReturn($response);
        $view = $this->getMockBuilder(TemplateView::class)
            ->onlyMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects($this->once())->method('render')->will($this->returnValue('test'));
        $this->setInaccessiblePropertyValue($instance, 'extensionName', $this->shortExtensionName);
        $this->setInaccessiblePropertyValue($instance, 'view', $view);
        $result = $this->callInaccessibleMethod(
            $instance,
            'performSubRendering',
            $this->shortExtensionName,
            $controllerName,
            $this->defaultAction,
            'Content',
            'tx_flux_content'
        );
        $this->assertEquals($response, $result);
    }

    public function testCallingSubControllerActionExecutesExpectedMethodsOnNestedObjects(): void
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)
            ->setMethods(
                [
                    'processRequest',
                    'initializeViewHelperVariableContainer',
                    'createHtmlResponse',
                    'getServerRequest',
                ]
            )
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();

        $instance->method('getServerRequest')->willReturn(
            $this->getMockBuilder(ServerRequestInterface::class)->getMockForAbstractClass()
        );

        $request = $this->getMockBuilder(RequestInterface::class)->getMock();

        $this->requestBuilder->method('buildRequestFor')->willReturn($request);

        $this->setInaccessiblePropertyValue($instance, 'request', $request);
        if (method_exists($instance, 'injectResponseFactory')) {
            $responseBody = $this->getMockBuilder(StreamInterface::class)->getMockForAbstractClass();
            $responseBody->method('getContents')->willReturn('test');
            $responseClassName = ResponseInterface::class;
            $response = $this->getMockBuilder($responseClassName)->setMethods(['getBody'])->getMockForAbstractClass();
            $response->method('getBody')->willReturn($responseBody);
            $responseFactory = $this->getMockBuilder(ResponseFactory::class)
                ->setMethods(['createResponse'])
                ->disableOriginalConstructor()
                ->getMock();
            $responseFactory->method('createResponse')->willReturn($response);
            $instance->injectResponseFactory($responseFactory);
        } else {
            $responseClassName = Response::class;
            $response = $this->getMockBuilder($responseClassName)->setMethods(['getContent'])->getMock();
            $response->expects($this->once())->method('getContent')->willReturn('test');
            GeneralUtility::addInstance(Response::class, $response);
        }
        $instance->expects($this->once())->method('processRequest')->willReturn($response);
        $instance->method('createHtmlResponse')->willReturn($response);

        GeneralUtility::addInstance($controllerClassName, $instance);

        $result = $this->callInaccessibleMethod(
            $instance,
            'callSubControllerAction',
            $this->shortExtensionName,
            $controllerClassName,
            'render',
            'Content',
            'tx_flux_content'
        );
        $this->assertEquals('test', $result);
    }

    public function testCanInitializeViewVariables(): void
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $data = ['test' => 'test'];
        $variables = ['foo' => 'bar'];
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $instance = $this->getMockBuilder($controllerClassName)
            ->onlyMethods(['getRecord', 'getServerRequest'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->method('getServerRequest')->willReturn(
            $this->getMockBuilder(ServerRequestInterface::class)->getMockForAbstractClass()
        );
        $instance->injectConfigurationManager(
            $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass()
        );
        $instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
        $view = $this->getMockBuilder(TemplateView::class)
            ->onlyMethods(['assign', 'assignMultiple'])
            ->disableOriginalConstructor()
            ->getMock();
        GeneralUtility::addInstance(TemplateView::class, $view);

        $provider = $this->getMockBuilder(Provider::class)
            ->onlyMethods(['getTemplateVariables'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->once())
            ->method('getTemplateVariables')
            ->with($row)
            ->will($this->returnValue($variables));
        $view->expects($this->atLeastOnce())->method('assignMultiple');
        $view->expects($this->atLeastOnce())->method('assign');
        $this->setInaccessiblePropertyValue($instance, 'provider', $provider);
        $this->setInaccessiblePropertyValue($instance, 'data', $data);
        $this->callInaccessibleMethod($instance, 'initializeViewVariables', $view);
    }

    public function testCanUseTypoScriptSettingsInsteadOfFlexFormDataWhenRequested(): void
    {
        $instance = $this->testCanCreateInstanceOfCustomRegisteredController();
        $instance->method('getServerRequest')->willReturn(
            $this->getMockBuilder(ServerRequestInterface::class)->getMockForAbstractClass()
        );
        $contentObjectRenderer = $this->getMockBuilder(ContentObjectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contentObjectRenderer->data = [];
        $instance->method('getContentObject')->willReturn($contentObjectRenderer);
        $provider = $this->getMockBuilder(Provider::class)
            ->onlyMethods(['getFlexFormValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->method('getFlexFormValues')->willReturn(['settings' => ['useTypoScript' => 1]]);
        $this->typoScriptService->method('getSettingsForExtensionName')->willReturn(['foo' => 'bar']);
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
        $settings = [
            'useTypoScript' => true
        ];
        $previousSettings = $this->getInaccessiblePropertyValue($instance, 'settings');
        $this->setInaccessiblePropertyValue($instance, 'settings', $settings);
        $this->callInaccessibleMethod($instance, 'initializeProvider');
        $this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
        $overriddenSettings = $this->getInaccessiblePropertyValue($instance, 'settings');
        $this->assertNotSame($previousSettings, $overriddenSettings);
    }

    public function testCanUseFlexFormDataWhenPresent(): void
    {
        $instance = $this->testCanCreateInstanceOfCustomRegisteredController();
        $contentObjectRenderer = $this->getMockBuilder(ContentObjectRenderer::class)
            ->disableOriginalConstructor()
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $contentObjectRenderer->data = [];
        $instance->method('getContentObject')->willReturn($contentObjectRenderer);
        $instance->injectConfigurationManager(
            $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass()
        );

        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn(
            $this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock()
        );
        $settings = [
            'settings' => [
                'test' => 'test'
            ]
        ];
        $this->setInaccessiblePropertyValue($instance, 'data', $settings);
        $this->callInaccessibleMethod($instance, 'initializeProvider');
        $this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
        $overriddenSettings = $this->getInaccessiblePropertyValue($instance, 'settings');
        $this->assertEquals($settings['settings']['test'], $overriddenSettings['test']);
    }

    public function testRenderActionReturnsEmptyStringWithoutProvider(): void
    {
        $subject = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->expectExceptionCode(1672082347);
        $subject->renderAction();
    }

    public function testGetRecordThrowsExceptionIfContentObjectIsEmpty(): void
    {
        $subject = $this->getMockBuilder(AbstractFluxController::class)
            ->onlyMethods(['getContentObject'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $subject->method('getContentObject')->willReturn(null);

        self::expectExceptionCode(1666538343);
        $subject->getRecord();
    }
}
