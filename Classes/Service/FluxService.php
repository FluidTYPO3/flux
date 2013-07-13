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
class Tx_Flux_Service_FluxService implements t3lib_Singleton {

	/**
	 * @var array
	 */
	private static $sentDebugMessages = array();

	/**
	 * @var array
	 */
	private static $friendlySeverities = array(
		t3lib_div::SYSLOG_SEVERITY_INFO,
		t3lib_div::SYSLOG_SEVERITY_NOTICE
	);

	/**
	 * @var array
	 */
	private static $cache = array();

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
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_Extbase_Reflection_Service $reflectionService
	 * @return void
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @param string $extensionName
	 * @param string $controllerName
	 * @return Tx_Flux_MVC_View_ExposedTemplateView
	 */
	public function getPreparedExposedTemplateView($extensionName = NULL, $controllerName = NULL) {
		if (NULL === $extensionName || FALSE === t3lib_extMgm::isLoaded($extensionName)) {
			// Note here: a default value of the argument would not be adequate; outside callers could still pass NULL.
			$extensionName = 'Flux';
		}
		if (NULL === $controllerName) {
			$controllerName = 'Flux';
		}
		/** @var $context Tx_Extbase_MVC_Controller_ControllerContext */
		$context = $this->objectManager->get('Tx_Extbase_MVC_Controller_ControllerContext');
		/** @var $request Tx_Extbase_MVC_Web_Request */
		$request = $this->objectManager->get('Tx_Extbase_MVC_Web_Request');
		/** @var $response Tx_Extbase_MVC_Web_Response */
		$response = $this->objectManager->get('Tx_Extbase_MVC_Web_Response');
		$request->setControllerExtensionName($extensionName);
		$request->setControllerName($controllerName);
		$request->setDispatched(TRUE);
		/** @var $uriBuilder Tx_Extbase_Mvc_Web_Routing_UriBuilder */
		$uriBuilder = $this->objectManager->get('Tx_Extbase_Mvc_Web_Routing_UriBuilder');
		$uriBuilder->setRequest($request);
		$context->setRequest($request);
		$context->setResponse($response);
		/** @var $exposedView Tx_Flux_MVC_View_ExposedTemplateView */
		$exposedView = $this->objectManager->get('Tx_Flux_MVC_View_ExposedTemplateView');
		$exposedView->setControllerContext($context);
		return $exposedView;
	}

