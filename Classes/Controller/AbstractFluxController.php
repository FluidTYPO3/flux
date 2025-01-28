<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\RenderingContextBuilder;
use FluidTYPO3\Flux\Builder\RequestBuilder;
use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Integration\NormalizedData\DataAccessTrait;
use FluidTYPO3\Flux\Integration\Resolver;
use FluidTYPO3\Flux\Provider\Interfaces\ControllerProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FluidProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ContentObjectFetcher;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerInterface;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * Abstract Flux-enabled controller
 *
 * Extends a traditional ActionController with new services and methods
 * to ease interaction with Flux forms. Is not required as subclass for
 * Controllers rendering records associated with Flux - all it does is
 * ease the interaction by providing a common API.
 */
abstract class AbstractFluxController extends ActionController
{
    use DataAccessTrait;

    protected string $extensionName = 'FluidTYPO3.Flux';
    protected ?string $fluxRecordField = 'pi_flexform';
    protected ?string $fluxTableName = 'tt_content';
    protected array $data = [];

    protected RenderingContextBuilder $renderingContextBuilder;
    protected RequestBuilder $requestBuilder;
    protected WorkspacesAwareRecordService $recordService;
    protected TypoScriptService $typoScriptService;
    protected ProviderResolver $providerResolver;
    protected Resolver $resolver;
    protected ViewBuilder $viewBuilder;
    protected ?ControllerProviderInterface $provider = null;

    public function __construct(
        RenderingContextBuilder $renderingContextBuilder,
        RequestBuilder $requestBuilder,
        WorkspacesAwareRecordService $recordService,
        TypoScriptService $typoScriptService,
        ProviderResolver $providerResolver,
        Resolver $resolver,
        ViewBuilder $viewBuilder
    ) {
        $this->renderingContextBuilder = $renderingContextBuilder;
        $this->requestBuilder = $requestBuilder;
        $this->recordService = $recordService;
        $this->typoScriptService = $typoScriptService;
        $this->providerResolver = $providerResolver;
        $this->resolver = $resolver;
        $this->viewBuilder = $viewBuilder;

        /** @var Arguments $arguments */
        $arguments = GeneralUtility::makeInstance(Arguments::class);
        $this->arguments = $arguments;

        /** @var Request $request */
        $request = $requestBuilder->buildRequestFor(
            $this->extensionName,
            'Dummy',
            'render',
            '',
            [],
        );
        $this->request = $request;
    }

    protected function initializeSettings(): void
    {
        if ($this->provider === null) {
            return;
        }
        $row = $this->getRecord();
        $extensionKey = $this->provider->getControllerExtensionKeyFromRecord($row);
        $extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
        $pluginName = $this->request->getPluginName();
        $this->settings = RecursiveArrayUtility::merge(
            (array) $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                $extensionName,
                $pluginName
            ),
            (array) $this->settings
        );

        if ($this->provider instanceof DataStructureProviderInterface) {
            $this->data = $this->provider->getFlexFormValues($row);
        }

