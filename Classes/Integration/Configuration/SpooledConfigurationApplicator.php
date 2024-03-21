<?php
namespace FluidTYPO3\Flux\Integration\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\DBALException;
use FluidTYPO3\Flux\Builder\ContentTypeBuilder;
use FluidTYPO3\Flux\Builder\RequestBuilder;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3Fluid\Fluid\Exception;

class SpooledConfigurationApplicator
{
    private const CACHE_ID_SORTINGS = 'flux_contentType_sortingValues';

    private ContentTypeBuilder $contentTypeBuilder;
    private ContentTypeManager $contentTypeManager;
    private RequestBuilder $requestBuilder;
    private PackageManager $packageManager;
    private CacheService $cacheService;

    public function __construct(
        ContentTypeBuilder $contentTypeBuilder,
        ContentTypeManager $contentTypeManager,
        RequestBuilder $requestBuilder,
        PackageManager $packageManager,
        CacheService $cacheService
    ) {
        $this->contentTypeBuilder = $contentTypeBuilder;
        $this->contentTypeManager = $contentTypeManager;
        $this->requestBuilder = $requestBuilder;
        $this->packageManager = $packageManager;
        $this->cacheService = $cacheService;
    }

    public function processData(): void
    {
        // Initialize the TCA needed by "template as CType" integrations
        $this->spoolQueuedContentTypeTableConfigurations(Core::getQueuedContentTypeRegistrations());

        foreach ($this->contentTypeManager->fetchContentTypes() as $contentType) {
            if (!$contentType instanceof FluidRenderingContentTypeDefinitionInterface) {
                continue;
            }
            Core::registerTemplateAsContentType(
                $contentType->getExtensionIdentity(),
                $contentType->getTemplatePathAndFilename(),
                $contentType->getContentTypeName(),
                $contentType->getProviderClassName()
            );
        }

        $this->spoolQueuedContentTypeRegistrations(Core::getQueuedContentTypeRegistrations());
        Core::clearQueuedContentTypeRegistrations();

        $scopedRequire = static function (string $filename): void {
            require $filename;
        };

        $activePackages = $this->packageManager->getActivePackages();
        foreach ($activePackages as $package) {
            try {
                $finder = Finder::create()
                    ->files()
                    ->sortByName()
                    ->depth(0)
                    ->name('*.php')
                    ->in($package->getPackagePath() . 'Configuration/TCA/Flux');
            } catch (\InvalidArgumentException $e) {
                // No such directory in this package
                continue;
            }
            foreach ($finder as $fileInfo) {
                $scopedRequire($fileInfo->getPathname());
            }
        }
    }

    private function spoolQueuedContentTypeTableConfigurations(array $queue): void
    {
        foreach ($queue as $queuedRegistration) {
            [$extensionName, $templatePathAndFilename, , $contentType] = $queuedRegistration;
            $contentType = $contentType ?: $this->determineContentType($extensionName, $templatePathAndFilename);
            $this->contentTypeBuilder->addBoilerplateTableConfiguration($contentType);
        }
    }

