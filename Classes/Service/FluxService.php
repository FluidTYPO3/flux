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
	 * @var array
	 */
	protected $storage;

	/**
	 *
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Property_Mapper Tx_Extbase_Property_Mapper
	 */
	protected $propertyMapper;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * @var Tx_Flux_Service_DebugService
	 */
	protected $debugService;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_Extbase_Property_Mapper $propertyMapper
	 * @return void
	 */
	public function injectPropertyMapper(Tx_Extbase_Property_Mapper $propertyMapper) {
		$this->propertyMapper = $propertyMapper;
	}

	/**
	 * @param Tx_Extbase_Reflection_Service $reflectionService
	 * @return void
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @param Tx_Flux_Service_DebugService $debugService
	 * @return void
	 */
	public function injectDebugService(Tx_Flux_Service_DebugService $debugService) {
		$this->debugService = $debugService;
	}

	/**
	 * @param string $templatePathAndFilename
	 * @param string $variableName
	 * @param string $section
	 * @param array $paths
	 * @oaram string $extensionName
	 * @return mixed
	 */
	public function getStoredVariable($templatePathAndFilename, $variableName, $section = 'Configuration', $paths = array(), $extensionName = NULL) {
		$cacheKey = $templatePathAndFilename . $variableName . json_encode($paths) . $section;
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		/** @var $exposedView Tx_Flux_MVC_View_ExposedStandaloneView */
		$exposedView = $this->objectManager->get('Tx_Flux_MVC_View_ExposedStandaloneView');
		$exposedView->setTemplatePathAndFilename($templatePathAndFilename);
		if (TRUE === isset($paths['layoutRootPath'])) {
			$exposedView->setLayoutRootPath($paths['layoutRootPath']);
			$exposedView->setPartialRootPath($paths['partialRootPath']);
		}
		$value = $exposedView->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', $variableName, $section, $paths, $extensionName);
		self::$cache[$cacheKey] = $value;
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
	 * @param array $variables
	 * @param string|NULL $configurationSection
	 * @param array $paths
	 * @param string $extensionName
	 * @return array
	 * @throws Exception
	 */
	public function getGridFromTemplateFile($templatePathAndFilename, array $variables = array(), $configurationSection = NULL, array $paths = array(), $extensionName = NULL) {
		try {
			if (file_exists($templatePathAndFilename) === FALSE) {
				$templatePathAndFilename = t3lib_div::getFileAbsFileName($templatePathAndFilename);
			}
			if (file_exists($templatePathAndFilename) === FALSE) {
				t3lib_div::sysLog('Attempted to fetch a Grid from a template file which does not exist (' . $templatePathAndFilename . ')', 'flux', t3lib_div::SYSLOG_SEVERITY_WARNING);
				return array();
			}
			$paths = Tx_Flux_Utility_Path::translatePath($paths);
			$context = $this->objectManager->create('Tx_Extbase_MVC_Controller_ControllerContext');
			$request = $this->objectManager->create('Tx_Extbase_MVC_Request');
			$response = $this->objectManager->create('Tx_Extbase_MVC_Response');
			$request->setControllerExtensionName('Flux');
			$request->setControllerName('Flux');
			$request->setDispatched(TRUE);
			$context->setRequest($request);
			$context->setResponse($response);
			$view = $this->objectManager->get('Tx_Flux_MVC_View_ExposedTemplateView');
			$view->setControllerContext($context);
			$view->setTemplatePathAndFilename($templatePathAndFilename);
			if ($paths['partialRootPath']) {
				$view->setPartialRootPath($paths['partialRootPath']);
			}
			if ($paths['layoutRootPath']) {
				$view->setLayoutRootPath($paths['layoutRootPath']);
			}
			$view->assignMultiple($variables);
			$stored = $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', $configurationSection, $extensionName);
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
		if (file_exists($templateFile) === FALSE) {
			if (NULL === $extensionName && 0 === strpos($templateFile, 'EXT:')) {
				$extensionKey = substr($templateFile, 4, strpos($templateFile, '/') - 4);
				$extensionName = t3lib_div::underscoredToUpperCamelCase($extensionKey);
			}
			$templateFile = t3lib_div::getFileAbsFileName($templateFile);
		}
		$config = NULL;
		try {
			if (TRUE === isset($paths['extensionKey'])) {
				$extensionName = t3lib_div::underscoredToUpperCamelCase($paths['extensionKey']);
			}
			if (file_exists($templateFile) === FALSE) {
				// Only process this $dataStructArray if the specified template file exists.
				throw new Exception('Tried to get a FlexForm configuration from a file which does not exist (' . $templateFile . ')', 1343264270);
			}
			/**	@var $view Tx_Flux_MVC_View_ExposedStandaloneView */
			$view = $this->objectManager->create('Tx_Flux_MVC_View_ExposedStandaloneView');
			$view->setTemplatePathAndFilename($templateFile);
			$view->assignMultiple($values);
			$config = $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', $section, $paths, $extensionName);
		} catch (Exception $error) {
			$this->debugService->message('Reading file ' . $templateFile . ' caused an error - see next message', t3lib_div::SYSLOG_SEVERITY_FATAL);
			$this->debugService->debug($error);
		}
		return $config;
	}

	/**
	 * @param string $reference
	 * @param string $controllerObjectShortName
	 * @param boolean $failHardClass
	 * @param boolean $failHardAction
	 * @return string|NULL
	 */
	public function resolveFluxControllerClassName($reference, $controllerObjectShortName, $failHardClass = FALSE, $failHardAction = FALSE) {
		list ($extensionKey, $action) = explode('->', $reference);
		$action{0} = strtolower($action{0});
		$extensionName = ucfirst(t3lib_div::underscoredToLowerCamelCase($extensionKey));
		$potentialControllerClassName = 'Tx_' . $extensionName . '_Controller_' . $controllerObjectShortName . 'Controller';
		if (FALSE === class_exists($potentialControllerClassName)) {
			if (TRUE === $failHardClass) {
				throw new Exception('Class ' . $potentialControllerClassName . ' does not exist. It was build from: ' . var_export($reference, TRUE) .
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

	/**
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getViewConfigurationForExtensionName($extensionName) {
		$configuration = $this->getTypoScriptSubConfiguration(NULL, 'view', array(), $extensionName);
		return $configuration;
	}

	/**
	 * Gets an array of TypoScript configuration from below plugin.tx_fed -
	 * if $extensionName is set in parameters it is used to indicate which sub-
	 * section of the result to return.
	 *
	 * @param string $extensionName
	 * @param string $memberName
	 * @param array $dontTranslateMembers Array of members not to be translated by path
	 * @param string $containerExtensionScope If TypoScript is not located under plugin.tx_fed, change the tx_<scope> part by specifying this argument
	 * @return array
	 */
	public function getTypoScriptSubConfiguration($extensionName, $memberName, $dontTranslateMembers = array(), $containerExtensionScope = 'fed') {
		$containerExtensionScope = str_replace('_', '', $containerExtensionScope);
		$cacheKey = $extensionName . $memberName . $containerExtensionScope;
		if (TRUE === isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey];
		}
		$config = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$config = $config['plugin.']['tx_' . $containerExtensionScope . '.'][$memberName . '.'];
		if (is_array($config) === FALSE) {
			$config = array();
		}
		$config = Tx_Flux_Utility_Array::convertTypoScriptArrayToPlainArray($config);
		if ($extensionName) {
			$config = $config[$extensionName];
		}
		if (is_array($config) === FALSE) {
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
	 * @return Tx_Flux_Provider_ConfigurationProviderInterface|NULL
	 */
	public function resolvePrimaryConfigurationProvider($table, $fieldName, array $row = NULL, $extensionKey = NULL) {
		$providers = $this->resolveConfigurationProviders($table, $fieldName, $row, $extensionKey);
		$priority = 0;
		$providerWithTopPriority = NULL;
		foreach ($providers as $provider) {
			if ($provider->getPriority($row) >= $priority) {
				$providerWithTopPriority = $provider;
			}
		}
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
		$rowChecksum = md5(json_encode($row));
		$cacheKey = $table . $fieldName . $rowChecksum . $extensionKey;
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
				$providerCacheKey = $table . $fieldName . $rowChecksum . $extensionKey . $providerClassNameOrInstance;
				if (TRUE === isset(self::$cache[$providerCacheKey])) {
					$provider = &self::$cache[$providerCacheKey];
				} else {
					$provider = $this->objectManager->create($providerClassNameOrInstance);
				}
			}
			$priority = $provider->getPriority($row);
			$providerFieldName = $provider->getFieldName($row);
			$providerExtensionKey = $provider->getExtensionKey($row);
			$providerTableName = $provider->getTableName($row);
			if (is_array($prioritizedProviders[$priority]) === FALSE) {
				$prioritizedProviders[$priority] = array();
			}
			$matchesTableName = ($providerTableName === $table);
			$matchesFieldName = ($providerFieldName === $fieldName || $bindToFieldName === FALSE || $fieldName === NULL);
			$matchesExtensionKey = ($providerExtensionKey === $extensionKey || $extensionKey === NULL);
			/** @var Tx_Flux_Provider_ConfigurationProviderInterface $provider */
			if ($matchesExtensionKey && $matchesTableName && $matchesFieldName) {
				if ($provider instanceof Tx_Flux_Provider_ContentObjectConfigurationProviderInterface) {
					/** @var Tx_Flux_Provider_ContentObjectConfigurationProviderInterface $provider */
					if (isset($row['CType']) === FALSE || $provider->getContentObjectType($row) === $row['CType']) {
						$prioritizedProviders[$priority][] = $provider;
					}
				} elseif ($provider instanceof Tx_Flux_Provider_PluginConfigurationProviderInterface) {
					/** @var Tx_Flux_Provider_PluginConfigurationProviderInterface $provider */
					if (isset($row['list_type']) === FALSE || $provider->getListType($row) === $row['list_type']) {
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
			if ($templateFile === NULL) {
				$this->debugService->message('A template file path was NULL - this might indicate an error in class ' . $className);
				$config['parameters'] = array(
					'userFunction' => 'Tx_Flux_UserFunction_NoTemplate->renderField'
				);
				$dataStructArray = $this->objectManager->create('Tx_Flux_Provider_Structure_FallbackStructureProvider')->render($config);
				return;
			}
			$config = $this->getFlexFormConfigurationFromFile($templateFile, $values, $section, $paths, $extensionName);
			/** @var $flexFormStructureProvider Tx_Flux_Provider_Structure_FlexFormStructureProvider */
			$flexFormStructureProvider = $this->objectManager->create('Tx_Flux_Provider_Structure_FlexFormStructureProvider');
			$dataStructArray = $flexFormStructureProvider->render($config);
			if ((is_array($dataStructArray['ROOT']['el']) === FALSE && is_array($dataStructArray['sheets']) === FALSE) || (count($dataStructArray['sheets']) < 1 && count($dataStructArray['ROOT']['el']) < 1 && count($dataStructArray['sheets'][key($dataStructArray['sheets'])]) === 0)) {
				$config['parameters'] = array(
					'userFunction' => 'Tx_Flux_UserFunction_NoFields->renderField'
				);
				$dataStructArray = $this->objectManager->create('Tx_Flux_Provider_Structure_FallbackStructureProvider')->render($config);
			}
		} catch (Exception $e) {
			$this->debugService->message('Attempting to convert FlexForm XML to array using file ' . $templateFile . ' failed - ' .
				'see next error message');
			$this->debugService->debug($e);
			$config['parameters'] = array(
				'exception' => $e,
				'userFunction' => 'Tx_Flux_UserFunction_ErrorReporter->renderField'
			);
			if (t3lib_extMgm::isLoaded('templavoila') === FALSE) {
				$dataStructArray = $this->objectManager->create('Tx_Flux_Provider_Structure_FallbackStructureProvider')->render($config);
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
		if (empty($languagePointer)) {
			$languagePointer = 'lDEF';
		}
		if (empty($valuePointer)) {
			$valuePointer = 'vDEF';
		}
		$flexFormArray = t3lib_div::xml2array($flexFormContent);
		$flexFormArray = (isset($flexFormArray['data']) && is_array($flexFormArray['data']) ? $flexFormArray['data'] : $flexFormArray);
		if (is_array($flexFormArray) === FALSE) {
			return $settings;
		}
		foreach (array_values($flexFormArray) as $languages) {
			if (!is_array($languages) || !isset($languages[$languagePointer])) {
				continue;
			}
			foreach ($languages[$languagePointer] as $valueKey => $valueDefinition) {
				if (strpos($valueKey, '.') === FALSE) {
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
					$currentNode =& $return;
					$total = (count($nodeKeyParts) - 1);
					for ($i = 0; $i < $total; $i++) {
						$currentNode =& $currentNode[$nodeKeyParts[$i]];
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
	 * @return array
	 */
	private function transformAccordingToConfiguration($values, $fluxConfiguration) {
		return $values;
		foreach ($fieldArrayContainingTypes as $fieldConfiguration) {
			$transformType = $fieldConfiguration['transform'];
			if ($transformType) {
				$fieldName = $fieldConfiguration['name'];
				$path = explode('.', $fieldName);
				$current =& $all;
				while ($key = array_shift($path)) {
					$current =& $current[$key];
				}
				$current = $this->digDownTransform($values, explode('.', $fieldName), $transformType);
			}
		}
		return (array) $values;
	}

	/**
	 * Digs down path to transform final member to $dataType
	 *
	 * @param mixed $all
	 * @param array $keysLeft
	 * @param string $transformType
	 * @return mixed
	 */
	private function digDownTransform($all, $keysLeft, $transformType) {
		$current =& $all;
		while ($key = array_shift($keysLeft)) {
			$current =& $current[$key];
		}
		return $this->transformValueToType($current, $transformType);
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
		} else {
			return $value;
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
		$uids = trim($uids, ',');
		$identifiers = explode(',', $uids);
		// Fast decisions
		if (strpos($dataType, '_Domain_Model_') !== FALSE && strpos($dataType, '<') === FALSE) {
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
			if (strpos($object, '_Domain_Model_') !== FALSE && $uids) {
				$repositoryClassName = str_replace('_Model_', '_Repository_', $object) . 'Repository';
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

}
