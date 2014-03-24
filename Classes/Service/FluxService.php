<?php
namespace FluidTYPO3\Flux\Service;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@wildside.dk>
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

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\PathUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\View\ExposedTemplateView;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Responsee;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Flux FlexForm integration Service
 *
 * Main API Service for interacting with Flux-based FlexForms
 *
 * @package Flux
 * @subpackage Service
 */
class FluxService implements SingletonInterface {

	/**
	 * @var boolean
	 */
	protected $silent = FALSE;

	/**
	 * @var array
	 */
	protected static $sentDebugMessages = array();

	/**
	 * @var array
	 */
	private static $friendlySeverities = array(
		GeneralUtility::SYSLOG_SEVERITY_INFO,
		GeneralUtility::SYSLOG_SEVERITY_NOTICE
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
	 * @var ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @param ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param ReflectionService $reflectionService
	 * @return void
	 */
	public function injectReflectionService(ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @return void
	 */
	public function initializeObject() {
		$this->loadTypoScriptConfigurationProviderInstances();
	}

	/**
	 * @param string $qualifiedExtensionName
	 * @param string $controllerName
	 * @param array $paths
	 * @param array $variables
	 * @return ExposedTemplateView
	 */
	public function getPreparedExposedTemplateView($qualifiedExtensionName = NULL, $controllerName = NULL, $paths = array(), $variables = array()) {
		$qualifiedExtensionName = GeneralUtility::camelCaseToLowerCaseUnderscored($qualifiedExtensionName);
		$extensionKey = ExtensionNamingUtility::getExtensionKey($qualifiedExtensionName);
		$vendorName = ExtensionNamingUtility::getVendorName($qualifiedExtensionName);
		if (NULL === $qualifiedExtensionName || FALSE === ExtensionManagementUtility::isLoaded($extensionKey)) {
			// Note here: a default value of the argument would not be adequate; outside callers could still pass NULL.
			$qualifiedExtensionName = 'Flux';
		}
		$extensionName = ExtensionNamingUtility::getExtensionName($qualifiedExtensionName);
		if (NULL === $controllerName) {
			$controllerName = 'Flux';
		}
		/** @var $context ControllerContext */
		$context = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext');
		/** @var $request Request */
		$request = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Request');
		/** @var $response Responsee */
		$response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
		$request->setControllerExtensionName($extensionName);
		$request->setControllerName($controllerName);
		$request->setControllerVendorName($vendorName);
		$request->setDispatched(TRUE);
		/** @var $uriBuilder UriBuilder */
		$uriBuilder = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder');
		$uriBuilder->setRequest($request);
		$context->setUriBuilder($uriBuilder);
		$context->setRequest($request);
		$context->setResponse($response);
		/** @var $exposedView ExposedTemplateView */
		$exposedView = $this->objectManager->get('FluidTYPO3\Flux\View\ExposedTemplateView');
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
	 * @return Form|NULL
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
		} catch (\Exception $error) {
			$this->debug($error);
			/** @var Form $form */
			self::$cache[$cacheKey] = $this->objectManager->get('FluidTYPO3\Flux\Form');
			self::$cache[$cacheKey]->add(self::$cache[$cacheKey]->createField('UserFunction', 'func')->setFunction('FluidTYPO3\Flux\UserFunction\ErrorReporter->renderField'));
		}
		return self::$cache[$cacheKey];
	}

	/**
	 * Reads a Grid constructed using flux:grid, returning an array of
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
	 * @return Grid|NULL
	 */
	public function getGridFromTemplateFile($templatePathAndFilename, $section = 'Configuration', $gridName = 'grid', array $paths = array(), $extensionName = NULL, array $variables = array()) {
		$grid = NULL;
		if (TRUE === file_exists($templatePathAndFilename)) {
			$exposedView = $this->getPreparedExposedTemplateView($extensionName, 'Flux', $paths, $variables);
			$exposedView->setTemplatePathAndFilename($templatePathAndFilename);
			$grid = $exposedView->getGrid($section, $gridName);
		}
		if (NULL === $grid) {
			$grid = Grid::create(array('name' => $gridName));
		}
		return $grid;
	}

	/**
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getViewConfigurationForExtensionName($extensionName) {
		$extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);
		$configuration = $this->getTypoScriptSubConfiguration(NULL, 'view', $extensionName);
		if (FALSE === is_array($configuration) || 0 === count($configuration) || TRUE === empty($configuration['templateRootPath'])) {
			$configuration = array(
				'templateRootPath' => 'EXT:' . $extensionKey . '/Resources/Private/Templates',
				'partialRootPath' => 'EXT:' . $extensionKey . '/Resources/Private/Partials',
				'layoutRootPath' => 'EXT:' . $extensionKey . '/Resources/Private/Layouts',
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
		$config = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (FALSE === isset($config[$superScope . '.']['tx_' . $containerExtensionScope . '.'][$memberName . '.'])) {
			return NULL;
		}
		$config = $config[$superScope . '.']['tx_' . $containerExtensionScope . '.'][$memberName . '.'];
		$config = GeneralUtility::removeDotsFromTS($config);
		if ($extensionName) {
			$config = $config[$extensionName];
		}
		if (FALSE === is_array($config)) {
			$config = array();
		}
		$config = PathUtility::translatePath($config);
		self::$cache[$cacheKey] = $config;
		return $config;
	}

	/**
	 * ResolveUtility the top-priority ConfigurationPrivider which can provide
	 * a working FlexForm configuration baed on the given parameters.
	 *
	 * @param string $table
	 * @param string $fieldName
	 * @param array $row
	 * @param string $extensionKey
	 * @return ProviderInterface|NULL
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
	 * @return ProviderInterface[]
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
		$providers = Core::getRegisteredFlexFormProviders();
		$typoScriptConfigurationProviders = $this->loadTypoScriptConfigurationProviderInstances();
		$providers = array_merge($providers, $typoScriptConfigurationProviders);
		$prioritizedProviders = array();
		foreach ($providers as $providerClassNameOrInstance) {
			if (is_object($providerClassNameOrInstance)) {
				$provider = &$providerClassNameOrInstance;
			} else {
				$provider = $this->objectManager->get($providerClassNameOrInstance);
			}
			if (FALSE === in_array('FluidTYPO3\Flux\Provider\ProviderInterface', class_implements($providerClassNameOrInstance))) {
				throw new \RuntimeException(is_object($providerClassNameOrInstance) ? get_class($providerClassNameOrInstance) : $providerClassNameOrInstance . ' must implement ProviderInterfaces from Flux/Provider', 1327173536);
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
	 * @return ProviderInterface[]
	 */
	protected function loadTypoScriptConfigurationProviderInstances() {
		$cacheKey = 'typoscript_providers';
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$typoScriptSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (FALSE === isset($typoScriptSettings['plugin.']['tx_flux.']['providers.'])) {
			return array();
		}
		$providerConfigurations = GeneralUtility::removeDotsFromTS($typoScriptSettings['plugin.']['tx_flux.']['providers.']);
		self::$cache[$cacheKey] = array();
		foreach ($providerConfigurations as $name => $providerSettings) {
			if (TRUE === isset($providerSettings['className']) && TRUE === class_exists($providerSettings['className'])) {
				$className = $providerSettings['className'];
			} else {
				$className = 'FluidTYPO3\Flux\Provider\Provider';
			}
			/** @var ProviderInterface $provider */
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
	 * @param Form $form An instance of \FluidTYPO3\Flux\Form. If transformation instructions are contained in this configuration they are applied after conversion to array
	 * @param string $languagePointer language pointer used in the flexForm
	 * @param string $valuePointer value pointer used in the flexForm
	 * @return array the processed array
	 */
	public function convertFlexFormContentToArray($flexFormContent, Form $form = NULL, $languagePointer = 'lDEF', $valuePointer = 'vDEF') {
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
		$decoded = GeneralUtility::xml2array($flexFormContent);
		if (FALSE === isset($decoded['data']) || FALSE === is_array($decoded['data'])) {
			return array();
		}
		$settings = $this->objectManager->get('TYPO3\CMS\Extbase\Service\FlexFormService')->convertFlexFormContentToArray($flexFormContent, $languagePointer, $valuePointer);
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
	 * @param Form $form
	 * @param string $prefix
	 * @return array
	 */
	public function transformAccordingToConfiguration($values, Form $form, $prefix = '') {
		foreach ((array) $values as $index => $value) {
			if (TRUE === is_array($value)) {
				$value = $this->transformAccordingToConfiguration($value, $form, $prefix . $index . '.');
			} else {
				/** @var FieldInterface|ContainerInterface $object */
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
		$identifiers = TRUE === is_array($uids) ? $uids : GeneralUtility::trimExplode(',', trim($uids, ','), TRUE);
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
				/** @var $repository RepositoryInterface */
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
	 * @param RepositoryInterface $repository
	 * @param array $identifiers
	 * @return mixed
	 */
	private function loadObjectsFromRepository(RepositoryInterface $repository, $identifiers) {
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
	 * @param boolean $plainText
	 * @param integer $depth
	 * @return void
	 */
	public function debug($instance, $plainText = FALSE, $depth = 2) {
		if (1 > $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']) {
			if (TRUE === $instance instanceof \Exception) {
				GeneralUtility::sysLog('Flux Debug: Suppressed Exception - "' . $instance->getMessage() . '" (' . $instance->getCode() . ')', 'flux');
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
		if (TRUE === $instance instanceof ExposedTemplateView) {
			$this->debugView($instance, $plainText, $depth);
		} elseif (TRUE === $instance instanceof ProviderInterface) {
			$this->debugProvider($instance, $plainText, $depth);
		} elseif (TRUE === $instance instanceof \Exception) {
			$this->debugException($instance, $plainText, $depth);
		} else {
			$this->debugMixed($instance, $plainText, $depth);
		}
		self::$sentDebugMessages[$hash] = TRUE;
	}

	/**
	 * @param mixed $variable
	 * @param boolean $plainText
	 * @param integer $depth
	 * @return void
	 */
	public function debugMixed($variable, $plainText = FALSE, $depth = 2) {
		$this->passToDebugger($variable, 'Flux variable debug', $depth, $plainText, FALSE);
	}

	/**
	 * @param \Exception $error
	 * @return void
	 */
	public function debugException(\Exception $error) {
		$this->message($error->getMessage() . ' (' . $error->getCode() . ')', GeneralUtility::SYSLOG_SEVERITY_FATAL);
	}

	/**
	 * @param ExposedTemplateView $view
	 * @param boolean $plainText
	 * @param integer $depth
	 * @return void
	 */
	public function debugView(ExposedTemplateView $view, $plainText = FALSE, $depth = 2) {
		$this->passToDebugger($view, 'Flux View debug', $depth, $plainText, FALSE);;
	}

	/**
	 * @param ProviderInterface $provider
	 * @param boolean $plainText
	 * @param integer $depth
	 * @return void
	 */
	public function debugProvider(ProviderInterface $provider, $plainText = FALSE, $depth = 2) {
		$this->passToDebugger($provider, 'Flux Provider debug', $depth, $plainText, FALSE);
	}

	/**
	 * @return void
	 */
	protected function passToDebugger() {
		if (FALSE === $this->silent) {
			call_user_func_array(array('TYPO3\CMS\Extbase\Utility\DebuggerUtility', 'var_dump'), array(func_get_args()));
		}
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @param string $title
	 * @return NULL
	 */
	public function message($message, $severity = GeneralUtility::SYSLOG_SEVERITY_INFO, $title = 'Flux Debug') {
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
		$isAjaxCall = (boolean) 0 < GeneralUtility::_GET('ajaxCall');
		$flashMessage = new FlashMessage($message, $title, $severity);
		$flashMessage->setStoreInSession($isAjaxCall);
		FlashMessageQueue::addMessage($flashMessage);
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
