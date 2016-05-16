<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Configuration\BackendConfigurationManager;
use FluidTYPO3\Flux\FluxPackage;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Helper\Resolver;
use FluidTYPO3\Flux\Package\FluxPackageFactory;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\View\ExposedTemplateView;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use FluidTYPO3\Flux\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
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
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * Flux FlexForm integration Service
 *
 * Main API Service for interacting with Flux-based FlexForms
 */
class FluxService implements SingletonInterface {

	/**
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * @var array
	 */
	protected static $typoScript = array();

	/**
	 * @var array
	 */
	protected static $friendlySeverities = array(
		GeneralUtility::SYSLOG_SEVERITY_INFO,
		GeneralUtility::SYSLOG_SEVERITY_NOTICE,
	);

	/**
	 * @var array
	 */
	protected $sentDebugMessages = array();

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
	 * @var ProviderResolver
	 */
	protected $providerResolver;

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
	 * @param ProviderResolver $providerResolver
	 * @return void
	 */
	public function injectProviderResolver(ProviderResolver $providerResolver) {
		$this->providerResolver = $providerResolver;
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
	 * @param ViewContext $viewContext
	 * @return ExposedTemplateView
	 */
	public function getPreparedExposedTemplateView(ViewContext $viewContext) {
		$vendorName = $viewContext->getVendorName();
		$extensionKey = $viewContext->getExtensionKey();
		$qualifiedExtensionName = $viewContext->getExtensionName();
		$controllerName = $viewContext->getControllerName();
		$variables = $viewContext->getVariables();
		if (NULL === $qualifiedExtensionName || FALSE === ExtensionManagementUtility::isLoaded($extensionKey)) {
			// Note here: a default value of the argument would not be adequate; outside callers could still pass NULL.
			$qualifiedExtensionName = 'Flux';
		}
		$extensionName = ExtensionNamingUtility::getExtensionName($qualifiedExtensionName);
		/** @var $context ControllerContext */
		$context = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext');
		$request = $viewContext->getRequest();
		/** @var $response Response */
		$response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
		/** @var $uriBuilder UriBuilder */
		$uriBuilder = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder');
		$uriBuilder->setRequest($request);
		$context->setUriBuilder($uriBuilder);
		$context->setRequest($request);
		$context->setResponse($response);
		/** @var $renderingContext RenderingContext */
		$renderingContext = $this->objectManager->get(
			FluxPackageFactory::getPackageWithFallback($qualifiedExtensionName)
				->getImplementation(FluxPackage::IMPLEMENTATION_RENDERINGCONTEXT)
		);
		$renderingContext->setControllerContext($context);
		/** @var $exposedView ExposedTemplateView */
		$exposedView = $this->objectManager->get(
			FluxPackageFactory::getPackageWithFallback($qualifiedExtensionName)
				->getImplementation(FluxPackage::IMPLEMENTATION_VIEW)
		);
		$exposedView->setRenderingContext($renderingContext);
		$exposedView->setControllerContext($context);
		$exposedView->assignMultiple($variables);
		$exposedView->setTemplatePaths($viewContext->getTemplatePaths());
		$exposedView->setTemplatePathAndFilename($viewContext->getTemplatePathAndFilename());
		return $exposedView;
	}

	/**
	 * @param ViewContext $viewContext
	 * @param string $formName
	 * @return Form|NULL
	 */
	public function getFormFromTemplateFile(ViewContext $viewContext, $formName = 'form') {
		$templatePathAndFilename = $viewContext->getTemplatePathAndFilename();
		if (FALSE === file_exists($templatePathAndFilename)) {
			return NULL;
		}
		$section = $viewContext->getSectionName();
		$variables = $viewContext->getVariables();
		$extensionName = $viewContext->getExtensionName();
		$variableCheck = json_encode($variables);
		$cacheKey = md5($templatePathAndFilename . $formName . $extensionName . $section . $variableCheck);
		if (FALSE === isset(self::$cache[$cacheKey])) {
			try {
				$exposedView = $this->getPreparedExposedTemplateView($viewContext);
				self::$cache[$cacheKey] = $exposedView->getForm($section, $formName);
			} catch (\RuntimeException $error) {
				$this->debug($error);
				/** @var Form $form */
				self::$cache[$cacheKey] = $this->objectManager->get(
					FluxPackageFactory::getPackageWithFallback($extensionName)
						->getImplementation(FluxPackage::IMPLEMENTATION_FORM)
				);
				self::$cache[$cacheKey]->createField('UserFunction', 'error')
					->setFunction('FluidTYPO3\Flux\UserFunction\ErrorReporter->renderField')
					->setArguments(array($error)
				);
			}
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
	 * @param ViewContext $viewContext
	 * @param string $gridName
	 * @return Grid|NULL
	 */
	public function getGridFromTemplateFile(ViewContext $viewContext, $gridName = 'grid') {
		$templatePathAndFilename = $viewContext->getTemplatePathAndFilename();
		$section = $viewContext->getSectionName();
		$grid = NULL;
		if (TRUE === file_exists($templatePathAndFilename)) {
			$exposedView = $this->getPreparedExposedTemplateView($viewContext);
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
		$extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKey);
		return array(
			TemplatePaths::CONFIG_TEMPLATEROOTPATHS => array(0 => 'EXT:' . $extensionKey . '/Resources/Private/Templates/'),
			TemplatePaths::CONFIG_PARTIALROOTPATHS => array(0 => 'EXT:' . $extensionKey . '/Resources/Private/Partials/'),
			TemplatePaths::CONFIG_LAYOUTROOTPATHS => array(0 => 'EXT:' . $extensionKey . '/Resources/Private/Layouts/'),
		);
	}

	/**
	 * Returns the plugin.tx_extsignature.view array,
	 * or a default set of paths if that array is not
	 * defined in TypoScript.
	 *
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getViewConfigurationForExtensionName($extensionName) {
		$signature = ExtensionNamingUtility::getExtensionSignature($extensionName);
		$defaults = (array) $this->getDefaultViewConfigurationForExtensionKey($extensionName);
		$configuration = (array) $this->getTypoScriptByPath('plugin.tx_' . $signature . '.view');
		return RecursiveArrayUtility::mergeRecursiveOverrule($defaults, $configuration);
	}

	/**
	 * Returns the module.tx_extsignature.view array.
	 * Accepts any input extension name type.
	 *
	 * @param string $extensionName
	 * @return array|NULL
	 */
	public function getBackendViewConfigurationForExtensionName($extensionName) {
		$signature = ExtensionNamingUtility::getExtensionSignature($extensionName);
		return $this->getTypoScriptByPath('module.tx_' . $signature . '.view');
	}

	/**
	 * Returns the plugin.tx_extsignature.settings array.
	 * Accepts any input extension name type.
	 *
	 * @param string $extensionName
	 * @return array
	 */
	public function getSettingsForExtensionName($extensionName) {
		$signature = ExtensionNamingUtility::getExtensionSignature($extensionName);
		return (array) $this->getTypoScriptByPath('plugin.tx_' . $signature . '.settings');
	}

	/**
	 * Gets the value/array from global TypoScript by
	 * dotted path expression.
	 *
	 * @param string $path
	 * @return array
	 */
	public function getTypoScriptByPath($path) {
		$typoScript = $this->getAllTypoScript();
		return (array) ObjectAccess::getPropertyPath($typoScript, $path);
	}

	/**
	 * Returns the complete, global TypoScript array
	 * defined in TYPO3.
	 *
	 * @return array
	 */
	public function getAllTypoScript() {
		if (!$this->configurationManager instanceof BackendConfigurationManager) {
			$typoScript = (array) $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			$typoScript = GeneralUtility::removeDotsFromTS($typoScript);
			return $typoScript;
		} else {
			$pageId = $this->configurationManager->getCurrentPageId();
			if (FALSE === isset(self::$typoScript[$pageId])) {
				self::$typoScript[$pageId] = (array) $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
				self::$typoScript[$pageId] = GeneralUtility::removeDotsFromTS(self::$typoScript[$pageId]);
			}
			return (array) self::$typoScript[$pageId];
		}
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
		return $this->providerResolver->resolvePrimaryConfigurationProvider($table, $fieldName, $row, $extensionKey);
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
		return $this->providerResolver->resolveConfigurationProviders($table, $fieldName, $row, $extensionKey);
	}

	/**
	 * @return Resolver
	 */
	public function getResolver() {
		return new Resolver();
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
		$formTranslationDisabled = (NULL !== $form && FALSE === (boolean) $form->getOption(Form::OPTION_TRANSLATION));
		if (TRUE === empty($languagePointer) || TRUE === $formTranslationDisabled) {
			$languagePointer = 'lDEF';
		}
		if (TRUE === empty($valuePointer) || TRUE === $formTranslationDisabled) {
			$valuePointer = 'vDEF';
		}
		$settings = $this->objectManager->get('TYPO3\CMS\Extbase\Service\FlexFormService')
			->convertFlexFormContentToArray($flexFormContent, $languagePointer, $valuePointer);
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
	public function debug($instance, $plainText = TRUE, $depth = 2) {
		$text = DebuggerUtility::var_dump($instance, NULL, $depth, $plainText, FALSE, TRUE);
		GeneralUtility::devLog('Flux variable dump: ' . gettype($instance), 'flux', GeneralUtility::SYSLOG_SEVERITY_INFO, $text);
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @param string $title
	 * @return void
	 */
	public function message($message, $severity = GeneralUtility::SYSLOG_SEVERITY_INFO, $title = 'Flux Debug') {
		$hash = $message . $severity;
		$disabledDebugMode = (boolean) (1 < $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']);
		$alreadySent = TRUE === isset($this->sentDebugMessages[$hash]);
		$shouldExcludedFriendlySeverities = 2 == $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
		$isExcludedSeverity = (TRUE === $shouldExcludedFriendlySeverities && TRUE === in_array($severity, self::$friendlySeverities));
		if (FALSE === $disabledDebugMode && FALSE === $alreadySent && FALSE === $isExcludedSeverity) {
			$this->logMessage($message, $severity);
			$this->sentDebugMessages[$hash] = TRUE;
		}
	}

	/**
	 * @return void
	 */
	public function flushCache() {
		self::$cache = array();
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @return void
	 * @codeCoverageIgnore
	 */
	protected function logMessage($message, $severity) {
		GeneralUtility::sysLog($message, 'flux', $severity);
	}

}
