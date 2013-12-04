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
 * Flux FlexForm integration Service
 *
 * Main API Service for interacting with Flux-based FlexForms
 *
 * @package Flux
 * @subpackage Service
 */
class Tx_Flux_Service_FluxService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var array
	 */
	protected static $sentDebugMessages = array();

	/**
	 * @var array
	 */
	private static $friendlySeverities = array(
		\TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_INFO,
		\TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_NOTICE
	);

	/**
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * @var string
	 */
	protected $raw;

	/**
	 * @var array
	 */
	protected $contentObjectData;

	/**
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService
	 * @return void
	 */
	public function injectReflectionService(\TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @return void
	 */
	public function initializeObject() {
		$this->loadTypoScriptConfigurationProviderInstances();
	}

	/**
	 * @param string $extensionKey
	 * @param string $controllerName
	 * @param array $paths
	 * @param array $variables
	 * @return Tx_Flux_View_ExposedTemplateView
	 */
	public function getPreparedExposedTemplateView($extensionKey = NULL, $controllerName = NULL, $paths = array(), $variables = array()) {
		$extensionKey = \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($extensionKey);
		if (NULL === $extensionKey || FALSE === \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extensionKey)) {
			// Note here: a default value of the argument would not be adequate; outside callers could still pass NULL.
			$extensionKey = 'Flux';
		}
		if (NULL === $controllerName) {
			$controllerName = 'Flux';
		}
		/** @var $context \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext */
		$context = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ControllerContext');
		/** @var $request \TYPO3\CMS\Extbase\Mvc\Web\Request */
		$request = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Request');
		/** @var $response Tx_Extbase_MVC_Web_Response */
		$response = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response');
		$request->setControllerExtensionName($extensionKey);
		$request->setControllerName($controllerName);
		$request->setDispatched(TRUE);
		/** @var $uriBuilder \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder */
		$uriBuilder = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
		$uriBuilder->setRequest($request);
		$context->setUriBuilder($uriBuilder);
		$context->setRequest($request);
		$context->setResponse($response);
		/** @var $exposedView Tx_Flux_View_ExposedTemplateView */
		$exposedView = $this->objectManager->get('Tx_Flux_View_ExposedTemplateView');
		$exposedView->setControllerContext($context);
		if (0 < count($variables)) {
			$exposedView->assignMultiple($variables);
		}
		if (TRUE === isset($paths['layoutRootPath']) && FALSE === empty($paths['layoutRootPath'])) {
			$exposedView->setLayoutRootPath($paths['layoutRootPath']);
		}
		if (TRUE === isset($paths['partialRootPath']) && FALSE === empty($paths['partialRootPath'])) {
			$exposedView->setPartialRootPath($paths['partialRootPath']);
		}
		if (TRUE === isset($paths['templateRootPath']) && FALSE === empty($paths['templateRootPath'])) {
			$exposedView->setTemplateRootPath($paths['templateRootPath']);
		}
		return $exposedView;
	}

	/**
	 * @param string $templatePathAndFilename
	 * @param string $section
	 * @param string $formName
	 * @param array $paths
	 * @param string $extensionName
	 * @param array $variables
	 * @return Tx_Flux_Form|NULL
	 * @throws Exception
	 */
	public function getFormFromTemplateFile($templatePathAndFilename, $section = 'Configuration', $formName = 'form', $paths = array(), $extensionName = NULL, $variables = array()) {
		if (FALSE === file_exists($templatePathAndFilename)) {
			return NULL;
		}
		$variableCheck = json_encode($variables);
		$cacheKey = md5($templatePathAndFilename . $formName . $extensionName . implode('', $paths) . $section . $variableCheck);
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		try {
			$exposedView = $this->getPreparedExposedTemplateView($extensionName, 'Flux', $paths, $variables);
			$exposedView->setTemplatePathAndFilename($templatePathAndFilename);
			self::$cache[$cacheKey] = $exposedView->getForm($section, $formName);
		} catch (Exception $error) {
			$this->debug($error);
			/** @var Tx_Flux_Form $form */
			self::$cache[$cacheKey] = $this->objectManager->get('Tx_Flux_Form');
			self::$cache[$cacheKey]->add(self::$cache[$cacheKey]->createField('UserFunction', 'func')->setFunction('Tx_Flux_UserFunction_ErrorReporter->renderField'));
		}
		return self::$cache[$cacheKey];
	}

	/**
	 * Reads a Grid constructed using flux:flexform.grid, returning an array of
	 * defined rows and columns along with any content areas.
	 *
	 * Note about specific implementations:
	 *
	 * * EXT:fluidpages uses the Grid to render a BackendLayout on TYPO3 6.0 and above
	 * * EXT:flux uses the Grid to render content areas inside content elements
	 *   registered with Flux
	 *
	 * But your custom extension is of course allowed to use the Grid for any
	 * purpose. You can even read the Grid from - for example - the currently
	 * selected page template to know exactly how the BackendLayout looks.
	 *
	 * @param string $templatePathAndFilename
	 * @param string $section
	 * @param string $gridName
	 * @param array $paths
	 * @param string $extensionName
	 * @param array $variables
	 * @return Tx_Flux_Form_Container_Grid|NULL
	 * @throws Exception
	 */
	public function getGridFromTemplateFile($templatePathAndFilename, $section = 'Configuration', $gridName = 'grid', array $paths = array(), $extensionName = NULL, array $variables = array()) {
		$grid = NULL;
		if (TRUE === file_exists($templatePathAndFilename)) {
			$exposedView = $this->getPreparedExposedTemplateView($extensionName, 'Flux', $paths, $variables);
			$exposedView->setTemplatePathAndFilename($templatePathAndFilename);
			$grid = $exposedView->getGrid($section, $gridName);
		}
		if (NULL === $grid) {
			$grid = Tx_Flux_Form_Container_Grid::create(array('name' => $gridName));
		}
		return $grid;
	}

	/**
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getViewConfigurationForExtensionName($extensionName) {
		$configuration = $this->getTypoScriptSubConfiguration(NULL, 'view', $extensionName);
		if (FALSE === is_array($configuration) || 0 === count($configuration) || TRUE === empty($configuration['templateRootPath'])) {
			$configuration = array(
				'templateRootPath' => 'EXT:' . $extensionName . '/Resources/Private/Templates',
				'partialRootPath' => 'EXT:' . $extensionName . '/Resources/Private/Partials',
				'layoutRootPath' => 'EXT:' . $extensionName . '/Resources/Private/Layouts',
			);
		}
		return $configuration;
	}

	/**
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getBackendViewConfigurationForExtensionName($extensionName) {
		$configuration = $this->getTypoScriptSubConfiguration(NULL, 'view', $extensionName, 'module');
		return $configuration;
	}

	/**
	 * Gets an array of TypoScript configuration from below plugin.tx_fed -
	 * if $extensionName is set in parameters it is used to indicate which sub-
	 * section of the result to return.
	 *
	 * @param string $extensionName
	 * @param string $memberName
	 * @param string $containerExtensionScope If TypoScript is not located under plugin.tx_fed, change the tx_<scope> part by specifying this argument
	 * @param string $superScope Either "plugin" or "module", depending on the root scope
	 * @return array
	 */
	public function getTypoScriptSubConfiguration($extensionName, $memberName, $containerExtensionScope = 'fed', $superScope = 'plugin') {
		$containerExtensionScope = str_replace('_', '', $containerExtensionScope);
		$cacheKey = $extensionName . $memberName . $containerExtensionScope;
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$config = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (FALSE === isset($config[$superScope . '.']['tx_' . $containerExtensionScope . '.'][$memberName . '.'])) {
			return NULL;
		}
		$config = $config[$superScope . '.']['tx_' . $containerExtensionScope . '.'][$memberName . '.'];
		$config = \TYPO3\CMS\Core\Utility\GeneralUtility::removeDotsFromTS($config);
		if ($extensionName) {
			$config = $config[$extensionName];
		}
		if (FALSE === is_array($config)) {
			$config = array();
		}
		$config = Tx_Flux_Utility_Path::translatePath($config);
		self::$cache[$cacheKey] = $config;
		return $config;
	}

	/**
	 * Resolve the top-priority ConfigurationPrivider which can provide
	 * a working FlexForm configuration baed on the given parameters.
	 *
	 * @param string $table
	 * @param string $fieldName
	 * @param array $row
	 * @param string $extensionKey
	 * @return Tx_Flux_Provider_ProviderInterface|NULL
	 */
	public function resolvePrimaryConfigurationProvider($table, $fieldName, array $row = NULL, $extensionKey = NULL) {
		if (is_array($row) === FALSE) {
			$row = array();
		}
		$rowIdentity = TRUE === isset($row['uid']) ? $row['uid'] : NULL;
		$cacheKey = $table . $fieldName . $rowIdentity . $extensionKey . 'top';
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$providers = $this->resolveConfigurationProviders($table, $fieldName, $row, $extensionKey);
		$priority = 0;
		$providerWithTopPriority = NULL;
		foreach ($providers as $provider) {
			if ($provider->getPriority($row) >= $priority) {
				$providerWithTopPriority = $provider;
			}
		}
		self::$cache[$cacheKey] = $providerWithTopPriority;
		return $providerWithTopPriority;
	}

	/**
	 * Resolves a ConfigurationProvider which can provide a working FlexForm
	 * configuration based on the given parameters.
	 *
	 * @param string $table
	 * @param string $fieldName
	 * @param array $row
	 * @param string $extensionKey
	 * @return Tx_Flux_Provider_ProviderInterface[]
	 */
	public function resolveConfigurationProviders($table, $fieldName, array $row = NULL, $extensionKey = NULL) {
		if (is_array($row) === FALSE) {
			$row = array();
		}
		$rowIdentity = TRUE === isset($row['uid']) ? $row['uid'] : uniqid();
		$cacheKey = $table . $fieldName . $rowIdentity . $extensionKey;
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$providers = Tx_Flux_Core::getRegisteredFlexFormProviders();
		$typoScriptConfigurationProviders = $this->loadTypoScriptConfigurationProviderInstances();
		$providers = array_merge($providers, $typoScriptConfigurationProviders);
		$prioritizedProviders = array();
		foreach ($providers as $providerClassNameOrInstance) {
			if (is_object($providerClassNameOrInstance)) {
				$provider = &$providerClassNameOrInstance;
			} else {
				$provider = $this->objectManager->get($providerClassNameOrInstance);
			}
			if (TRUE === $provider->trigger($row, $table, $fieldName, $extensionKey)) {
				$priority = $provider->getPriority($row);
				if (FALSE === is_array($prioritizedProviders[$priority])) {
					$prioritizedProviders[$priority] = array();
				}
				$prioritizedProviders[$priority][] = $provider;
			}
		}
		ksort($prioritizedProviders);
		$providersToReturn = array();
		foreach ($prioritizedProviders as $providerSet) {
			foreach ($providerSet as $provider) {
				array_push($providersToReturn, $provider);
			}
		}
		self::$cache[$cacheKey] = $providersToReturn;
		return $providersToReturn;
	}

	/**
	 * @return Tx_Flux_Provider_ProviderInterface[]
	 */
	protected function loadTypoScriptConfigurationProviderInstances() {
		$cacheKey = 'typoscript_providers';
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$typoScriptSettings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (FALSE === isset($typoScriptSettings['plugin.']['tx_flux.']['providers.'])) {
			return array();
		}
		$providerConfigurations = \TYPO3\CMS\Core\Utility\GeneralUtility::removeDotsFromTS($typoScriptSettings['plugin.']['tx_flux.']['providers.']);
		self::$cache[$cacheKey] = array();
		foreach ($providerConfigurations as $name => $providerSettings) {
			if (TRUE === isset($providerSettings['className']) && TRUE === class_exists($providerSettings['className'])) {
				$className = $providerSettings['className'];
			} else {
				$className = 'Tx_Flux_Provider_Provider';
			}
			/** @var Tx_Flux_Provider_ProviderInterface $provider */
			$provider = $this->objectManager->get($className);
			$provider->setName($name);
			$provider->loadSettings($providerSettings);
			self::$cache[$cacheKey][$name] = $provider;
		}
		return self::$cache[$cacheKey];
	}

	/**
	 * Parses the flexForm content and converts it to an array
	 * The resulting array will be multi-dimensional, as a value "bla.blubb"
	 * results in two levels, and a value "bla.blubb.bla" results in three levels.
	 *
	 * Note: multi-language flexForms are not supported yet
	 *
	 * @param string $flexFormContent flexForm xml string
	 * @param Tx_Flux_Form $form An instance of Tx_Flux_Form. If transformation instructions are contained in this configuration they are applied after conversion to array
	 * @param string $languagePointer language pointer used in the flexForm
	 * @param string $valuePointer value pointer used in the flexForm
	 * @return array the processed array
	 */
	public function convertFlexFormContentToArray($flexFormContent, Tx_Flux_Form $form = NULL, $languagePointer = 'lDEF', $valuePointer = 'vDEF') {
		if (TRUE === empty($flexFormContent)) {
			return array();
		}
		if (TRUE === empty($languagePointer)) {
			$languagePointer = 'lDEF';
		}
		if (TRUE === empty($valuePointer)) {
			$valuePointer = 'vDEF';
		}
		// preliminary decode. The method called caches the decoded results so we can do almost without performance impact.
		$decoded = t3lib_div::xml2array($flexFormContent);
		if (FALSE === isset($decoded['data']) || FALSE === is_array($decoded['data'])) {
			return array();
		}
		$settings = $this->objectManager->get('Tx_Extbase_Service_FlexFormService')->convertFlexFormContentToArray($flexFormContent, $languagePointer, $valuePointer);
		if (NULL !== $form) {
			$settings = $this->transformAccordingToConfiguration($settings, $form);
		}
		return $settings;
	}

	/**
	 * Transforms members on $values recursively according to the provided
	 * Flux configuration extracted from a Flux template. Uses "transform"
	 * attributes on fields to determine how to transform values.
	 *
	 * @param array $values
	 * @param Tx_Flux_Form $form
	 * @param string $prefix
	 * @return array
	 */
	public function transformAccordingToConfiguration($values, Tx_Flux_Form $form, $prefix = '') {
		foreach ((array) $values as $index => $value) {
			if (TRUE === is_array($value)) {
				$value = $this->transformAccordingToConfiguration($value, $form, ltrim($prefix . '.' . $index . '.', '.'));
			} else {
				/** @var Tx_Flux_Form_FieldInterface|Tx_Flux_Form_ContainerInterface $object */
				$object = $form->get($prefix . $index, TRUE);
				if (FALSE !== $object) {
					$transformType = $object->getTransform();
					$value = $this->transformValueToType($value, $transformType);
				}
			}
			$values[$index] = $value;
		}
		return $values;
	}

	/**
	 * Transforms a single value to $dataType
	 *
	 * @param string $value
	 * @param string $dataType
	 * @return mixed
	 */
	protected function transformValueToType($value, $dataType) {
		if ('int' === $dataType || 'integer' === $dataType) {
			return intval($value);
		} elseif ('float' === $dataType) {
			return floatval($value);
		} elseif ('array' === $dataType) {
			return explode(',', $value);
		} else {
			return $this->getObjectOfType($dataType, $value);
		}
	}

	/**
	 * Gets a DomainObject or QueryResult of $dataType
	 *
	 * @param string $dataType
	 * @param string $uids
	 * @return mixed
	 */
	private function getObjectOfType($dataType, $uids) {
		$identifiers = TRUE === is_array($uids) ? $uids : \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', trim($uids, ','), TRUE);
		$identifiers = array_map('intval', $identifiers);
		$isModel = (FALSE !== strpos($dataType, '_Domain_Model_') || FALSE !== strpos($dataType, '\\Domain\\Model\\'));
		list ($container, $object) = FALSE !== strpos($dataType, '<') ? explode('<', trim($dataType, '>')) : array(NULL, $dataType);
		$repositoryClassName = str_replace('_Domain_Model_', '_Domain_Repository_', str_replace('\\Domain\\Model\\', '\\Domain\\Repository\\', $object)) . 'Repository';
		// Fast decisions
		if (TRUE === $isModel && NULL === $container) {
			if (TRUE === class_exists($repositoryClassName)) {
				$repository = $this->objectManager->get($repositoryClassName);
				$uid = array_pop($identifiers);
				return $repository->findOneByUid($uid);
			}
		} elseif (TRUE === class_exists($dataType)) {
			// using constructor value to support objects like DateTime
			return $this->objectManager->get($dataType, $uids);
		}
		// slower decisions with support for type-hinted collection objects
		if ($container && $object) {
			if (TRUE === $isModel && TRUE === class_exists($repositoryClassName) && 0 < count($identifiers)) {
				/** @var $repository Tx_Extbase_Persistence_RepositoryInterface */
				$repository = $this->objectManager->get($repositoryClassName);
				return $this->loadObjectsFromRepository($repository, $identifiers);
			} else {
				$container = $this->objectManager->get($container);
				return $container;
			}
		}
		return $uids;
	}

	/**
	 * @param Tx_Extbase_Persistence_RepositoryInterface $repository
	 * @param array $identifiers
	 * @return mixed
	 */
	private function loadObjectsFromRepository(Tx_Extbase_Persistence_RepositoryInterface $repository, $identifiers) {
		if (TRUE === method_exists($repository, 'findByIdentifiers')) {
			return $repository->findByIdentifiers($identifiers);
		} else {
			$query = $repository->createQuery();
			$query->matching($query->in('uid', $identifiers));
			return $query->execute();
		}
	}

	/**
	 * @param mixed $instance
	 * @return void
	 */
	public function debug($instance) {
		if (1 > $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']) {
			if (TRUE === $instance instanceof Exception) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::sysLog('Flux Debug: Suppressed Exception - "' . $instance->getMessage() . '" (' . $instance->getCode() . ')', 'flux');
			}
			return;
		}
		if (TRUE === is_object($instance)) {
			$hash = spl_object_hash($instance);
		} else {
			$hash = microtime(TRUE);
		}
		if (TRUE === isset(self::$sentDebugMessages[$hash])) {
			return;
		}
		if (TRUE === $instance instanceof Tx_Flux_View_ExposedTemplateView) {
			$this->debugView($instance);
		} elseif (TRUE === $instance instanceof Tx_Flux_Provider_ProviderInterface) {
			$this->debugProvider($instance);
		} elseif (TRUE === $instance instanceof Exception) {
			$this->debugException($instance);
		} else {
			$this->debugMixed($instance);
		}
		self::$sentDebugMessages[$hash] = TRUE;
	}

	/**
	 * @param mixed $variable
	 * @return void
	 */
	public function debugMixed($variable) {
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($variable);
	}

	/**
	 * @param Exception $error
	 * @return void
	 */
	public function debugException(Exception $error) {
		$this->message($error->getMessage() . ' (' . $error->getCode() . ')', \TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_FATAL);
	}

	/**
	 * @param Tx_Flux_View_ExposedTemplateView $view
	 * @return void
	 */
	public function debugView(Tx_Flux_View_ExposedTemplateView $view) {
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($view);
	}

	/**
	 * @param Tx_Flux_Provider_ProviderInterface $provider
	 * @return void
	 */
	public function debugProvider(Tx_Flux_Provider_ProviderInterface $provider) {
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($provider);
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @param string $title
	 * @return NULL
	 */
	public function message($message, $severity = \TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_INFO, $title = 'Flux Debug') {
		if (1 > $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']) {
			return NULL;
		}
		$hash = $message . $severity;
		if (TRUE === isset(self::$sentDebugMessages[$hash])) {
			return NULL;
		}
		if (2 == $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] && TRUE === in_array($severity, self::$friendlySeverities)) {
			return NULL;
		}
		$isAjaxCall = (boolean) 0 < \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('ajaxCall');
		$flashMessage = new \TYPO3\CMS\Core\Messaging\FlashMessage($message, $title, $severity);
		$flashMessage->setStoreInSession($isAjaxCall);
		\TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($flashMessage);
		self::$sentDebugMessages[$hash] = TRUE;
		return NULL;
	}

	/**
	 * @return void
	 */
	public function flushCache() {
		self::$cache = array();
	}

}
