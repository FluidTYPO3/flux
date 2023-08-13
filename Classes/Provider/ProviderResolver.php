<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
use FluidTYPO3\Flux\Service\TypoScriptService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provider Resolver
 *
 * Returns one or more Provider instances based on parameters.
 */
class ProviderResolver implements SingletonInterface
{
    protected array $providers = [];
    protected TypoScriptService $typoScriptService;

    public function __construct(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
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
        $providers = $this->resolveConfigurationProviders($table, $fieldName, $row, $extensionKey, $interfaces);
        return reset($providers) ?: null;
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
    ) {
        $row = false === is_array($row) ? [] : $row;
        $providers = $this->getAllRegisteredProviderInstances();
        if ($interfaces) {
            $providers = array_filter(
                $providers,
                function ($provider) use ($interfaces) {
                    foreach ((array) $interfaces as $interface) {
                        if (!is_a($provider, $interface, true)) {
                            return false;
                        }
                    }
                    return true;
                }
            );
        }
        usort(
            $providers,
            function (ProviderInterface $a, ProviderInterface $b) use ($row) {
                return $b->getPriority($row) <=> $a->getPriority($row);
            }
        );
        // RecordProviderInterface being missing will automatically include the Provider. Those that do
        // implement the interface will be asked if they should trigger on the table/field/row/ext combo.
        $providers = array_filter(
            $providers,
            function (ProviderInterface $provider) use ($row, $table, $fieldName, $extensionKey) {
                return !$provider instanceof RecordProviderInterface
                    || $provider->trigger($row, $table, $fieldName, $extensionKey);
            }
        );
        return HookHandler::trigger(
            HookHandler::PROVIDERS_RESOLVED,
            [
                'table' => $table,
                'field' => $fieldName,
                'record' => $row,
                'extensionKey' => $extensionKey,
                'interfaces' => $interfaces,
                'providers' => $providers
            ]
        )['providers'];
    }

    /**
     * @return ProviderInterface[]
     */
    public function loadTypoScriptConfigurationProviderInstances(): array
    {
        /** @var array[] $providerConfigurations */
        $providerConfigurations = (array) $this->typoScriptService->getTypoScriptByPath('plugin.tx_flux.providers');
        $providers = [];
        foreach ($providerConfigurations as $name => $providerSettings) {
            $className = Provider::class;
            if (isset($providerSettings['className']) && class_exists($providerSettings['className'])) {
                $className = $providerSettings['className'];
            }
            /** @var ProviderInterface $provider */
            $provider = GeneralUtility::makeInstance($className);
            $provider->setName($name);
            $provider->loadSettings($providerSettings);
            $providers[$name] = $provider;
        }
        return $providers;
    }

    /**
     * @return ProviderInterface[]
     */
    protected function getAllRegisteredProviderInstances(): array
    {
        if (empty($this->providers)) {
            $providers = $this->loadCoreRegisteredProviders();
            $typoScriptConfigurationProviders = $this->loadTypoScriptConfigurationProviderInstances();
            $providers = array_merge($providers, $typoScriptConfigurationProviders);
            $this->providers = $this->validateAndInstantiateProviders($providers);
        }
        return $this->providers;
    }

    /**
     * @return ProviderInterface[]
     */
    protected function validateAndInstantiateProviders(array $providers): array
    {
        $instances = [];
        foreach ($providers as $classNameOrInstance) {
            if (!is_a($classNameOrInstance, ProviderInterface::class, true)) {
                $className = is_object($classNameOrInstance) ? get_class($classNameOrInstance) : $classNameOrInstance;
                throw new \RuntimeException(
                    $className . ' must implement ProviderInterfaces from Flux/Provider',
                    1327173536
                );
            }
            if (true === is_object($classNameOrInstance)) {
                /** @var ProviderInterface $provider */
                $provider = $classNameOrInstance;
            } else {
                /** @var ProviderInterface $provider */
                $provider = GeneralUtility::makeInstance($classNameOrInstance);
            }
            $instances[] = $provider;
        }
        return $instances;
    }

    protected function loadCoreRegisteredProviders(): array
    {
        return Core::getRegisteredFlexFormProviders();
    }
}
