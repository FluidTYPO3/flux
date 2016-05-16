<?php
namespace FluidTYPO3\Flux\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Response;

/**
 * Abstract Flux-enabled controller
 *
 * Extends a traditional ActionController with new services and methods
 * to ease interaction with Flux forms. Is not required as subclass for
 * Controllers rendering records associated with Flux - all it does is
 * ease the interaction by providing a common API.
 *
 * @route off
 */
abstract class AbstractFluxController extends ActionController {

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'FluidTYPO3\Flux\View\ExposedTemplateView';

	/**
	 * @var string
	 */
	protected $fallbackExtensionKey = 'flux';

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @var \FluidTYPO3\Flux\Provider\ProviderInterface
	 */
	protected $provider;

	/**
	 * @var string
	 */
	protected $fluxRecordField = 'pi_flexform';

	/**
	 * @var string
	 */
	protected $fluxTableName = 'tt_content';

	/**
	 * @var array
	 */
	protected $setup = array();

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var WorkspacesAwareRecordService
	 */
	protected $workspacesAwareRecordService;

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param WorkspacesAwareRecordService $workspacesAwareRecordService
	 * @return void
	 */
	public function injectWorkspacesAwareRecordService(WorkspacesAwareRecordService $workspacesAwareRecordService) {
		$this->workspacesAwareRecordService = $workspacesAwareRecordService;
	}

	/**
	 * @return void
	 * @throws \RuntimeException
	 */
	protected function initializeSettings() {
		$row = $this->getRecord();
		$extensionKey = $this->provider->getExtensionKey($row);
		$extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
		$pluginName = $this->request->getPluginName();
		$this->settings = RecursiveArrayUtility::merge(
			(array) $this->configurationManager->getConfiguration(
				ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $extensionName, $pluginName
			),
			$this->settings
		);
		$this->data = $this->provider->getFlexFormValues($row);
		$this->setup = $this->provider->getTemplatePaths($row);
	}

