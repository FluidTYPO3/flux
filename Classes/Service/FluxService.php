<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Configuration\ConfigurationManager;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Helper\Resolver;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3\CMS\Fluid\View\TemplatePaths;

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
     * @param string $message
     * @param integer $severity
     * @return void
     */
    public function message($message, $severity = GeneralUtility::SYSLOG_SEVERITY_INFO)
    {
        $hash = $message . $severity;
        $disabledDebugMode = (boolean) (1 < $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']);
        $alreadySent = isset($this->sentDebugMessages[$hash]);
        $shouldExcludedFriendlySeverities = 2 == $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'];
        $isExcludedSeverity = ($shouldExcludedFriendlySeverities && in_array($severity, self::$friendlySeverities));
        if (!$disabledDebugMode && !$alreadySent && !$isExcludedSeverity) {
            GeneralUtility::sysLog($message, 'flux', $severity);
            $this->sentDebugMessages[$hash] = true;
        }
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
        return (new TemplatePaths(ExtensionNamingUtility::getExtensionKey($extensionName)))->toArray();
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
}
