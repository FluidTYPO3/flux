<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Configuration\ConfigurationManager;
use FluidTYPO3\Flux\FluxPackage;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Helper\Resolver;
use FluidTYPO3\Flux\Package\FluxPackageFactory;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\UserFunction\ErrorReporter;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\View\ExposedTemplateView;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * Flux FlexForm integration Service
 *
 * Main API Service for interacting with Flux-based FlexForms
 */
class FluxService implements SingletonInterface
{

    /**
     * @var array
     * @deprecated To be removed in next major release
     */
    protected static $cache = [];

    /**
     * @var array
     */
    protected static $typoScript = [];

    /**
     * @var array
     */
    protected static $friendlySeverities = [
        GeneralUtility::SYSLOG_SEVERITY_INFO,
        GeneralUtility::SYSLOG_SEVERITY_NOTICE,
    ];

    /**
     * @var array
     */
    protected $sentDebugMessages = [];

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
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param ReflectionService $reflectionService
     * @return void
     */
    public function injectReflectionService(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * @param ProviderResolver $providerResolver
     * @return void
     */
    public function injectProviderResolver(ProviderResolver $providerResolver)
    {
        $this->providerResolver = $providerResolver;
    }

    /**
     * @param array $objects
     * @param string $sortBy
     * @param string $sortDirection
     * @return array
     */
    public function sortObjectsByProperty(array $objects, $sortBy, $sortDirection = 'ASC')
    {
        $ascending = 'ASC' === strtoupper($sortDirection);
        uasort($objects, function ($a, $b) use ($sortBy, $ascending) {
            $a = ObjectAccess::getPropertyPath($a, $sortBy);
            $b = ObjectAccess::getPropertyPath($b, $sortBy);
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? ($ascending ? -1 : 1) : ($ascending ? 1 : -1);
        });
        return $objects;
    }

    /**
     * @param ViewContext $viewContext
     * @return ExposedTemplateView
     */
    public function getPreparedExposedTemplateView(ViewContext $viewContext)
    {
        GeneralUtility::logDeprecatedFunction();
        $viewContextHash = $viewContext->getHash();
        static $cache = [];
        if (isset($cache[$viewContextHash])) {
            return $cache[$viewContextHash];
        }
        $extensionKey = $viewContext->getExtensionKey();
        $qualifiedExtensionName = $viewContext->getExtensionName();
        $variables = $viewContext->getVariables();
        if (null === $qualifiedExtensionName || false === ExtensionManagementUtility::isLoaded($extensionKey)) {
            // Note here: a default value of the argument would not be adequate; outside callers could still pass NULL.
            $qualifiedExtensionName = 'Flux';
        }
        /** @var $context ControllerContext */
        $context = $this->objectManager->get(ControllerContext::class);
        $request = $viewContext->getRequest();
        /** @var $response Response */
        $response = $this->objectManager->get(Response::class);
        /** @var $uriBuilder UriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
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
        return $cache[$viewContextHash] = $exposedView;
    }

    /**
     * @param ViewContext $viewContext
     * @param string $formName
     * @return Form|NULL
     */
    public function getFormFromTemplateFile(ViewContext $viewContext, $formName = 'form')
    {
        GeneralUtility::logDeprecatedFunction();
        static $cache = [];
        $templatePathAndFilename = $viewContext->getTemplatePathAndFilename();
        if (false === file_exists($templatePathAndFilename)) {
            return null;
        }
        $section = $viewContext->getSectionName();
        $extensionName = $viewContext->getExtensionName();
        $cacheKey = $viewContext->getHash();
        if (false === isset($cache[$cacheKey])) {
            try {
                $exposedView = $this->getPreparedExposedTemplateView($viewContext);
                $cache[$cacheKey] = $exposedView->getForm($section, $formName);
            } catch (\RuntimeException $error) {
                $this->debug($error);
                /** @var Form $form */
                $cache[$cacheKey] = $this->objectManager->get(
                    FluxPackageFactory::getPackageWithFallback($extensionName)
                        ->getImplementation(FluxPackage::IMPLEMENTATION_FORM)
                );
                $cache[$cacheKey]->createField('UserFunction', 'error')
                    ->setFunction(ErrorReporter::class . '->renderField')
                    ->setArguments([$error]);
            }
        }
        return $cache[$cacheKey];
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
    public function getGridFromTemplateFile(ViewContext $viewContext, $gridName = 'grid')
    {
        GeneralUtility::logDeprecatedFunction();
        $hash = $viewContext->getHash() . $gridName;
        static $cache = [];
        if (isset($cache[$hash])) {
            return $cache[$hash];
        }
        $templatePathAndFilename = $viewContext->getTemplatePathAndFilename();
        $section = $viewContext->getSectionName();
        $grid = null;
        if (true === file_exists($templatePathAndFilename)) {
            $exposedView = $this->getPreparedExposedTemplateView($viewContext);
            $exposedView->setTemplatePathAndFilename($templatePathAndFilename);
            $grid = $exposedView->getGrid($section, $gridName);
        }
        if (null === $grid) {
            $grid = Grid::create(['name' => $gridName]);
        }
        return $cache[$hash] = $grid;
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
    protected function getDefaultViewConfigurationForExtensionKey($extensionKey)
    {
        GeneralUtility::logDeprecatedFunction();
        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKey);
        return [
            TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [0 => 'EXT:' . $extensionKey . '/Resources/Private/Templates/'],
            TemplatePaths::CONFIG_PARTIALROOTPATHS => [0 => 'EXT:' . $extensionKey . '/Resources/Private/Partials/'],
            TemplatePaths::CONFIG_LAYOUTROOTPATHS => [0 => 'EXT:' . $extensionKey . '/Resources/Private/Layouts/'],
        ];
    }

    /**
     * Returns the plugin.tx_extsignature.view array,
     * or a default set of paths if that array is not
     * defined in TypoScript.
     *
     * @param string $extensionName
     * @return array|NULL
     */
    public function getViewConfigurationForExtensionName($extensionName)
    {
        GeneralUtility::logDeprecatedFunction();
        static $cache = [];
        if (isset($cache[$extensionName])) {
            return $cache[$extensionName];
        }
        $signature = ExtensionNamingUtility::getExtensionSignature($extensionName);
        $defaults = (array) $this->getDefaultViewConfigurationForExtensionKey($extensionName);
        $configuration = (array) $this->getTypoScriptByPath('plugin.tx_' . $signature . '.view');
        $typoScript = $this->getAllTypoScript();
        // We probe $typoScript here, determining if any TS was loaded. If none was loaded we
        // avoid caching the result so that future calls (after TS gets loaded) will receive the
        // TS-configured paths. The result will then get cached as soon as TS becomes available.
        if (empty($typoScript)) {
            // Special case: when $configuration is empty this means
            return $defaults;
        }
        return $cache[$extensionName] = RecursiveArrayUtility::mergeRecursiveOverrule($defaults, $configuration);
    }

    /**
     * Returns the module.tx_extsignature.view array.
     * Accepts any input extension name type.
     *
     * @param string $extensionName
     * @return array|NULL
     */
    public function getBackendViewConfigurationForExtensionName($extensionName)
    {
        GeneralUtility::logDeprecatedFunction();
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
    public function getSettingsForExtensionName($extensionName)
    {
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
    public function getTypoScriptByPath($path)
    {
        $typoScript = $this->getAllTypoScript();
        return (array) ObjectAccess::getPropertyPath($typoScript, $path);
    }

    /**
     * Returns the complete, global TypoScript array
     * defined in TYPO3.
     *
     * @return array
     */
    public function getAllTypoScript()
    {
        static $cache = [];
        $pageId = $this->getCurrentPageId();
        if (isset($cache[$pageId])) {
            return $cache[$pageId];
        }
        if (false === isset($cache[$pageId])) {
            $typoScript = (array) $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
            if (!empty($typoScript)) {
                $cache[$pageId] = GeneralUtility::removeDotsFromTS($typoScript);
            } else {
                // Special case: the TS is empty, meaning the template is not yet initialized.
                // We avoid caching this result so future calls won't read an empty array.
                return [];
            }
        }
        return (array) $cache[$pageId];
    }

    /**
     * @return integer
     */
    protected function getCurrentPageId()
    {
        if ($this->configurationManager instanceof ConfigurationManager) {
            return (integer) $this->configurationManager->getCurrentPageId();
        } else {
            return (integer) $GLOBALS['TSFE']->id;
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
    public function resolvePrimaryConfigurationProvider($table, $fieldName, array $row = null, $extensionKey = null)
    {
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
    public function resolveConfigurationProviders($table, $fieldName, array $row = null, $extensionKey = null)
    {
        return $this->providerResolver->resolveConfigurationProviders($table, $fieldName, $row, $extensionKey);
    }

    /**
     * @return Resolver
     */
    public function getResolver()
    {
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
     * @param Form $form An instance of \FluidTYPO3\Flux\Form. If transformation instructions are contained in this
     *                   configuration they are applied after conversion to array
     * @param string $languagePointer language pointer used in the flexForm
     * @param string $valuePointer value pointer used in the flexForm
     * @return array the processed array
     */
    public function convertFlexFormContentToArray(
        $flexFormContent,
        Form $form = null,
        $languagePointer = 'lDEF',
        $valuePointer = 'vDEF'
    ) {
        if (true === empty($flexFormContent)) {
            return [];
        }
        if (true === empty($languagePointer)) {
            $languagePointer = 'lDEF';
        }
        if (true === empty($valuePointer)) {
            $valuePointer = 'vDEF';
        }
        $settings = $this->objectManager->get(FlexFormService::class)
            ->convertFlexFormContentToArray($flexFormContent, $languagePointer, $valuePointer);
        if (null !== $form && $form->getOption(Form::OPTION_TRANSFORM)) {
            /** @var FormDataTransformer $transformer */
            $transformer = $this->objectManager->get(FormDataTransformer::class);
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
    public function debug($instance, $plainText = true, $depth = 2)
    {
        GeneralUtility::logDeprecatedFunction();
        $text = DebuggerUtility::var_dump($instance, null, $depth, $plainText, false, true);
        GeneralUtility::devLog(
            'Flux variable dump: ' . gettype($instance),
            'flux',
            GeneralUtility::SYSLOG_SEVERITY_INFO,
            $text
        );
    }

    /**
     * @param string $message
     * @param integer $severity
     * @param string $title
     * @return void
     */
    public function message($message, $severity = GeneralUtility::SYSLOG_SEVERITY_INFO, $title = 'Flux Debug')
    {
        $hash = $message . $severity;
        $disabledDebugMode = (boolean) (1 < $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']);
        $alreadySent = isset($this->sentDebugMessages[$hash]);
        $shouldExcludedFriendlySeverities = 2 == $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
        $isExcludedSeverity = ($shouldExcludedFriendlySeverities && in_array($severity, self::$friendlySeverities));
        if (!$disabledDebugMode && !$alreadySent && !$isExcludedSeverity) {
            $this->logMessage($message, $severity);
            $this->sentDebugMessages[$hash] = true;
        }
    }

    /**
     * @return void
     */
    public function flushCache()
    {
        GeneralUtility::logDeprecatedFunction();
        self::$cache = [];
    }

    /**
     * @param mixed $value
     * @param boolean $persistent
     * @param array ...$identifyingValues
     * @return void
     */
    public function setInCaches($value, $persistent, ...$identifyingValues)
    {
        $cacheKey = $this->createCacheIdFromValues($identifyingValues);
        $this->getRuntimeCache()->set($cacheKey, $value);
        if ($persistent) {
            $this->getPersistentCache()->set($cacheKey, $value);
        }
    }

    /**
     * @param array ...$identifyingValues
     * @return mixed|false
     */
    public function getFromCaches(...$identifyingValues)
    {
        $cacheKey = $this->createCacheIdFromValues($identifyingValues);
        return $this->getRuntimeCache()->get($cacheKey) ?: $this->getPersistentCache()->get($cacheKey);
    }

    /**
     * @param array $identifyingValues
     * @return string
     */
    protected function createCacheIdFromValues(array $identifyingValues)
    {
        return 'flux-' . md5(serialize($identifyingValues));
    }

    /**
     * @return VariableFrontend
     */
    protected function getRuntimeCache()
    {
        static $cache;
        return $cache ?? ($cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_runtime'));
    }

    /**
     * @return VariableFrontend
     */
    protected function getPersistentCache()
    {
        static $cache;
        return $cache ?? ($cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('flux'));
    }

    /**
     * @param string $message
     * @param integer $severity
     * @return void
     * @codeCoverageIgnore
     */
    protected function logMessage($message, $severity)
    {
        GeneralUtility::logDeprecatedFunction();
        GeneralUtility::sysLog($message, 'flux', $severity);
    }
}