    private function determineContentType(
        string $providerExtensionName,
        string $templatePathAndFilename
    ): string {
        // Determine which plugin name and controller action to emulate with this CType, base on file name.
        $controllerExtensionName = $providerExtensionName;
        $emulatedPluginName = ucfirst(pathinfo($templatePathAndFilename, PATHINFO_FILENAME));
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($controllerExtensionName));
        $fullContentType = $extensionSignature . '_' . strtolower($emulatedPluginName);
        return $fullContentType;
    }

    protected function spoolQueuedContentTypeRegistrations(array $queue): void
    {
        $applicationContext = $this->getApplicationContext();
        $providers = [];
        foreach ($queue as $queuedRegistration) {
            [
                $providerExtensionName,
                $templateFilename,
                $providerClassName,
                $contentType,
                $pluginName,
                $controllerActionName
            ] = $queuedRegistration;
            try {
                $contentType = $contentType ?: $this->determineContentType($providerExtensionName, $templateFilename);
                $defaultControllerExtensionName = 'FluidTYPO3.Flux';

                /** @var ProviderInterface $provider */
                $provider = $this->contentTypeBuilder->configureContentTypeFromTemplateFile(
                    $providerExtensionName,
                    $templateFilename,
                    $providerClassName ?? Provider::class,
                    $contentType,
                    $defaultControllerExtensionName,
                    $controllerActionName
                );

                $provider->setPluginName($pluginName);

                Core::registerConfigurationProvider($provider);

                $providers[] = $provider;
            } catch (Exception $error) {
                if (!$applicationContext->isProduction()) {
                    throw $error;
                }
            }
        }

        $backup = $GLOBALS['TYPO3_REQUEST'] ?? null;

        $GLOBALS['TYPO3_REQUEST'] = $this->requestBuilder->getServerRequest();

        $sortingValues = $this->resolveSortingValues($providers);
        uasort(
            $providers,
            function (ProviderInterface $item1, ProviderInterface $item2) use ($sortingValues) {
                $contentType1 = $item1->getContentObjectType();
                $contentType2 = $item2->getContentObjectType();
                return ($sortingValues[$contentType1] ?? 0) <=> ($sortingValues[$contentType2] ?? 0);
            }
        );

        $providerExtensionNamesToFlush = [];

        foreach ($providers as $provider) {
            $contentType = $provider->getContentObjectType();
            $virtualRecord = ['CType' => $contentType];
            $providerExtensionName = $provider->getExtensionKey($virtualRecord);

            $providerExtensionNamesToFlush[] = $providerExtensionName;

            try {
                $this->contentTypeBuilder->registerContentType($providerExtensionName, $contentType, $provider);
            } catch (PageNotFoundException|DBALException $error) {
                // Suppressed: Flux bootstrap does not care if a page can be resolved or not.
            } catch (Exception $error) {
                if (!$applicationContext->isProduction()) {
                    $GLOBALS['TYPO3_REQUEST'] = $backup;
                    throw $error;
                }
            }
        }

        // Flush the cache entry that was generated; make sure any TypoScript overrides will take place once
        // all TypoScript is finally loaded.
        foreach (array_unique($providerExtensionNamesToFlush) as $providerExtensionName) {
            $this->cacheService->remove(
                'viewpaths_' . ExtensionNamingUtility::getExtensionKey($providerExtensionName)
            );
        }

        $GLOBALS['TYPO3_REQUEST'] = $backup;
    }

    /**
     * @param ProviderInterface[] $providers
     */
    private function resolveSortingValues(array $providers): array
    {
        /** @var array|false $fromCache */
        $fromCache = $this->cacheService->getFromCaches(self::CACHE_ID_SORTINGS);
        if ($fromCache) {
            return $fromCache;
        }

        $store = true;
        $sortingValues = [];
        foreach ($providers as $provider) {
            $contentObjectType = $provider->getContentObjectType();
            try {
                $form = $provider->getForm(['CType' => $contentObjectType]);
                $sortingValues[$contentObjectType] = $this->resolveSortingValue($form);
            } catch (\Exception $exception) {
                // A raised exception is ignored and causes sorting value to be "0", but disables storing to cache.
                $store = false;
            }
        }

        if ($store) {
            $this->cacheService->setInCaches($sortingValues, true, self::CACHE_ID_SORTINGS);
        }

        return $sortingValues;
    }

    private function resolveSortingValue(?Form $form): string
    {
        $sortingOptionValue = 0;
        if ($form instanceof Form\FormInterface) {
            if ($form->hasOption(FormOption::SORTING)) {
                $sortingOptionValue = $form->getOption(FormOption::SORTING);
            } elseif ($form->hasOption(FormOption::TEMPLATE_FILE)) {
                /** @var string $templateFilename */
                $templateFilename = $form->getOption(FormOption::TEMPLATE_FILE);
                $sortingOptionValue = basename($templateFilename);
            } else {
                $sortingOptionValue = $form->getId();
            }
        }
        return !is_scalar($sortingOptionValue) ? '0' : (string) $sortingOptionValue;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getApplicationContext(): ApplicationContext
    {
        return Environment::getContext();
    }
}
