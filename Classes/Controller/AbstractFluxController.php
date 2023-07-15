<?php
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
use FluidTYPO3\Flux\Provider\Interfaces\FluidProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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

    /**
     * @var string
     */
    protected $fallbackExtensionKey = 'flux';

    /**
     * @var string
     */
    protected $extensionName = 'FluidTYPO3.Flux';

    /**
     * @var FluxService
     */
    protected $configurationService;

    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @var string|null
     */
    protected $fluxRecordField = 'pi_flexform';

    /**
     * @var string|null
     */
    protected $fluxTableName = 'tt_content';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @return void
     * @throws \RuntimeException
     */
    protected function initializeSettings()
    {
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
            $this->settings
        );
        $this->data = $this->provider->getFlexFormValues($row);
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

    /**
     * @return void
     */
    protected function initializeOverriddenSettings()
    {
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

    /**
     * @throws \RuntimeException
     * @return void
     */
    protected function initializeProvider()
    {
        $row = $this->getRecord();
        $table = (string) $this->getFluxTableName();
        $field = $this->getFluxRecordField();
        $provider = $this->configurationService->resolvePrimaryConfigurationProvider(
            $table,
            $field,
            $row,
            null,
            ControllerProviderInterface::class
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

    /**
     * @return void
     */
    protected function initializeViewVariables()
    {
        $row = $this->getRecord();
        $this->view->assignMultiple($this->provider->getTemplateVariables($row));
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

    /**
     * @return void
     */
    protected function initializeViewHelperVariableContainer()
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

    /**
     * @return void
     */
    protected function initializeAction()
    {
        $this->initializeProvider();
        $this->initializeSettings();
        $this->initializeOverriddenSettings();
    }

    /**
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
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

    /**
     * Prepares a view for the current action.
     * By default, this method tries to locate a view with a name matching the current action.
     *
     * @return ViewInterface
     * @api
     */
    protected function resolveView()
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
     *
     * @return void
     */
    public function defaultAction()
    {
        $this->forward('render');
    }

    /**
     * Render content
     *
     * @return string
     */
    public function renderAction()
    {
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

    /**
     * @param string $pluginSignature
     * @return string|null
     */
    protected function resolveOverriddenFluxControllerActionNameFromRequestParameters($pluginSignature)
    {
        /** @var string[] $requestParameters */
        $requestParameters = (array) GeneralUtility::_GET($pluginSignature);
        $overriddenControllerActionName = isset($requestParameters['action']) ? $requestParameters['action'] : null;
        return $overriddenControllerActionName;
    }

    /**
     * @param string $extensionName
     * @param string $controllerName
     * @param string $actionName
     * @param string $pluginSignature
     * @return string
     */
    protected function performSubRendering($extensionName, $controllerName, $actionName, $pluginSignature)
    {
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

    /**
     * @param string $extensionName
     * @param string $controllerName
     * @param string $actionName
     * @return boolean
     */
    protected function hasSubControllerActionOnForeignController($extensionName, $controllerName, $actionName)
    {
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
     * @param string $extensionName
     * @param class-string $controllerClassName
     * @param string $controllerActionName
     * @param string $pluginSignature
     * @return string
     */
    protected function callSubControllerAction(
        $extensionName,
        $controllerClassName,
        $controllerActionName,
        $pluginSignature
    ) {
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

        if (class_exists(Typo3Version::class)) {
            /** @var Typo3Version $versionClass */
            $versionClass = GeneralUtility::makeInstance(Typo3Version::class);
            $version = $versionClass->getVersion();
        } else {
            $version = ExtensionManagementUtility::getExtensionVersion('core');
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
     *
     * @return array
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * @return string|null
     */
    protected function getFluxRecordField()
    {
        return $this->fluxRecordField;
    }

    /**
     * @return string|null
     */
    protected function getFluxTableName()
    {
        return $this->fluxTableName;
    }

    /**
     * @return array
     */
    public function getRecord()
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
     * @return string
     */
    public function outletAction()
    {
        $record = $this->getRecord();
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
