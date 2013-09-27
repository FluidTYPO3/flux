<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
abstract class Tx_Flux_Controller_AbstractFluxController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'Tx_Flux_View_ExposedTemplateView';

	/**
	 * @var string
	 */
	protected $fallbackExtensionKey = 'flux';

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * @var Tx_Flux_Provider_ProviderInterface
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
	private $setup = array();

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @param Tx_Flux_Service_FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Flux_Service_FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @return void
	 * @throws RuntimeException
	 */
	protected function initializeSettings() {
		$row = $this->getRecord();
		$extensionKey = $this->provider->getExtensionKey($row);
		$extensionName = t3lib_div::underscoredToUpperCamelCase($extensionKey);
		$pluginName = $this->request->getPluginName();
		$this->settings = (array) $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $extensionName, $pluginName);
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
			$this->settings = Tx_Flux_Utility_RecursiveArray::merge($this->settings, $this->data['settings']);
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
	 * @throws RuntimeException
	 * @return void
	 */
	protected function initializeProvider() {
		$row = $this->getRecord();
		$table = $this->getFluxTableName();
		$field = $this->getFluxRecordField();
		$this->provider = $this->configurationService->resolvePrimaryConfigurationProvider($table, $field, $row);
		if (NULL === $this->provider) {
			throw new RuntimeException('Unable to resolve a ConfigurationProvider, but controller indicates it is a Flux-enabled Controller - ' .
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
		$extensionName = t3lib_div::underscoredToUpperCamelCase($extensionKey);
		$controller = $this->request->getControllerName();
		$this->view = $this->configurationService->getPreparedExposedTemplateView($extensionName, $controller, $this->setup, $this->data);
		$this->view->setTemplatePathAndFilename($templatePathAndFilename);
	}

	/**
	 * @param Tx_Extbase_MVC_View_ViewInterface $view
	 *
	 * @throws Exception
	 * @return void
	 */
	public function initializeView(Tx_Extbase_MVC_View_ViewInterface $view) {
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
		$controllerExtensionName = t3lib_div::underscoredToUpperCamelCase($controllerExtensionKey);
		$requestParameterActionName = Tx_Flux_Utility_Resolve::resolveOverriddenFluxControllerActionNameFromRequestParameters($pluginSignature);
		$controllerActionName = $this->provider->getControllerActionFromRecord($row);
		$overriddenControllerActionName = NULL !== $requestParameterActionName ? $requestParameterActionName : $controllerActionName;
		$controllerName = $this->request->getControllerName();
		return $this->performSubRendering($controllerExtensionName, $controllerName, $overriddenControllerActionName, $pluginSignature);
	}

	/**
	 * @param string $extensionName
	 * @param string $controllerName
	 * @param string $actionName
	 * @param string $pluginSignature
	 * @return string
	 */
	protected function performSubRendering($extensionName, $controllerName, $actionName, $pluginSignature) {
		$extensionKey = t3lib_div::camelCaseToLowerCaseUnderscored($extensionName);
		$potentialControllerClassName = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction($extensionKey, $actionName, $controllerName);
		if ($extensionName === $this->extensionName) {
			return $this->view->render();
		} elseif (TRUE === class_exists($potentialControllerClassName)) {
			return $this->callSubControllerAction($potentialControllerClassName, $actionName, $pluginSignature);
		}
		return $this->view->render();
	}

	/**
	 * @param string $controllerClassName
	 * @param string $controllerActionName
	 * @param string $pluginSignature
	 * @return string
	 */
	protected function callSubControllerAction($controllerClassName, $controllerActionName, $pluginSignature) {
		/** @var $response Tx_Extbase_MVC_Web_Response */
		$response = $this->objectManager->get('Tx_Extbase_MVC_Web_Response');
		$arguments = (array) (TRUE === is_array(t3lib_div::_POST($pluginSignature)) ? t3lib_div::_POST($pluginSignature) : t3lib_div::_GET($pluginSignature));
		$potentialControllerInstance = $this->objectManager->get($controllerClassName);
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
		return $row;
	}

}
