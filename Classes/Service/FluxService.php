<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\DropInContentTypeDefinition;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Integration\Resolver;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\View\TemplatePaths;

/**
 * Flux FlexForm integration Service
 *
 * Main API Service for interacting with Flux-based FlexForms
 */
class FluxService implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ServerRequest $serverRequest;
    protected WorkspacesAwareRecordService $recordService;
    protected ResourceFactory $resourceFactory;
    protected ProviderResolver $providerResolver;
    protected CacheService $cacheService;
    protected FormDataTransformer $transformer;
    protected FlexFormService $flexFormService;
    protected ConfigurationManagerInterface $configurationManager;

    public function __construct(
        ServerRequest $serverRequest,
        WorkspacesAwareRecordService $recordService,
        ResourceFactory $resourceFactory,
        ProviderResolver $providerResolver,
        CacheService $cacheService,
        FormDataTransformer $transformer,
        FlexFormService $flexFormService,
        ConfigurationManagerInterface $configurationManager
    ) {
        $this->serverRequest = $serverRequest;
        $this->recordService = $recordService;
        $this->resourceFactory = $resourceFactory;
        $this->providerResolver = $providerResolver;
        $this->cacheService = $cacheService;
        $this->transformer = $transformer;
        $this->flexFormService = $flexFormService;
        $this->configurationManager = $configurationManager;
    }

    public function sortObjectsByProperty(array $objects, string $sortBy, string $sortDirection = 'ASC'): array
    {
        $ascending = 'ASC' === strtoupper($sortDirection);
        uasort($objects, function ($a, $b) use ($sortBy, $ascending) {
            $a = ObjectAccess::getPropertyPath($a, $sortBy);
            $b = ObjectAccess::getPropertyPath($b, $sortBy);
            return $ascending ? $a <=> $b : $b <=> $a;
        });
        return $objects;
    }

    /**
     * Returns the plugin.tx_extsignature.settings array.
     * Accepts any input extension name type.
     */
    public function getSettingsForExtensionName(string $extensionName): array
    {
        $signature = ExtensionNamingUtility::getExtensionSignature($extensionName);
        return (array) $this->getTypoScriptByPath('plugin.tx_' . $signature . '.settings');
    }

    /**
     * Gets the value/array from global TypoScript by
     * dotted path expression.
     *
     * @return array|mixed
     */
    public function getTypoScriptByPath(string $path)
    {
        $cacheId = md5('ts_' . $path);
        $fromCache = $this->cacheService->getFromCaches($cacheId);
        if ($fromCache) {
            return $fromCache;
        }

        $all = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        $value = &$all;
        foreach (explode('.', $path) as $segment) {
            $value = ($value[$segment . '.'] ?? $value[$segment] ?? null);
            if ($value === null) {
                break;
            }
        }
        if (is_array($value)) {
            $value = GeneralUtility::removeDotsFromTS($value);
        }
        $this->cacheService->setInCaches($value, true, $cacheId);
        return $value;
    }

    /**
     * ResolveUtility the top-priority ConfigurationPrivider which can provide
     * a working FlexForm configuration baed on the given parameters.
     *
     * @template T
     * @param class-string<T>[] $interfaces
     * @return T|null
     */
    public function resolvePrimaryConfigurationProvider(
        ?string $table,
        ?string $fieldName,
        array $row = null,
        ?string $extensionKey = null,
        array $interfaces = [ProviderInterface::class]
    ) {
        return $this->providerResolver->resolvePrimaryConfigurationProvider(
            $table,
            $fieldName,
            $row,
            $extensionKey,
            $interfaces
        );
    }

    /**
     * Resolves a ConfigurationProvider which can provide a working FlexForm
     * configuration based on the given parameters.
     *
     * @template T
     * @param class-string<T>[] $interfaces
     * @return T[]
     */
    public function resolveConfigurationProviders(
        ?string $table,
        ?string $fieldName,
        array $row = null,
        ?string $extensionKey = null,
        array $interfaces = [ProviderInterface::class]
    ): array {
        return $this->providerResolver->resolveConfigurationProviders(
            $table,
            $fieldName,
            $row,
            $extensionKey,
            $interfaces
        );
    }

    public function getResolver(): Resolver
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
     * @param string|null $languagePointer language pointer used in the flexForm
     * @param string|null $valuePointer value pointer used in the flexForm
     */
    public function convertFlexFormContentToArray(
        string $flexFormContent,
        Form $form = null,
        ?string $languagePointer = 'lDEF',
        ?string $valuePointer = 'vDEF'
    ): array {
        if (true === empty($flexFormContent)) {
            return [];
        }
        if (true === empty($languagePointer)) {
            $languagePointer = 'lDEF';
        }
        if (true === empty($valuePointer)) {
            $valuePointer = 'vDEF';
        }
        $settings = $this->flexFormService->convertFlexFormContentToArray(
            $flexFormContent,
            $languagePointer,
            $valuePointer
        );
        if (null !== $form && $form->getOption(Form::OPTION_TRANSFORM)) {
            $settings = $this->transformer->transformAccordingToConfiguration($settings, $form);
        }
        return $settings;
    }

    public function convertFileReferenceToTemplatePathAndFilename(string $reference): string
    {
        $parts = explode(':', $reference);
        $filename = array_pop($parts);
        if (true === ctype_digit($filename)) {
            /** @var File $file */
            $file = $this->resourceFactory->getFileObjectFromCombinedIdentifier($reference);
            return $file->getIdentifier();
        }
        $reference = $this->resolveAbsolutePathForFilename($reference);
        return $reference;
    }

    public function getViewConfigurationByFileReference(string $reference): array
    {
        $extensionKey = 'flux';
        if (0 === strpos($reference, 'EXT:')) {
            $extensionKey = substr($reference, 4, strpos($reference, '/') - 4);
        }
        $templatePaths = $this->createTemplatePaths($extensionKey);
        return $templatePaths->toArray();
    }

    public function getPageConfiguration(?string $extensionName = null): array
    {
        if (null !== $extensionName && true === empty($extensionName)) {
            // Note: a NULL extensionName means "fetch ALL defined collections" whereas
            // an empty value that is not null indicates an incorrect caller. Instead
            // of returning ALL paths here, an empty array is the proper return value.
            // However, dispatch a debug message to inform integrators of the problem.
            if ($this->logger instanceof LoggerInterface) {
                $this->logger->log(
                    'notice',
                    'Template paths have been attempted fetched using an empty value that is NOT NULL in ' .
                    get_class($this) . '. This indicates a potential problem with your TypoScript configuration - a ' .
                    'value which is expected to be an array may be defined as a string. This error is not fatal but ' .
                    'may prevent the affected collection (which cannot be identified here) from showing up'
                );
            }
            return [];
        }

        $plugAndPlayEnabled = ExtensionConfigurationUtility::getOption(
            ExtensionConfigurationUtility::OPTION_PLUG_AND_PLAY
        );
        $plugAndPlayDirectory = ExtensionConfigurationUtility::getOption(
            ExtensionConfigurationUtility::OPTION_PLUG_AND_PLAY_DIRECTORY
        );
        if (!is_scalar($plugAndPlayDirectory)) {
            return [];
        }
        $plugAndPlayTemplatesDirectory = trim((string) $plugAndPlayDirectory, '/.') . '/';
        if ($plugAndPlayEnabled && $extensionName === 'Flux') {
            return [
                TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [
                    $plugAndPlayTemplatesDirectory
                    . DropInContentTypeDefinition::TEMPLATES_DIRECTORY
                    . DropInContentTypeDefinition::PAGE_DIRECTORY
                ],
                TemplatePaths::CONFIG_PARTIALROOTPATHS => [
                    $plugAndPlayTemplatesDirectory . DropInContentTypeDefinition::PARTIALS_DIRECTORY
                ],
                TemplatePaths::CONFIG_LAYOUTROOTPATHS => [
                    $plugAndPlayTemplatesDirectory . DropInContentTypeDefinition::LAYOUTS_DIRECTORY
                ],
            ];
        }
        if (null !== $extensionName) {
            $templatePaths = $this->createTemplatePaths($extensionName);
            return $templatePaths->toArray();
        }
        $configurations = [];
        $registeredExtensionKeys = Core::getRegisteredProviderExtensionKeys('Page');
        foreach ($registeredExtensionKeys as $registeredExtensionKey) {
            $templatePaths = $this->createTemplatePaths($registeredExtensionKey);
            $configurations[$registeredExtensionKey] = $templatePaths->toArray();
        }
        if ($plugAndPlayEnabled) {
            $configurations['FluidTYPO3.Flux'] = array_replace(
                $configurations['FluidTYPO3.Flux'] ?? [],
                [
                    TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [
                        $plugAndPlayTemplatesDirectory
                        . DropInContentTypeDefinition::TEMPLATES_DIRECTORY
                        . DropInContentTypeDefinition::PAGE_DIRECTORY
                    ],
                    TemplatePaths::CONFIG_PARTIALROOTPATHS => [
                        $plugAndPlayTemplatesDirectory . DropInContentTypeDefinition::PARTIALS_DIRECTORY
                    ],
                    TemplatePaths::CONFIG_LAYOUTROOTPATHS => [
                        $plugAndPlayTemplatesDirectory . DropInContentTypeDefinition::LAYOUTS_DIRECTORY
                    ],
                ]
            );
        }
        return $configurations;
    }

    /**
     * Resolve fluidpages specific configuration provider. Always
     * returns the main PageProvider type which needs to be used
     * as primary PageProvider when processing a complete page
     * rather than just the "sub configuration" field value.
     */
    public function resolvePageProvider(array $row): ?ProviderInterface
    {
        $provider = $this->resolvePrimaryConfigurationProvider('pages', PageProvider::FIELD_NAME_MAIN, $row);
        return $provider;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createTemplatePaths(string $registeredExtensionKey): TemplatePaths
    {
        /** @var TemplatePaths $templatePaths */
        $templatePaths = GeneralUtility::makeInstance(
            TemplatePaths::class,
            ExtensionNamingUtility::getExtensionKey($registeredExtensionKey)
        );
        return $templatePaths;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function resolveAbsolutePathForFilename(string $filename): string
    {
        return GeneralUtility::getFileAbsFileName($filename);
    }

    protected function getRequest(): ServerRequest
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? $this->serverRequest;
    }
}
