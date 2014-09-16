<?php
namespace FluidTYPO3\Flux\Service;
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

use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Utility\PathUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\View\ExposedTemplateView;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

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
	 * @param array $objects
	 * @param string $sortBy
	 * @param string $sortDirection
	 * @return array
	 */
	public function sortObjectsByProperty(array $objects, $sortBy, $sortDirection = 'ASC') {
		$sorted = array();
		$sort = array();
		foreach ($objects as $index => $object) {
			$sortValue = ObjectAccess::getPropertyPath($object, $sortBy);
			$sort[$index] = $sortValue;
		}
		if ('ASC' === strtoupper($sortDirection)) {
			asort($sort);
		} else {
			arsort($sort);
		}
		$hasStringIndex = FALSE;
		foreach ($sort as $index => $value) {
			$sorted[$index] = $objects[$index];
			if (TRUE === is_string($index)) {
				$hasStringIndex = TRUE;
			}
		}
		if (FALSE === $hasStringIndex) {
			// reset out-of-sequence indices if provided indices contain no strings
			$sorted = array_values($sorted);
		}
		return $sorted;
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
		/** @var $response Response */
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
			self::$cache[$cacheKey]->createField('UserFunction', 'error')
				->setFunction('FluidTYPO3\Flux\UserFunction\ErrorReporter->renderField')
				->setArguments(array($error)
			);
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
	 * Gets an array with the default view configuration for the provided
	 * extension key. Maybe overwritten by a sub-service class adding
	 * additional subfolders used by default.
	 * (e.g. EXT:fluidpages can provide "Resources/Private/Templates/Page"
	 * as default templateRootPath)
	 *
	 * @param string $extensionKey
	 * @return array
	 */
	protected function getDefaultViewConfigurationForExtensionKey($extensionKey) {
		return array(
			'templateRootPath' => 'EXT:' . $extensionKey . '/Resources/Private/Templates',
			'partialRootPath' => 'EXT:' . $extensionKey . '/Resources/Private/Partials',
			'layoutRootPath' => 'EXT:' . $extensionKey . '/Resources/Private/Layouts',
		);
	}

	/**
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getViewConfigurationForExtensionName($extensionName) {
		$extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);
		$configuration = $this->getTypoScriptSubConfiguration(NULL, 'view', $extensionKey);
		if (FALSE === is_array($configuration) || 0 === count($configuration) || TRUE === empty($configuration['templateRootPath'])) {
			$configuration = $this->getDefaultViewConfigurationForExtensionKey($extensionKey);
		}
		if (FALSE === is_array($configuration)) {
			$this->message('Template paths resolved for "' . $extensionName . '" was not an array.', GeneralUtility::SYSLOG_SEVERITY_WARNING);
			$configuration = NULL;
		}
		return $configuration;
	}

	/**
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getBackendViewConfigurationForExtensionName($extensionName) {
		$extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);
		$configuration = $this->getTypoScriptSubConfiguration(NULL, 'view', $extensionKey, 'module');
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
		return $this->objectManager->get('FluidTYPO3\Flux\Provider\ProviderResolver')->resolvePrimaryConfigurationProvider($table, $fieldName, $row, $extensionKey);
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
		return $this->objectManager->get('FluidTYPO3\Flux\Provider\ProviderResolver')->resolveConfigurationProviders($table, $fieldName, $row, $extensionKey);
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
			/** @var FormDataTransformer $transformer */
			$transformer = $this->objectManager->get('FluidTYPO3\Flux\Transformation\FormDataTransformer');
			$settings = $transformer->transformAccordingToConfiguration($settings, $form);
		}
		return $settings;
	}

	/**
	 * @param mixed $instance
	 * @param boolean $plainText
	 * @param integer $depth
	 * @return void
	 */
	public function debug($instance, $plainText = FALSE, $depth = 2) {
		\FluidTYPO3\Flux\Utility\DebuggerUtility::debug($instance, $plainText, $depth);
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @param string $title
	 * @return NULL
	 */
	public function message($message, $severity = GeneralUtility::SYSLOG_SEVERITY_INFO, $title = 'Flux Debug') {
		\FluidTYPO3\Flux\Utility\DebuggerUtility::message($message, $severity, $title);
	}

	/**
	 * @return void
	 */
	public function flushCache() {
		self::$cache = array();
	}

}
