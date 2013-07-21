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
	 * Exception code for "class not found" - which is thrown, caught but ignored
	 * when Flux attempts to use a custom controller extension name without also
	 * replacing the standard controller. In this case the error is friendly enough
	 * but still takes the form of an Exception - and we have way to change that
	 * default Extbase behavior.
	 *
	 * @var integer
	 */
	const EXCEPTION_CUSTOM_CONTROLLER_NOT_FOUND = 1289386765;

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'Tx_Flux_MVC_View_ExposedTemplateView';

	/**
	 * @var string
	 */
	protected $fallbackExtensionKey = 'flux';

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * @var Tx_Flux_Provider_ConfigurationProviderInterface
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
	 * @param Tx_Extbase_MVC_View_ViewInterface $view
	 *
	 * @throws Exception
	 * @return void
	 */
	public function initializeView(Tx_Extbase_MVC_View_ViewInterface $view) {
		try {
			$this->view = $view;
			$row = $this->getRecord();
			if (TRUE === empty($row)) {
				if ('BE' === TYPO3_MODE) {
					$row = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', "uid = '" . t3lib_div::_GET('id') . "'"));
				} else {
					$row = $GLOBALS['TSFE']->page;
				}
			}
			if (TRUE === empty($row)) {
				$this->configurationService->message('Unable to detect active record; page, content or otherwise.', 1368271141);
				return;
			}
			$table = $this->getFluxTableName();
			$field = $this->getFluxRecordField();
			$this->provider = $this->configurationService->resolvePrimaryConfigurationProvider($table, $field, $row);
			$extensionKey = $this->provider->getExtensionKey($row);
			$extensionName = t3lib_div::underscoredToUpperCamelCase($extensionKey);
			if (NULL === $this->provider) {
				$this->configurationService->message('Unable to resolve a ConfigurationProvider, but controller indicates it is a Flux-enabled Controller - ' .
					'this is a grave error and indicates that EXT: ' . $extensionName . ' itself is broken - or that EXT:' . $extensionName .
					' has been overridden by another implementation which is broken. The controller that caused this error was ' .
					get_class($this) . ' and the table name is "' . $table . '".', t3lib_div::SYSLOG_SEVERITY_WARNING);
				return;
			}
			$extensionSignature = str_replace('_', '', $extensionKey);
			$pluginName = $this->request->getPluginName();
			$this->setup = $this->provider->getTemplatePaths($row);
			if (FALSE === is_array($this->setup) || 0 === count($this->setup)) {
				throw new Exception('Unable to read a working path set from the Provider. The extension that caused this error was "' .
					$extensionName . '" and the controller was "' . get_class($this) . '". The provider which should have returned ' .
					'a valid path set was "' . get_class($this->provider) . '" but it returned an empty array or not an array. View ' .
					'paths have been reset to paths native to the controller in question.', 1364685651);
			}
			$this->settings = (array) $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $extensionName, $pluginName);
			$this->data = $this->provider->getFlexFormValues($row);
			$this->view = $this->configurationService->getPreparedExposedTemplateView($this->extensionName, $this->request->getControllerName(), $this->setup, $this->data);
			if (TRUE === isset($this->settings['useTypoScript']) && 0 < $this->settings['useTypoScript']) {
				// an override shared by all Flux enabled controllers: setting plugin.tx_EXTKEY.settings.useTypoScript = 1
				// will read the "settings" array from that location instead - thus excluding variables from the flexform
				// which are still available as $this->data but no longer available automatically in the template.
				$this->settings = $this->configurationService->getTypoScriptSubConfiguration(NULL, 'settings', $extensionSignature);
			} elseif (TRUE === isset($this->data['settings']) && TRUE === is_array($this->data['settings'])) {
				// a "settings." array is defined in the flexform configuration - extract it, use as "settings" in template
				// as well as the internal $this->settings array as per expected Extbase behavior.
				$this->settings = t3lib_div::array_merge_recursive_overrule($this->settings, $this->data['settings'], FALSE, TRUE);
			}
			$this->view->assignMultiple($this->data);
			$this->view->assign('settings', $this->settings);
			$templatePathAndFilename = $this->provider->getTemplatePathAndFilename($row);
			if (FALSE === file_exists($templatePathAndFilename)) {
				throw new Exception('Desired template file "' . $templatePathAndFilename . '" does not exist', 1364741158);
			}
			/** @var $view Tx_Flux_MVC_View_ExposedTemplateView */
			$this->view->setTemplatePathAndFilename($templatePathAndFilename);
			$this->view->setLayoutRootPath($this->setup['layoutRootPath']);
			$this->view->setPartialRootPath($this->setup['partialRootPath']);
			$this->view->setTemplateRootPath($this->setup['templateRootPath']);
			$this->view->assign('fluxTableName', $this->fluxTableName);
			$this->view->assign('fluxRecordField', $this->fluxRecordField);
			$this->view->assign('record', $row);
		} catch (Exception $error) {
			$this->handleError($error);
		}
	}

	/**
	 * @return string
	 */
	public function errorAction() {
		$this->clearCacheOnError();
		$setup = $this->getSetup();
		$extensionName = $this->controllerContext->getRequest()->getControllerExtensionName();
		$extensionKey = t3lib_div::camelCaseToLowerCaseUnderscored($extensionName);
		$nativePaths = $this->configurationService->getViewConfigurationForExtensionName($extensionKey);
		$controllerName = $this->request->getControllerName();
		$errorPageSubPath = $controllerName . '/Error.' . $this->request->getFormat();
		$errorTemplatePathAndFilename = $setup['templateRootPath'] . $errorPageSubPath;
		if (FALSE === file_exists($errorTemplatePathAndFilename) || $setup === NULL) {
			if (TRUE === file_exists($nativePaths['templateRootPath'] . $errorPageSubPath)) {
				$this->view->setTemplateRootPath($nativePaths['templateRootPath']);
				$this->view->setLayoutRootPath($nativePaths['layoutRootPath']);
				$this->view->setPartialRootPath($nativePaths['partialRootPath']);
			}
		}
	}

	/**
	 * @return string
	 * @route off
	 * @throws Exception
	 */
	public function renderAction() {
		$row = $this->getRecord();
		$this->provider = $this->configurationService->resolvePrimaryConfigurationProvider($this->fluxTableName, $this->fluxRecordField, $row);
		$extensionKey = $this->provider->getExtensionKey($row);
		$pluginSignature = 'tx_' . str_replace('_', '', $extensionKey) . '_content';
		$controllerActionName = $this->provider->getControllerActionFromRecord($row);
		$controllerExtensionKey = $this->provider->getControllerExtensionKeyFromRecord($row);
		$controllerExtensionName = t3lib_div::underscoredToUpperCamelCase($controllerExtensionKey);
		$requestParameters = (array) t3lib_div::_GET($pluginSignature);
		$arguments = (array) (TRUE === is_array(t3lib_div::_POST($pluginSignature)) ? t3lib_div::_POST($pluginSignature) : $requestParameters);
		$overriddenControllerActionName = TRUE === isset($requestParameters['action']) ? $requestParameters['action'] : $controllerActionName;
		if ($controllerExtensionName === $this->extensionName) {
			return $this->view->render();
		}
		try {
			$controllerName = $this->request->getControllerName();
			$potentialControllerClassName = $this->resolveFluxControllerClassNameByExtensionKeyAndAction($extensionKey, $overriddenControllerActionName, $controllerName);
			if (NULL === $potentialControllerClassName) {
				$this->request->setControllerExtensionName($this->extensionName);
				return $this->view->render();
			}
			/** @var $response Tx_Extbase_MVC_Web_Response */
			$response = $this->objectManager->get('Tx_Extbase_MVC_Web_Response');
			$potentialControllerInstance = $this->objectManager->get($potentialControllerClassName);
			$this->request->setControllerActionName($overriddenControllerActionName);
			$this->request->setControllerExtensionName($controllerExtensionName);
			$this->request->setArguments($arguments);
			$potentialControllerInstance->processRequest($this->request, $response);
			return $response->getContent();
		} catch (Exception $error) {
			$code = $error->getCode();
			if (self::EXCEPTION_CUSTOM_CONTROLLER_NOT_FOUND === $code) {
				return $this->view->render();
			}
			if (TRUE === (boolean) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['handleErrors']) {
				$this->handleError($error);
			} else {
				throw $error;
			}
		}
		return $this->view->render();
	}

	/**
	 * @param Exception $error
	 * @throws Exception
	 * @return void
	 */
	public function handleError(Exception $error) {
		if (TRUE === isset($this->settings['displayErrors']) && 0 < $this->settings['displayErrors']) {
			throw $error;
		}
		$versionNumbers = explode('.', TYPO3_version);
		$versionNumbers = array_map('intval', $versionNumbers);
		$versionVariable = array();
		list ($versionVariable['major'], $versionVariable['minor'], $versionVariable['bugfix']) = $versionNumbers;
		$versionVariable['isLongTermSupport'] = (4 === $versionVariable['major'] && 5 === $versionVariable['minor']);
		$this->configurationService->debug($error);
		$this->view->assign('class', get_class($this));
		$this->view->assign('error', $error);
		$this->view->assign('backtrace', $this->getLimitedBacktrace());
		$this->view->assign('version', $versionVariable);
		if ('error' !== $this->request->getControllerActionName()) {
			$this->forward('error');
		}
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
	private function getLimitedBacktrace() {
		$trace = debug_backtrace();
		foreach ($trace as $index => $step) {
			if (($step['class'] === 'TYPO3\\CMS\\Extbase\\Core\\Bootstrap' || $step['class'] === 'Tx_Extbase_Core_Bootstrap') && $step['function'] === 'run') {
				$trace = array_slice($trace, 1, $index);
				break;
			}
		}
		return $trace;
	}

	/**
	 * @return array
	 */
	public function getRecord() {
		return $this->configurationManager->getContentObject()->data;
	}

	/**
	 * @param string $extensionKey
	 * @param string $action
	 * @param string $controllerObjectShortName
	 * @param boolean $failHardClass
	 * @param boolean $failHardAction
	 * @throws Exception
	 * @return string|NULL
	 */
	protected function resolveFluxControllerClassNameByExtensionKeyAndAction($extensionKey, $action, $controllerObjectShortName, $failHardClass = FALSE, $failHardAction = FALSE) {
		$extensionName = t3lib_div::underscoredToUpperCamelCase($extensionKey);
		$potentialControllerClassName = 'Tx_' . $extensionName . '_Controller_' . $controllerObjectShortName . 'Controller';
		if (FALSE === class_exists($potentialControllerClassName)) {
			if (TRUE === $failHardClass) {
				throw new Exception('Class ' . $potentialControllerClassName . ' does not exist. It was build from: ' . var_export($extensionKey, TRUE) .
					' but the resulting class name was not found.', 1364498093);
			}
			return NULL;
		}
		if (FALSE === method_exists($potentialControllerClassName, $action . 'Action')) {
			if (TRUE === $failHardAction) {
				throw new Exception('Class ' . $potentialControllerClassName . ' does not contain a method named ' . $action . 'Action', 1364498223);
			}
			return NULL;
		}
		return $potentialControllerClassName;
	}

}