        $overrides = HookHandler::trigger(
            HookHandler::CONTROLLER_SETTINGS_INITIALIZED,
            [
                'settings' => $this->settings,
                'data' => $this->data,
                'record' => $row,
                'provider' => $this->provider,
                'controller' => $this,
                'request' => $this->request,
                'extensionKey' => $extensionKey
            ]
        );
        $this->data = $overrides['data'];
        $this->settings = $overrides['settings'];
        $this->provider = $overrides['provider'];
    }

    protected function initializeOverriddenSettings(): void
    {
        if ($this->provider === null) {
            return;
        }
        $row = $this->getRecord();
        $extensionKey = $this->provider->getControllerExtensionKeyFromRecord($row);
        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKey);
        if (is_array($this->data['settings'] ?? null)) {
            // a "settings." array is defined in the flexform configuration - extract it, use as "settings" in template
            // as well as the internal $this->settings array as per expected Extbase behavior.
            $this->settings = RecursiveArrayUtility::merge($this->settings, $this->data['settings'] ?? []);
        }
        if ($this->settings['useTypoScript'] ?? false) {
            // an override shared by all Flux enabled controllers: setting plugin.tx_EXTKEY.settings.useTypoScript = 1
            // will read the "settings" array from that location instead - thus excluding variables from the flexform
            // which are still available as $this->data but no longer available automatically in the template.
            $this->settings = $this->typoScriptService->getSettingsForExtensionName($extensionKey);
        }
    }

    protected function initializeProvider(): void
    {
        $row = $this->getRecord();
        $table = (string) $this->getFluxTableName();
        $field = $this->getFluxRecordField();
        $provider = $this->providerResolver->resolvePrimaryConfigurationProvider(
            $table,
            $field,
            $row,
            null,
            [ControllerProviderInterface::class]
        );
        if ($provider === null) {
            throw new \RuntimeException(
                'Unable to resolve a ConfigurationProvider, but controller indicates it is a Flux-enabled ' .
                'Controller - this is a grave error and indicates that EXT: ' . $this->extensionName . ' itself is ' .
                'broken - or that EXT:' . $this->extensionName . ' has been overridden by another implementation ' .
                'which is broken. The controller that caused this error was ' . get_class($this) . '".',
                1377458581
            );
        }
        $this->provider = $provider;
    }

    protected function initializeViewVariables(ViewInterface $view): void
    {
        $contentObject = $this->getContentObject();
        $row = $this->getRecord();

        $view->assign('contentObject', $contentObject);
        $view->assign('data', $contentObject instanceof ContentObjectRenderer ? $contentObject->data : null);
        if ($this->provider instanceof FluidProviderInterface) {
            $view->assignMultiple($this->provider->getTemplateVariables($row));
        }
        $view->assignMultiple($this->data);
        $view->assign('settings', $this->settings);
        $view->assign('provider', $this->provider);
        $view->assign('record', $row);

        HookHandler::trigger(
            HookHandler::CONTROLLER_VARIABLES_ASSIGNED,
            [
                'view' => $view,
                'record' => $row,
                'settings' => $this->settings,
                'provider' => $this->provider,
                'contentObject' => $contentObject,
            ]
        );
    }

    protected function initializeViewHelperVariableContainer(
        ViewHelperVariableContainer $viewHelperVariableContainer
    ): void {
        $viewHelperVariableContainer->add(FormViewHelper::class, 'provider', $this->provider);
        $viewHelperVariableContainer->add(
            FormViewHelper::class,
            'extensionName',
            $this->request->getControllerExtensionKey()
        );
        $viewHelperVariableContainer->add(
            FormViewHelper::class,
            'pluginName',
            $this->request->getPluginName()
        );
        $viewHelperVariableContainer->add(FormViewHelper::class, 'record', $this->getRecord());
    }

    protected function initializeAction(): void
    {
        $this->initializeProvider();
        $this->initializeSettings();
        $this->initializeOverriddenSettings();
    }

    protected function resolveView(): ViewInterface
    {
        if (!$this->provider instanceof ControllerProviderInterface) {
            throw new \RuntimeException(
                get_class($this) . ' cannot handle record; no ControllerProviderInterface could be resolved',
                1672082347
            );
        }
        /** @var TemplateView $view */
        $view = GeneralUtility::makeInstance(TemplateView::class);
        $record = $this->getRecord();
        $extensionKey = ExtensionNamingUtility::getExtensionKey(
            $this->provider->getControllerExtensionKeyFromRecord($record)
        );
        $extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
        $controllerActionName = $this->provider->getControllerActionFromRecord($record);

        $templatePaths = $this->viewBuilder->buildTemplatePaths($extensionKey);

        /** @var RenderingContextInterface $renderingContext */
        $renderingContext = $view->getRenderingContext();

        $renderingContext = $this->renderingContextBuilder->buildRenderingContextFor(
            $extensionName,
            $this->resolver->resolveControllerNameFromControllerClassName(get_class($this)),
            $controllerActionName,
            $this->provider->getPluginName() ?? $this->provider->getControllerNameFromRecord($record)
        );
        if (method_exists($renderingContext, 'setRequest')) {
            $renderingContext->setRequest($this->request);
        }
        $renderingContext->setTemplatePaths($templatePaths);
        $renderingContext->setControllerAction($controllerActionName);

        $view->setRenderingContext($renderingContext);

        $this->initializeViewVariables($view);
        $this->initializeViewHelperVariableContainer($renderingContext->getViewHelperVariableContainer());
        HookHandler::trigger(
            HookHandler::CONTROLLER_VIEW_INITIALIZED,
            [
                'view' => $view,
                'request' => $this->request,
                'provider' => $this->provider,
                'controller' => $this,
                'extensionKey' => $extensionKey
            ]
        );
        return $view;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    protected function createHtmlResponse(string $html = null)
    {
        if (method_exists($this, 'htmlResponse')) {
            return parent::htmlResponse($html);
        }
        $response = clone $this->response;
        $response->setContent((string) $html);
        return $response;
    }

    /**
     * Default action, proxy for "render". Added in order to
     * capture requests which use the Fluid-native "default"
     * action name when no specific action name is set in the
     * request. The "default" action is also returned by
     * vanilla Provider instances when registering them for
     * content object types or other ad-hoc registrations.
     *
     * @return \Psr\Http\Message\ResponseInterface|Response|ResponseInterface
     */
    public function defaultAction()
    {
        return $this->renderAction();
    }

    /**
     * Render content
     *
     * @return \Psr\Http\Message\ResponseInterface|Response|ResponseInterface
     */
    public function renderAction()
    {
        if (!$this->provider instanceof ControllerProviderInterface) {
            throw new \RuntimeException(
                get_class($this) . ' cannot handle record; no ControllerProviderInterface could be resolved',
                1672082347
            );
        }
        $row = $this->getRecord();
        $extensionKey = $this->provider->getControllerExtensionKeyFromRecord($row);
        $extensionSignature = ExtensionNamingUtility::getExtensionSignature($extensionKey);
        $pluginName = $this->request->getPluginName();
        $pluginSignature = strtolower('tx_' . $extensionSignature . '_' . $pluginName);
        $controllerExtensionKey = $this->provider->getControllerExtensionKeyFromRecord($row);
        $requestActionName = $this->resolveOverriddenFluxControllerActionNameFromRequestParameters($pluginSignature);
        $controllerActionName = $this->provider->getControllerActionFromRecord($row);
        $actualActionName = null !== $requestActionName ? $requestActionName : $controllerActionName;
        $controllerName = $this->request->getControllerName();

        return $this->performSubRendering(
            $controllerExtensionKey,
            $controllerName,
            $actualActionName,
            $pluginName,
            $pluginSignature
        );
    }

    protected function resolveOverriddenFluxControllerActionNameFromRequestParameters(string $pluginSignature): ?string
    {
        return $this->getServerRequest()->getQueryParams()[$pluginSignature]['action'] ?? null;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface|Response|ResponseInterface
     */
    protected function performSubRendering(
        string $extensionName,
        string $controllerName,
        string $actionName,
        string $pluginName,
        string $pluginSignature
    ) {
        if (property_exists($this, 'responseFactory') && $this->responseFactory instanceof ResponseFactoryInterface) {
            $response = $this->responseFactory->createResponse();
        } else {
            /** @var ResponseInterface $response */
            $response = GeneralUtility::makeInstance(Response::class);
        }
    
        $shouldRelay = $this->hasSubControllerActionOnForeignController($extensionName, $controllerName, $actionName);
        $foreignControllerClass = null;
        $content = null;
        if (!$shouldRelay) {
            if ($this->provider instanceof FluidProviderInterface) {
                $templatePathAndFilename = $this->provider->getTemplatePathAndFilename($this->getRecord());
                $vendorLessExtensionName = ExtensionNamingUtility::getExtensionName($extensionName);
                /** @var TemplateView $view */
                $view = $this->view;
                $renderingContext = $view->getRenderingContext();
                $paths = $renderingContext->getTemplatePaths();

                if (method_exists($this->request, 'setControllerExtensionName')) {
                    $this->request->setControllerExtensionName($vendorLessExtensionName);
                }

                if (method_exists($this->request, 'withControllerExtensionName')) {
                    $this->request = $this->request->withControllerExtensionName($vendorLessExtensionName);
                }

                if (method_exists($renderingContext, 'setRequest')) {
                    $renderingContext->setRequest($this->request);
                }

                $this->configurationManager->setConfiguration(
                    array_merge(
                        (array) $this->configurationManager->getConfiguration(
                            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
                            $vendorLessExtensionName
                        ),
                        [
                            'extensionName' => $vendorLessExtensionName,
                        ]
                    )
                );
                $paths->fillDefaultsByPackageName(
                    GeneralUtility::camelCaseToLowerCaseUnderscored($vendorLessExtensionName)
                );
                $paths->setTemplatePathAndFilename((string) $templatePathAndFilename);
            }
            $content = $this->view->render();
        } else {
            $foreignControllerClass = $this->resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName(
                $extensionName,
                $controllerName
            );
            $content = $this->callSubControllerAction(
                $extensionName,
                $foreignControllerClass ?? static::class,
                $actionName,
                $pluginName,
                $pluginSignature
            );
        }
        $content = HookHandler::trigger(
            HookHandler::CONTROLLER_AFTER_RENDERING,
            [
                'view' => $this->view,
                'content' => $content,
                'request' => $this->request,
                'response' => $response,
                'extensionName' => $extensionName,
                'controllerClassName' => $foreignControllerClass,
                'controllerActionName' => $actionName
            ]
        )['content'];

        return $content instanceof \Psr\Http\Message\ResponseInterface || $content instanceof ResponseInterface
            ? $content
            : $this->createHtmlResponse($content);
    }

    protected function hasSubControllerActionOnForeignController(
        string $extensionName,
        string $controllerName,
        string $actionName
    ): bool {
        $potentialControllerClassName = $this->resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName(
            $extensionName,
            $controllerName
        );
        if ($potentialControllerClassName === null) {
            return false;
        }
        $isNotThis = get_class($this) !== $potentialControllerClassName;
        $isValidController = class_exists($potentialControllerClassName);
        return ($isNotThis && $isValidController
            && method_exists($potentialControllerClassName, $actionName . 'Action'));
    }

    /**
     * @param class-string $controllerClassName
     * @return \Psr\Http\Message\ResponseInterface|ResponseInterface|null
     */
    protected function callSubControllerAction(
        string $extensionName,
        string $controllerClassName,
        string $controllerActionName,
        string $pluginName,
        string $pluginSignature
    ) {
        $serverRequest = $this->getServerRequest();
        $arguments = $serverRequest->getQueryParams()[$pluginSignature] ?? [];
        $arguments = array_merge($arguments, ((array) $serverRequest->getParsedBody())[$pluginSignature] ?? []);

        $request = $this->requestBuilder->buildRequestFor(
            $extensionName,
            $this->resolver->resolveControllerNameFromControllerClassName(
                $controllerClassName
            ),
            $controllerActionName,
            $pluginName,
            $arguments
        );

        /** @var ControllerInterface $potentialControllerInstance */
        $potentialControllerInstance = GeneralUtility::makeInstance($controllerClassName);

        if (property_exists($this, 'responseFactory') && $this->responseFactory instanceof ResponseFactoryInterface) {
            /** @var ResponseInterface\ $response */
            $response = $this->responseFactory->createResponse();
        } else {
            /** @var ResponseInterface $response */
            $response = GeneralUtility::makeInstance(Response::class);
        }

        try {
            HookHandler::trigger(
                HookHandler::CONTROLLER_BEFORE_REQUEST,
                [
                    'request' => $this->request,
                    'response' => $response,
                    'extensionName' => $extensionName,
                    'controllerClassName' => $controllerClassName,
                    'controllerActionName' => $controllerActionName
                ]
            );

            /** @var \Psr\Http\Message\ResponseInterface|ResponseInterface|null $responseFromCall */
            $responseFromCall = $potentialControllerInstance->processRequest($request, $response);
            if ($responseFromCall) {
                $response = $responseFromCall;
            }
        } catch (StopActionException $error) {
            // intentionally left blank
        }
        HookHandler::trigger(
            HookHandler::CONTROLLER_AFTER_REQUEST,
            [
                'request' => $this->request,
                'response' => $response,
                'extensionName' => $extensionName,
                'controllerClassName' => $controllerClassName,
                'controllerActionName' => $controllerActionName
            ]
        );

        return $response;
    }

    /**
     * Get the data stored in a record's Flux-enabled field,
     * i.e. the variables of the Flux template as configured in this
     * particular record.
     */
    protected function getData(): array
    {
        return $this->data;
    }

    protected function getFluxRecordField(): ?string
    {
        return $this->fluxRecordField;
    }

    protected function getFluxTableName(): ?string
    {
        return $this->fluxTableName;
    }

    public function getRecord(): array
    {
        $contentObject = $this->getContentObject();
        if ($contentObject === null) {
            throw new \UnexpectedValueException(
                "Record of table " . $this->getFluxTableName() . ' not found',
                1666538343
            );
        }

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '<')) {
            /** @var TypoScriptFrontendController|null $tsfe */
            $tsfe = $GLOBALS['TSFE'] ?? null;
        } else {
            $tsfe = $contentObject->getTypoScriptFrontendController();
        }
        if ($tsfe === null) {
            throw new \UnexpectedValueException(
                "Record of table " . $this->getFluxTableName() . ' not found',
                1729864782
            );
        }

        [$table, $recordUid] = GeneralUtility::trimExplode(
            ':',
            $tsfe->currentRecord ?: $contentObject->currentRecord
        );
        $record = $this->recordService->getSingle($table, '*', (integer) $recordUid);
        if ($record === null) {
            throw new \UnexpectedValueException(
                "Record of table " . $this->getFluxTableName() . ' not found',
                1729864698
            );
        }
        
        if (is_array($record)) {
            $record = $tsfe->sys_page->getLanguageOverlay($table, $record);
        }

        if ($record['_LOCALIZED_UID'] ?? false) {
            $record = array_merge(
                $record,
                $this->recordService->getSingle(
                    (string) $this->getFluxTableName(),
                    '*',
                    $record['_LOCALIZED_UID']
                ) ?? $record
            );
        }
        return $record;
    }

    protected function getContentObject(): ?ContentObjectRenderer
    {
        return ContentObjectFetcher::resolve($this->configurationManager);
    }

    protected function getServerRequest(): ServerRequestInterface
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        return $request;
    }
}