	/**
	 * @return void
	 */
	protected function initializeOverriddenSettings() {
		$row = $this->getRecord();
		$extensionKey = $this->provider->getExtensionKey($row);
		$extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKey);
		if (TRUE === isset($this->data['settings']) && TRUE === is_array($this->data['settings'])) {
			// a "settings." array is defined in the flexform configuration - extract it, use as "settings" in template
			// as well as the internal $this->settings array as per expected Extbase behavior.
			$this->settings = RecursiveArrayUtility::merge($this->settings, $this->data['settings']);
		}
		if (TRUE === isset($this->settings['useTypoScript']) && TRUE === (boolean) $this->settings['useTypoScript']) {
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
	protected function initializeProvider() {
		$row = $this->getRecord();
		$table = $this->getFluxTableName();
		$field = $this->getFluxRecordField();
		$this->provider = $this->configurationService->resolvePrimaryConfigurationProvider($table, $field, $row);
		if (NULL === $this->provider) {
			throw new \RuntimeException(
				'Unable to resolve a ConfigurationProvider, but controller indicates it is a Flux-enabled Controller - ' .
				'this is a grave error and indicates that EXT: ' . $this->extensionName . ' itself is broken - or that EXT:' .
				$this->extensionName . ' has been overridden by another implementation which is broken. The controller that ' .
				'caused this error was ' . get_class($this) . '".',
				1377458581
			);
		}
	}

	/**
	 * @return void
	 */
	protected function initializeViewVariables() {
		$row = $this->getRecord();
		$this->view->assignMultiple($this->provider->getTemplateVariables($row));
		$this->view->assignMultiple($this->data);
		$this->view->assign('settings', $this->settings);
		$this->view->assign('provider', $this->provider);
		$this->view->assign('record', $row);
	}

	/**
	 * @return void
	 */
	protected function initializeViewObject() {
		$row = $this->getRecord();
		$viewContext = $this->provider->getViewContext($row, $this->request);
		$controllerActionName = $this->provider->getControllerActionFromRecord($row);
		$this->view = $this->configurationService->getPreparedExposedTemplateView($viewContext);
	}

	/**
	 * @param ViewInterface $view
	 *
	 * @return void
	 */
	public function initializeView(ViewInterface $view) {
		$this->view = $view;
		$this->initializeProvider();
		$this->initializeSettings();
		$this->initializeOverriddenSettings();
		$this->initializeViewObject();
		$this->initializeViewVariables();
	}

	/**
	 * @return string
	 * @route off
	 */
	public function renderAction() {
		$row = $this->getRecord();
		$extensionKey = $this->provider->getExtensionKey($row);
		$extensionSignature = ExtensionNamingUtility::getExtensionSignature($extensionKey);
		$pluginSignature = strtolower('tx_' . $extensionSignature . '_' . $this->request->getPluginName());
		$controllerExtensionKey = $this->provider->getControllerExtensionKeyFromRecord($row);
		$requestActionName = $this->resolveOverriddenFluxControllerActionNameFromRequestParameters($pluginSignature);
		$controllerActionName = $this->provider->getControllerActionFromRecord($row);
		$actualActionName = NULL !== $requestActionName ? $requestActionName : $controllerActionName;
		$controllerName = $this->request->getControllerName();
		return $this->performSubRendering($controllerExtensionKey, $controllerName, $actualActionName, $pluginSignature);
	}

	/**
	 * @param string $pluginSignature
	 * @return string|NULL
	 */
	protected function resolveOverriddenFluxControllerActionNameFromRequestParameters($pluginSignature) {
		$requestParameters = (array) GeneralUtility::_GET($pluginSignature);
		$overriddenControllerActionName = TRUE === isset($requestParameters['action']) ? $requestParameters['action'] : NULL;
		return $overriddenControllerActionName;
	}

	/**
	 * @param string $extensionName
	 * @param string $controllerName
	 * @param string $actionName
	 * @param string $pluginSignature
	 * @return string
	 */
	protected function performSubRendering($extensionName, $controllerName, $actionName, $pluginSignature) {
		$shouldRelay = $this->hasSubControllerActionOnForeignController($extensionName, $controllerName, $actionName);
		if (TRUE === $shouldRelay) {
			$foreignControllerClass = $this->configurationService
				->getResolver()->resolveFluxControllerClassNameByExtensionKeyAndAction(
				$extensionName, $actionName, $controllerName
			);
			return $this->callSubControllerAction($extensionName, $foreignControllerClass, $actionName, $pluginSignature);
		}
		return $this->view->render();
	}

	/**
	 * @param string $extensionName
	 * @param string $controllerName
	 * @param string $actionName
	 * @return boolean
	 */
	protected function hasSubControllerActionOnForeignController($extensionName, $controllerName, $actionName) {
		$potentialControllerClassName = $this->configurationService
			->getResolver()->resolveFluxControllerClassNameByExtensionKeyAndAction($extensionName, $actionName, $controllerName);
		$isForeign = $extensionName !== $this->extensionName;
		$isValidController = class_exists($potentialControllerClassName);
		return (TRUE === $isForeign && TRUE === $isValidController);
	}

	/**
	 * @param string $extensionName
	 * @param string $controllerClassName
	 * @param string $controllerActionName
	 * @param string $pluginSignature
	 * @return string
	 */
	protected function callSubControllerAction($extensionName, $controllerClassName, $controllerActionName, $pluginSignature) {
		/** @var Response $response */
		$post = GeneralUtility::_POST($pluginSignature);
		$row = $this->getRecord();
		$response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
		$arguments = (array) (TRUE === is_array($post) ? $post : GeneralUtility::_GET($pluginSignature));
		$potentialControllerInstance = $this->objectManager->get($controllerClassName);
		$viewContext = $this->provider->getViewContext($row, $this->request);
		$viewContext->setPackageName($this->provider->getControllerPackageNameFromRecord($row));
		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Request $subRequest */
		$subRequest = $viewContext->getRequest();
		$subRequest->setArguments($arguments);
		$subRequest->setControllerExtensionName($viewContext->getExtensionName());
		$subRequest->setControllerVendorName($viewContext->getVendorName());
		$subRequest->setControllerActionName($this->provider->getControllerActionFromRecord($row));
		$potentialControllerInstance->processRequest($subRequest, $response);
		return $response->getContent();
	}

	/**
	 * Get the data stored in a record's Flux-enabled field,
	 * i.e. the variables of the Flux template as configured in this
	 * particular record.
	 *
	 * @return array
	 */
	protected function getData() {
		return $this->data;
	}

	/**
	 * Get the array of TS configuration associated with the
	 * Flux template of the record (or overall record type)
	 * currently being rendered.
	 *
	 * @return array
	 */
	protected function getSetup() {
		return $this->setup;
	}

	/**
	 * @return string
	 */
	protected function getFluxRecordField() {
		return $this->fluxRecordField;
	}

	/**
	 * @return string
	 */
	protected function getFluxTableName() {
		return $this->fluxTableName;
	}

	/**
	 * @return array
	 */
	public function getRecord() {
		$row = $this->configurationManager->getContentObject()->data;
		return (array) $row;
	}

}
