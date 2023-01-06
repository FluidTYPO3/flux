<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Integration\NormalizedData\DataAccessTrait;
use FluidTYPO3\Flux\Provider\Interfaces\ControllerProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FluidProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\Utility\RenderingContextBuilder;
use FluidTYPO3\Flux\Utility\RequestBuilder;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use Psr\Http\Message\ResponseFactoryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerInterface;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

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

    protected FluxService $configurationService;
    protected ?ControllerProviderInterface $provider = null;

    public function __construct()
    {
        /** @var FluxService $configurationService */
        $configurationService = GeneralUtility::makeInstance(FluxService::class);
        $this->configurationService = $configurationService;

        /** @var Arguments $arguments */
        $arguments = GeneralUtility::makeInstance(Arguments::class);
        $this->arguments = $arguments;

        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = GeneralUtility::makeInstance(RequestBuilder::class);

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
            $this->settings = RecursiveArrayUtility::merge($this->settings, $this->data['settings']);
        }
        if ($this->settings['useTypoScript'] ?? false) {
            // an override shared by all Flux enabled controllers: setting plugin.tx_EXTKEY.settings.useTypoScript = 1
            // will read the "settings" array from that location instead - thus excluding variables from the flexform
            // which are still available as $this->data but no longer available automatically in the template.
            $this->settings = $this->configurationService->getSettingsForExtensionName($extensionKey);
        }
    }

    protected function initializeProvider(): void
    {
        $row = $this->getRecord();
        $table = (string) $this->getFluxTableName();
        $field = $this->getFluxRecordField();
        $provider = $this->configurationService->resolvePrimaryConfigurationProvider(
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

    protected function initializeViewVariables(\TYPO3Fluid\Fluid\View\ViewInterface $view): void
    {
        $row = $this->getRecord();
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

    protected function resolveView(): \TYPO3Fluid\Fluid\View\ViewInterface
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

        /** @var TemplatePaths $templatePaths */
        $templatePaths = GeneralUtility::makeInstance(TemplatePaths::class, $extensionKey);

        /** @var RenderingContextBuilder $renderingContextBuilder */
        $renderingContextBuilder = GeneralUtility::makeInstance(RenderingContextBuilder::class);

        /** @var RenderingContextInterface $renderingContext */
        $renderingContext = $view->getRenderingContext();

        $renderingContext = $renderingContextBuilder->buildRenderingContextFor(
            $extensionName,
            $this->configurationService->getResolver()->resolveControllerNameFromControllerClassName(get_class($this)),
            $controllerActionName
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
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public function defaultAction()
    {
        return $this->renderAction();
    }

    /**
     * Render content
     *
     * @return \Psr\Http\Message\ResponseInterface|Response
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
        /** @var string[] $requestParameters */
        $requestParameters = (array) GeneralUtility::_GET($pluginSignature);
        $overriddenControllerActionName = isset($requestParameters['action']) ? $requestParameters['action'] : null;
        return $overriddenControllerActionName;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface|Response
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
                $paths = $view->getRenderingContext()->getTemplatePaths();

                if (method_exists($this->request, 'setControllerExtensionName')) {
                    $this->request->setControllerExtensionName($vendorLessExtensionName);
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
            $foreignControllerClass = $this->configurationService
                ->getResolver()
                ->resolveFluxControllerClassNameByExtensionKeyAndControllerName(
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

        return $this->createHtmlResponse($content);
    }

    protected function hasSubControllerActionOnForeignController(
        string $extensionName,
        string $controllerName,
        string $actionName
    ): bool {
        $potentialControllerClassName = $this->configurationService->getResolver()
            ->resolveFluxControllerClassNameByExtensionKeyAndControllerName(
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
     */
    protected function callSubControllerAction(
        string $extensionName,
        string $controllerClassName,
        string $controllerActionName,
        string $pluginName,
        string $pluginSignature
    ): string {
        $post = GeneralUtility::_POST($pluginSignature);
        $arguments = (array) (true === is_array($post) ? $post : GeneralUtility::_GET($pluginSignature));
        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = GeneralUtility::makeInstance(RequestBuilder::class);
        $request = $requestBuilder->buildRequestFor(
            $extensionName,
            $this->configurationService->getResolver()->resolveControllerNameFromControllerClassName(
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
            /** @var Response $response */
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

            /** @var Response|null $responseFromCall */
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
        if (method_exists($response, 'getContent')) {
            return $response->getContent();
        }
        if (method_exists($response, 'getBody')) {
            $response->getBody()->rewind();
            return $response->getBody()->getContents();
        }
        return '';
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
        $contentObject = $this->configurationManager->getContentObject();
        if ($contentObject === null) {
            throw new \UnexpectedValueException(
                "Record of table " . $this->getFluxTableName() . ' not found',
                1666538343
            );
        }
        return $contentObject->data;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface|string|Response
     */
    public function outletAction()
    {
        $record = $this->getRecord();
        if (!$this->provider instanceof FormProviderInterface) {
            throw new \UnexpectedValueException('Provider must implement ' . FormProviderInterface::class, 1669488830);
        }
        if (!$this->provider instanceof RecordProviderInterface) {
            throw new \UnexpectedValueException(
                'Provider must implement ' . RecordProviderInterface::class,
                1669488830
            );
        }
        $form = $this->provider->getForm($record);
        $input = $this->request->getArguments();

        if (method_exists($this->request, 'getInternalArguments')) {
            $arguments = $this->request->getInternalArguments();
        } else {
            $arguments = $this->request->getArguments();
        }
        $targetConfiguration = $arguments['__outlet'] ?? [];

        if ($form === null
            ||
            ($this->provider->getTableName($record) !== ($targetConfiguration['table'] ?? '')
                && ($record['uid'] ?? 0) !== (integer) ($targetConfiguration['recordUid'] ?? 0)
            )
        ) {
            // This instance does not match the instance that rendered the form. Forward the request
            // to the default "render" action.
            return $this->renderAction();
        }
        $input['settings'] = $this->settings;
        try {
            /** @var Form $form */
            $outlet = $form->getOutlet();
            $outlet->setView($this->view);
            $outlet->fill($input);
            if (!$outlet->isValid()) {
                $input = array_replace(
                    $input,
                    [
                        'validationResults' => $outlet->getValidationResults()->getFlattenedErrors()
                    ]
                );

                $content = $this->view->renderSection('Main', $input, true);
            } else {
                // Pipes of Outlet get called in sequence to either return content or perform actions
                // Outlet receives our local View which is pre-configured with paths. If one was not
                // passed, a default StandaloneView is created with paths belonging to extension that
                // contains the Form.
                $input = array_replace(
                    $input,
                    $outlet->produce()
                );

                $content = $this->view->renderSection('OutletSuccess', $input, true);
            }
        } catch (\RuntimeException $error) {
            $input['error'] = $error;

            $content = $this->view->renderSection('OutletError', $input, true);
        }

        return $this->createHtmlResponse($content);
    }
}
