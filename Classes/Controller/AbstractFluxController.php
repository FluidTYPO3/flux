<?php
namespace FluidTYPO3\Flux\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
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
 * @package Flux
 * @subpackage Controller
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
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @return void
	 * @throws RuntimeException
	 */
	protected function initializeSettings() {
		$row = $this->getRecord();
		$extensionKey = $this->provider->getExtensionKey($row);
		$extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
		$pluginName = $this->request->getPluginName();
		$this->settings = (array) $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $extensionName, $pluginName);
		$this->data = $this->provider->getFlexFormValues($row);
		$this->setup = $this->provider->getTemplatePaths($row);
	}

	/**
	 * @return void
	 */
	protected function initializeOverriddenSettings() {
		$row = $this->getRecord();
		$extensionKey = $this->provider->getExtensionKey($row);
		if (TRUE === isset($this->data['settings']) && TRUE === is_array($this->data['settings'])) {
			// a "settings." array is defined in the flexform configuration - extract it, use as "settings" in template
			// as well as the internal $this->settings array as per expected Extbase behavior.
			$this->settings = RecursiveArrayUtility::merge($this->settings, $this->data['settings']);
		}
		if (TRUE === isset($this->settings['useTypoScript']) && TRUE === (boolean) $this->settings['useTypoScript']) {
			// an override shared by all Flux enabled controllers: setting plugin.tx_EXTKEY.settings.useTypoScript = 1
			// will read the "settings" array from that location instead - thus excluding variables from the flexform
			// which are still available as $this->data but no longer available automatically in the template.
			$extensionSignature = str_replace('_', '', $extensionKey);
			$this->settings = $this->configurationService->getTypoScriptSubConfiguration(NULL, 'settings', $extensionSignature);
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
			throw new \RuntimeException('Unable to resolve a ConfigurationProvider, but controller indicates it is a Flux-enabled Controller - ' .
				'this is a grave error and indicates that EXT: ' . $this->extensionName . ' itself is broken - or that EXT:' . $this->extensionName .
				' has been overridden by another implementation which is broken. The controller that caused this error was ' .
				get_class($this) . '".', 1377458581);
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
		$templatePathAndFilename = $this->provider->getTemplatePathAndFilename($row);
		$extensionKey = $this->provider->getExtensionKey($row);
		$extensionName = ExtensionNamingUtility::getExtensionName($extensionKey);
		$controller = $this->request->getControllerName();
		$this->view = $this->configurationService->getPreparedExposedTemplateView($extensionKey, $controller, $this->setup, $this->data);
		$this->request->setControllerExtensionName($extensionName);
		$this->view->setControllerContext($this->controllerContext);
		if (FALSE === empty($templatePathAndFilename)) {
			$this->view->setTemplatePathAndFilename($templatePathAndFilename);
		}
	}

	/**
	 * @param ViewInterface $view
	 *
	 * @return void
	 */
	public function initializeView(ViewInterface $view) {
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
		$pluginSignature = 'tx_' . str_replace('_', '', $extensionKey) . '_' . str_replace('_', '', strtolower($this->request->getPluginName()));
		$controllerExtensionKey = $this->provider->getControllerExtensionKeyFromRecord($row);
		$requestParameterActionName = ResolveUtility::resolveOverriddenFluxControllerActionNameFromRequestParameters($pluginSignature);
		$controllerActionName = $this->provider->getControllerActionFromRecord($row);
		$overriddenControllerActionName = NULL !== $requestParameterActionName ? $requestParameterActionName : $controllerActionName;
		$controllerName = $this->request->getControllerName();
		return $this->performSubRendering($controllerExtensionKey, $controllerName, $overriddenControllerActionName, $pluginSignature);
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
			$foreignControllerClass = ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction($extensionName, $actionName, $controllerName);
			$extensionName = ExtensionNamingUtility::getExtensionName($extensionName);
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
		$potentialControllerClassName = ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction($extensionName, $actionName, $controllerName);
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
		$response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
		$arguments = (array) (TRUE === is_array(GeneralUtility::_POST($pluginSignature)) ? GeneralUtility::_POST($pluginSignature) : GeneralUtility::_GET($pluginSignature));
		$potentialControllerInstance = $this->objectManager->get($controllerClassName);
		$this->request->setControllerExtensionName($extensionName);
		$this->request->setControllerActionName($controllerActionName);
		$this->request->setArguments($arguments);
		$potentialControllerInstance->processRequest($this->request, $response);
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