	/**
	 * @param string $templatePathAndFilename
	 * @param string $variableName
	 * @param string $section
	 * @param array $paths
	 * @param string|NULL $extensionName
	 * @param array $variables
	 * @throws Exception
	 * @return mixed
	 */
	public function getStoredVariable($templatePathAndFilename, $variableName, $section = 'Configuration', $paths = array(), $extensionName = NULL, $variables = array()) {
		if (FALSE === file_exists($templatePathAndFilename)) {
			throw new Exception('The template file "' . $templatePathAndFilename . '" was not found.', 1366824347);
		}
		$variableCheck = json_encode($variables);
		$actionName = strtolower(pathinfo($templatePathAndFilename, PATHINFO_FILENAME));
		$cacheKey = md5($templatePathAndFilename . $variableName . $extensionName . implode('', $paths) . $section . $variableCheck);
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$exposedView = $this->getPreparedExposedTemplateView($extensionName);
		$exposedView->setTemplatePathAndFilename($templatePathAndFilename);
		if (0 < count($variables)) {
			$exposedView->assignMultiple($variables);
		}
		if (TRUE === isset($paths['layoutRootPath'])) {
			$exposedView->setLayoutRootPath($paths['layoutRootPath']);
		}
		if (TRUE === isset($paths['partialRootPath'])) {
			$exposedView->setPartialRootPath($paths['partialRootPath']);
		}
		$value = $exposedView->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', $variableName, $section, $paths, $extensionName, $actionName);
		self::$cache[$cacheKey] = $value;
		return $value;
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
	 * @param array $variables
	 * @param string|NULL $configurationSection
	 * @param array $paths
	 * @param string $extensionName
	 * @return array
	 * @throws Exception
	 */
	public function getGridFromTemplateFile($templatePathAndFilename, array $variables = array(), $configurationSection = NULL, array $paths = array(), $extensionName = NULL) {
		try {
			$paths = Tx_Flux_Utility_Path::translatePath($paths);
			$stored = $this->getStoredVariable($templatePathAndFilename, 'storage', $configurationSection, $paths, $extensionName, $variables);
			$grid = isset($stored['grid']) ? $stored['grid'] : NULL;
		} catch (Exception $error) {
			if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] > 0) {
				throw $error;
			} else {
				t3lib_div::sysLog($error->getMessage(), 'flux');
			}
			$grid = array();
		}
		return $grid;
	}

	/**
	 * Gets a stored FlexForm configuration and applies any dynamic values to
	 * create a current representation of the FlexForm sheet+fields array
	 *
	 * @param string $templateFile The absolute filename containing the configuration
	 * @param mixed $values Optional values to use when rendering the configuration
	 * @param string|NULL $section Optional section name containing the configuration
	 * @param array|NULL $paths Template paths; required if template renders Partials (from inside section if $section != NULL)
	 * @param string|NULL $extensionName If specified, uses this extensionName in an injected ControllerContext
	 * @throws Exception
	 * @return array
	 */
	public function getFlexFormConfigurationFromFile($templateFile, $values, $section = NULL, $paths = NULL, $extensionName = NULL) {
		$config = NULL;
		try {
			$config = $this->getStoredVariable($templateFile, 'storage', $section, $paths, $extensionName, $values);
		} catch (Exception $error) {
			$this->message('Reading file ' . $templateFile . ' caused an error - see next message', t3lib_div::SYSLOG_SEVERITY_FATAL);
			$this->debug($error);
		}
		return $config;
	}

	/**
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getViewConfigurationForExtensionName($extensionName) {
		$configuration = $this->getTypoScriptSubConfiguration(NULL, 'view', $extensionName);
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
		$config = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (FALSE === isset($config[$superScope . '.']['tx_' . $containerExtensionScope . '.'][$memberName . '.'])) {
			return NULL;
		}
		$config = $config[$superScope . '.']['tx_' . $containerExtensionScope . '.'][$memberName . '.'];
		$config = Tx_Flux_Utility_Array::convertTypoScriptArrayToPlainArray($config);
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
	 * @param string $extensionKey
	 * @param string $controllerName
	 * @return boolean
	 */
	public function detectControllerClassPresenceFromExtensionKeyAndControllerType($extensionKey, $controllerName) {
		if (FALSE !== strpos($extensionKey, '.')) {
			list ($vendorName, $extensionName) = explode('.', $extensionKey);
			$potentialClassName = $vendorName . '\\' . $extensionName . '\\Controller\\' . $controllerName . 'Controller';
			if (TRUE === class_exists($potentialClassName)) {
				return TRUE;
			}
		}
		$extensionName = t3lib_div::underscoredToUpperCamelCase($extensionKey);
		$potentialClassName = 'Tx_' . $extensionName . '_Controller_' . $controllerName . 'Controller';
		return class_exists($potentialClassName);
	}

	/**
	 * Resolve the top-priority ConfigurationPrivider which can provide
	 * a working FlexForm configuration baed on the given parameters.
	 *
	 * @param string $table
	 * @param string $fieldName
	 * @param array $row
	 * @param string $extensionKey
	 * @return Tx_Flux_Provider_ConfigurationProviderInterface|NULL
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
	 * @return Tx_Flux_Provider_ConfigurationProviderInterface[]
	 */
	public function resolveConfigurationProviders($table, $fieldName, array $row=NULL, $extensionKey=NULL) {
		if (is_array($row) === FALSE) {
			$row = array();
		}
		$rowIdentity = TRUE === isset($row['uid']) ? $row['uid'] : NULL;
		$cacheKey = $table . $fieldName . $rowIdentity . $extensionKey;
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$bindToFieldName = Tx_Flux_Utility_Version::assertHasFixedFlexFormFieldNamePassing();
		$providers = Tx_Flux_Core::getRegisteredFlexFormProviders();
		$prioritizedProviders = array();
		foreach ($providers as $providerClassNameOrInstance) {
			if (is_object($providerClassNameOrInstance)) {
				$provider = &$providerClassNameOrInstance;
			} else {
				$providerCacheKey = $table . $fieldName . $rowIdentity . $extensionKey . $providerClassNameOrInstance;
				if (TRUE === isset(self::$cache[$providerCacheKey])) {
					$provider = &self::$cache[$providerCacheKey];
				} else {
					$provider = $this->objectManager->get($providerClassNameOrInstance);
				}
			}
			$priority = $provider->getPriority($row);
			$providerFieldName = $provider->getFieldName($row);
			$providerExtensionKey = $provider->getExtensionKey($row);
			$providerTableName = $provider->getTableName($row);
			if (FALSE === is_array($prioritizedProviders[$priority])) {
				$prioritizedProviders[$priority] = array();
			}
			$matchesTableName = ($providerTableName === $table);
			$matchesFieldName = ($providerFieldName === $fieldName || FALSE === $bindToFieldName || NULL === $fieldName);
			$matchesExtensionKey = ($providerExtensionKey === $extensionKey || NULL === $extensionKey);
			/** @var Tx_Flux_Provider_ConfigurationProviderInterface $provider */
			if ($matchesExtensionKey && $matchesTableName && $matchesFieldName) {
				if ($provider instanceof Tx_Flux_Provider_ContentObjectConfigurationProviderInterface) {
					/** @var Tx_Flux_Provider_ContentObjectConfigurationProviderInterface $provider */
					if (FALSE === isset($row['CType']) || $provider->getContentObjectType($row) === $row['CType']) {
						$prioritizedProviders[$priority][] = $provider;
					}
				} elseif (TRUE === $provider instanceof Tx_Flux_Provider_PluginConfigurationProviderInterface) {
					/** @var Tx_Flux_Provider_PluginConfigurationProviderInterface $provider */
					if (FALSE === isset($row['list_type']) || $provider->getListType($row) === $row['list_type']) {
						$prioritizedProviders[$priority][] = $provider;
					}
				} else {
					$prioritizedProviders[$priority][] = $provider;
				}
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
	 * @param array $config
	 * @return array|NULL
	 */
	public function convertFlexFormConfigurationToDataStructure($config) {
		/** @var $flexFormStructureProvider Tx_Flux_Provider_Structure_FlexFormStructureProvider */
		$flexFormStructureProvider = $this->objectManager->get('Tx_Flux_Provider_Structure_FlexFormStructureProvider');
		$dataStructArray = $flexFormStructureProvider->render($config);
		if ((FALSE === is_array($dataStructArray['ROOT']['el']) && FALSE === is_array($dataStructArray['sheets'])) || (count($dataStructArray['sheets']) < 1 && count($dataStructArray['ROOT']['el']) < 1 && count($dataStructArray['sheets'][key($dataStructArray['sheets'])]) === 0)) {
			$config['parameters'] = array(
				'userFunction' => 'Tx_Flux_UserFunction_NoFields->renderField'
			);
			$dataStructArray = $this->objectManager->get('Tx_Flux_Provider_Structure_FallbackStructureProvider')->render($config);
		}
		return $dataStructArray;
	}

	/**
	 * Updates $dataStructArray by reference, filling it with a proper data structure
	 * based on the selected template file.
	 *
	 * @param string $templateFile
	 * @param array $values
	 * @param array $paths
	 * @param array $dataStructArray
	 * @param string $section
	 * @param string $extensionName
	 * @throws Exception
	 * @return void
	 */
	public function convertFlexFormTemplateToDataStructure($templateFile, $values, $paths, &$dataStructArray, $section = NULL, $extensionName = NULL) {
		$className = get_class($this);
		try {
			if (NULL === $templateFile) {
				$this->message('A template file path was NULL - this might indicate an error in class ' . $className);
				$config['parameters'] = array(
					'userFunction' => 'Tx_Flux_UserFunction_NoTemplate->renderField'
				);
				$dataStructArray = $this->objectManager->get('Tx_Flux_Provider_Structure_FallbackStructureProvider')->render($config);
				return;
			}
			$config = $this->getFlexFormConfigurationFromFile($templateFile, $values, $section, $paths, $extensionName);
			$dataStructArray = $this->convertFlexFormConfigurationToDataStructure($config);
		} catch (Exception $e) {
			$this->message('Attempting to convert FlexForm XML to array using file ' . $templateFile . ' failed - ' .
				'see next error message');
			$this->debug($e);
			$config['parameters'] = array(
				'exception' => $e,
				'userFunction' => 'Tx_Flux_UserFunction_ErrorReporter->renderField'
			);
			if (FALSE === t3lib_extMgm::isLoaded('templavoila')) {
				$dataStructArray = $this->objectManager->get('Tx_Flux_Provider_Structure_FallbackStructureProvider')->render($config);
			}
		}
	}

	/**
	 * Parses the flexForm content and converts it to an array
	 * The resulting array will be multi-dimensional, as a value "bla.blubb"
	 * results in two levels, and a value "bla.blubb.bla" results in three levels.
	 *
	 * Note: multi-language flexForms are not supported yet
	 *
	 * @param string $flexFormContent flexForm xml string
	 * @param array $fluxConfiguration An array from a Flux template file. If transformation instructions are contained in this configuration they are applied after conversion to array
	 * @param string $languagePointer language pointer used in the flexForm
	 * @param string $valuePointer value pointer used in the flexForm
	 * @return array the processed array
	 */
	public function convertFlexFormContentToArray($flexFormContent, $fluxConfiguration = NULL, $languagePointer = 'lDEF', $valuePointer = 'vDEF') {
		$settings = array();
		if (TRUE === empty($languagePointer)) {
			$languagePointer = 'lDEF';
		}
		if (TRUE === empty($valuePointer)) {
			$valuePointer = 'vDEF';
		}
		$flexFormArray = t3lib_div::xml2array($flexFormContent);
		$flexFormArray = (TRUE === isset($flexFormArray['data']) && TRUE === is_array($flexFormArray['data']) ? $flexFormArray['data'] : $flexFormArray);
		if (FALSE === is_array($flexFormArray)) {
			return $settings;
		}
		foreach (array_values($flexFormArray) as $languages) {
			if (!is_array($languages) || !isset($languages[$languagePointer])) {
				continue;
			}
			if (!is_array($languages[$languagePointer])) {
				$currentNode = $languages[$languagePointer];
				continue;
			}
			foreach ($languages[$languagePointer] as $valueKey => $valueDefinition) {
				if (FALSE === strpos($valueKey, '.')) {
					$settings[$valueKey] = $this->walkFlexFormNode($valueDefinition, $valuePointer);
				} else {
					$valueKeyParts = explode('.', $valueKey);
					$currentNode =& $settings;

					foreach ($valueKeyParts as $valueKeyPart) {
						$currentNode =& $currentNode[$valueKeyPart];
					}

					if (is_array($valueDefinition)) {
						if (array_key_exists($valuePointer, $valueDefinition)) {
							$currentNode = $valueDefinition[$valuePointer];
						} else {
							$currentNode = $this->walkFlexFormNode($valueDefinition, $valuePointer);
						}
					} else {
						$currentNode = $valueDefinition;
					}
				}
			}
		}
		if (TRUE === is_array($fluxConfiguration)) {
			$settings = $this->transformAccordingToConfiguration($settings, $fluxConfiguration);
		}
		return $settings;
	}

	/**
	 * Parses a flexForm node recursively and takes care of sections etc
	 *
	 * @param array $nodeArray The flexForm node to parse
	 * @param string $valuePointer The valuePointer to use for value retrieval
	 * @return array
	 */
	private function walkFlexFormNode($nodeArray, $valuePointer = 'vDEF') {
		if (is_array($nodeArray)) {
			$return = array();
			foreach ($nodeArray as $nodeKey => $nodeValue) {
				if ($nodeKey === $valuePointer) {
					return $nodeValue;
				}
				if (in_array($nodeKey, array('el', '_arrayContainer'))) {
					return $this->walkFlexFormNode($nodeValue, $valuePointer);
				}
				if (substr($nodeKey, 0, 1) === '_') {
					continue;
				}
				if (strpos($nodeKey, '.')) {
					$nodeKeyParts = explode('.', $nodeKey);
					$currentNode = &$return;
					$total = (count($nodeKeyParts) - 1);
					for ($i = 0; $i < $total; $i++) {
						$currentNode = &$currentNode[$nodeKeyParts[$i]];
					}
					$newNode = array(next($nodeKeyParts) => $nodeValue);
					$currentNode = $this->walkFlexFormNode($newNode, $valuePointer);
				} else if (is_array($nodeValue)) {
					if (array_key_exists($valuePointer, $nodeValue)) {
						$return[$nodeKey] = $nodeValue[$valuePointer];
					} else {
						$return[$nodeKey] = $this->walkFlexFormNode($nodeValue, $valuePointer);
					}
				} else {
					$return[$nodeKey] = $nodeValue;
				}
			}
			return $return;
		}
		return $nodeArray;
	}

	/**
	 * Transforms members on $values recursively according to the provided
	 * Flux configuration extracted from a Flux template. Uses "transform"
	 * attributes on fields to determine how to transform values.
	 *
	 * @param array $values
	 * @param array $fluxConfiguration
	 * @param string $prefix
	 * @return array
	 */
	public function transformAccordingToConfiguration($values, $fluxConfiguration = NULL, $prefix = '') {
		if (FALSE === is_array($values) || NULL === $fluxConfiguration) {
			return $values;
		}
		foreach ($values as $index => $value) {
			if (TRUE === is_array($value)) {
				$value = $this->transformAccordingToConfiguration($value, $fluxConfiguration, $prefix . (FALSE === empty($prefix) ? '.' : '') . $index);
			} elseif (TRUE === isset($fluxConfiguration['fields'])) {
				foreach ($fluxConfiguration['fields'] as $field) {
					$fieldConfiguration = $field->renderConfiguration();
					$fieldName = $fieldConfiguration['name'];
					$transformType = $fieldConfiguration['transform'];
					if ($fieldName === $prefix . (FALSE === empty($prefix) ? '.' : '') . $index && FALSE === empty($transformType)) {
						$value = $this->transformValueToType($value, $transformType);
					}
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
	private function transformValueToType($value, $dataType) {
		if ($dataType == 'int' || $dataType == 'integer') {
			return intval($value);
		} else if ($dataType == 'float') {
			return floatval($value);
		} else if ($dataType == 'array') {
			return explode(',', $value);
		} else if (strpos($dataType, 'Tx_') === 0) {
			return $this->getObjectOfType($dataType, $value);
		}
		return $value;
	}

	/**
	 * Gets a DomainObject or QueryResult of $dataType
	 *
	 * @param string $dataType
	 * @param string $uids
	 * @return mixed
	 */
	private function getObjectOfType($dataType, $uids) {
		$uids = trim($uids, ',');
		$identifiers = explode(',', $uids);
		// Fast decisions
		if (FALSE !== strpos($dataType, '_Domain_Model_') && FALSE === strpos($dataType, '<')) {
			$repositoryClassName = str_replace('_Model_', '_Repository_', $dataType) . 'Repository';
			if (class_exists($repositoryClassName)) {
				$repository = $this->objectManager->get($repositoryClassName);
				$uid = array_pop($identifiers);
				return $repository->findOneByUid($uid);
			}
		} else if (class_exists($dataType)) {
			// using constructor value to support objects like DateTime
			return $this->objectManager->get($dataType, $uids);
		}
		// slower decisions with support for type-hinted collection objects
		list ($container, $object) = explode('<', trim($dataType, '>'));
		if ($container && $object) {
			if (FALSE !== strpos($object, '_Domain_Model_') && $uids) {
				$repositoryClassName = str_replace('_Model_', '_Repository_', $object) . 'Repository';
				/** @var $repository Tx_Extbase_Persistence_Repository */
				$repository = $this->objectManager->get($repositoryClassName);
				$query = $repository->createQuery();
				$query->matching($query->in('uid', $uids));
				return $query->execute();
			} else {
				$container = $this->objectManager->get($container);
				return $container;
			}
		} else {
			// passthrough; neither object nor type hinted collection object
			return $uids;
		}
	}

	/**
	 * @param mixed $instance
	 * @return void
	 */
	public function debug($instance) {
		if (1 > $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']) {
			if (TRUE === $instance instanceof Exception) {
				t3lib_div::sysLog('Flux Debug: Suppressed Exception - "' . $instance->getMessage() . '" (' . $instance->getCode() . ')', 'flux');
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
		if (TRUE === $instance instanceof Tx_Flux_MVC_View_ExposedTemplateView) {
			$this->debugView($instance);
		} elseif (TRUE === $instance instanceof Tx_Flux_Provider_ConfigurationProviderInterface) {
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
		Tx_Extbase_Utility_Debugger::var_dump($variable);
	}

	/**
	 * @param Exception $error
	 * @return void
	 */
	public function debugException(Exception $error) {
		$this->message($error->getMessage() . ' (' . $error->getCode() . ')', t3lib_div::SYSLOG_SEVERITY_FATAL);
	}

	/**
	 * @param Tx_Flux_MVC_View_ExposedTemplateView $view
	 * @return void
	 */
	public function debugView(Tx_Flux_MVC_View_ExposedTemplateView $view) {
		Tx_Extbase_Utility_Debugger::var_dump($view);
	}

	/**
	 * @param Tx_Flux_Provider_ConfigurationProviderInterface $provider
	 * @return void
	 */
	public function debugProvider(Tx_Flux_Provider_ConfigurationProviderInterface $provider) {
		Tx_Extbase_Utility_Debugger::var_dump($provider);
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @param string $title
	 * @return NULL
	 */
	public function message($message, $severity = t3lib_div::SYSLOG_SEVERITY_INFO, $title = 'Flux Debug') {
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
		$isAjaxCall = (boolean) 0 < t3lib_div::_GET('ajaxCall');
		$flashMessage = new t3lib_FlashMessage($message, $title, $severity);
		$flashMessage->setStoreInSession($isAjaxCall);
		t3lib_FlashMessageQueue::addMessage($flashMessage);
		self::$sentDebugMessages[$hash] = TRUE;
		return NULL;
	}

}
