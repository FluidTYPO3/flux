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
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerInterface;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

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

    public function injectConfigurationService(FluxService $configurationService): void
    {
        $this->configurationService = $configurationService;
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

    protected function initializeViewVariables(): void
    {
        $row = $this->getRecord();
        if ($this->provider instanceof FluidProviderInterface) {
            $this->view->assignMultiple($this->provider->getTemplateVariables($row));
        }
        $this->view->assignMultiple($this->data);
        $this->view->assign('settings', $this->settings);
        $this->view->assign('provider', $this->provider);
        $this->view->assign('record', $row);
        HookHandler::trigger(
            HookHandler::CONTROLLER_VARIABLES_ASSIGNED,
            [
                'view' => $this->view,
                'record' => $row,
                'settings' => $this->settings,
                'provider' => $this->provider,
            ]
        );
    }

    protected function initializeViewHelperVariableContainer(): void
    {
        $viewHelperVariableContainer = $this->view->getRenderingContext()->getViewHelperVariableContainer();
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

    protected function initializeView(ViewInterface $view): void
    {
        if (!$this->provider instanceof ControllerProviderInterface) {
            return;
        }
        $record = $this->getRecord();
        $extensionKey = ExtensionNamingUtility::getExtensionKey(
            $this->provider->getControllerExtensionKeyFromRecord($record)
        );
        $extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);

        $this->controllerContext->getRequest()->setControllerExtensionName($extensionName);
        $view->setControllerContext($this->controllerContext);

        /** @var TemplatePaths $templatePaths */
        $templatePaths = $this->objectManager->get(TemplatePaths::class, $extensionKey);

        /** @var RenderingContextInterface $renderingContext */
        $renderingContext = $view->getRenderingContext();
        $renderingContext->setTemplatePaths($templatePaths);
        $renderingContext->setControllerAction(
            $this->provider->getControllerActionFromRecord($record)
        );
        $this->initializeViewVariables();
        $this->initializeViewHelperVariableContainer();
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
    }

    protected function resolveView(): ViewInterface
    {
        /** @var class-string $viewClassName */
        $viewClassName = (!method_exists($this, 'resolveViewObjectName')
            ? $this->defaultViewObjectName
            : $this->resolveViewObjectName()) ?: $this->defaultViewObjectName;
        /** @var ViewInterface $view */
        $view = $this->objectManager->get($viewClassName);
        return $view;
    }

    /**
     * Default action, proxy for "render". Added in order to
     * capture requests which use the Fluid-native "default"
     * action name when no specific action name is set in the
     * request. The "default" action is also returned by
     * vanilla Provider instances when registering them for
     * content object types or other ad-hoc registrations.
     */
    public function defaultAction(): void
    {
        $this->forward('render');
    }

    /**
     * Render content
     */
    public function renderAction(): string
    {
        if (!$this->provider instanceof ControllerProviderInterface) {
            return '';
        }
        $row = $this->getRecord();
        $extensionKey = $this->provider->getControllerExtensionKeyFromRecord($row);
        $extensionSignature = ExtensionNamingUtility::getExtensionSignature($extensionKey);
        $pluginSignature = strtolower('tx_' . $extensionSignature . '_' . $this->request->getPluginName());
        $controllerExtensionKey = $this->provider->getControllerExtensionKeyFromRecord($row);
        $requestActionName = $this->resolveOverriddenFluxControllerActionNameFromRequestParameters($pluginSignature);
        $controllerActionName = $this->provider->getControllerActionFromRecord($row);
        $actualActionName = null !== $requestActionName ? $requestActionName : $controllerActionName;
        $controllerName = $this->request->getControllerName();

        return $this->performSubRendering(
            $controllerExtensionKey,
            $controllerName,
            $actualActionName,
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

    protected function performSubRendering(
        string $extensionName,
        string $controllerName,
        string $actionName,
        string $pluginSignature
    ): string {
        if (isset($this->responseFactory)) {
            $response = $this->responseFactory->createResponse();
        } else {
            $response = $this->objectManager->get(Response::class);
        }
    
        $shouldRelay = $this->hasSubControllerActionOnForeignController($extensionName, $controllerName, $actionName);
        $foreignControllerClass = null;
        $content = null;
        if (!$shouldRelay) {
            if ($this->provider instanceof FluidProviderInterface) {
                $templatePathAndFilename = $this->provider->getTemplatePathAndFilename($this->getRecord());
                $vendorLessExtensionName = ExtensionNamingUtility::getExtensionName($extensionName);
                $paths = $this->view->getRenderingContext()->getTemplatePaths();

                $this->request->setControllerExtensionName($vendorLessExtensionName);
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
                $paths->setTemplatePathAndFilename($templatePathAndFilename);
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
                $pluginSignature
            );
        }
        return HookHandler::trigger(
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
        $isForeign = $extensionName !== $this->extensionName;
        $isValidController = class_exists($potentialControllerClassName);
        return (true === $isForeign && true === $isValidController
            && method_exists($potentialControllerClassName, $actionName . 'Action'));
    }

    /**
     * @param class-string $controllerClassName
     */
    protected function callSubControllerAction(
        string $extensionName,
        string $controllerClassName,
        string $controllerActionName,
        string $pluginSignature
    ): string {
        $post = GeneralUtility::_POST($pluginSignature);
        $arguments = (array) (true === is_array($post) ? $post : GeneralUtility::_GET($pluginSignature));
        $this->request->setArguments($arguments);
        $this->request->setControllerExtensionName($extensionName);
        $this->request->setControllerActionName($controllerActionName);
        /** @var ControllerInterface $potentialControllerInstance */
        $potentialControllerInstance = $this->objectManager->get($controllerClassName);

        if (isset($this->responseFactory)) {
            $response = $this->responseFactory->createResponse();
        } else {
            /** @var Response $response */
            $response = $this->objectManager->get(Response::class);
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
            $responseFromCall = $potentialControllerInstance->processRequest($this->request, $response);
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
        $response->getBody()->rewind();
        return $response->getBody()->getContents();
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

    public function outletAction(): string
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
        $targetConfiguration = $this->request->getInternalArguments()['__outlet'] ?? [];
        if ($form === null
            ||
            ($this->provider->getTableName($record) !== ($targetConfiguration['table'] ?? '')
                && ($record['uid'] ?? 0) !== (integer) ($targetConfiguration['recordUid'] ?? 0)
            )
        ) {
            // This instance does not match the instance that rendered the form. Forward the request
            // to the default "render" action.
            $this->forward('render');
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

                return $this->view->renderSection('Main', $input, true);
            } else {
                // Pipes of Outlet get called in sequence to either return content or perform actions
                // Outlet receives our local View which is pre-configured with paths. If one was not
                // passed, a default StandaloneView is created with paths belonging to extension that
                // contains the Form.
                $input = array_replace(
                    $input,
                    $outlet->produce()
                );

                return $this->view->renderSection('OutletSuccess', $input, true);
            }
        } catch (\RuntimeException $error) {
            $input['error'] = $error;

            return $this->view->renderSection('OutletError', $input, true);
        }
    }
}
