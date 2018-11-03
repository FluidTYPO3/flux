<?php
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
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Provider Resolver
 *
 * Returns one or more Provider instances based on parameters.
 */
class ProviderResolver implements SingletonInterface
{
    /**
     * @var array
     */
    protected $providers = null;

    /**
     * @var FluxService
     */
    protected $configurationService;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        $this->configurationService = $configurationService;
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
     * ResolveUtility the top-priority ConfigurationPrivider which can provide
     * a working FlexForm configuration baed on the given parameters.
     *
     * @param string $table
     * @param string $fieldName
     * @param array $row
     * @param string $extensionKey
     * @param string|array $interfaces One or more specific interfaces the Provider must implement.
     * @return ProviderInterface|NULL
     */
    public function resolvePrimaryConfigurationProvider(
        $table,
        $fieldName,
        array $row = null,
        $extensionKey = null,
        $interfaces = null
    ) {
        $providers = $this->resolveConfigurationProviders($table, $fieldName, $row, $extensionKey, $interfaces);
        return reset($providers) ?: null;
    }

    /**
     * Resolves a ConfigurationProvider which can provide a working FlexForm
     * configuration based on the given parameters.
     *
     * @param string $table Table the Provider must match.
     * @param string $fieldName Field in the table the Provider must match.
     * @param array|null $row The record from table which the Provider must handle, or null if any record.
     * @param string|null $extensionKey The extension key the Provider must match, or null if any extension key.
     * @param string|array $interfaces One or more specific interfaces the Provider must implement.
     * @throws \RuntimeException
     * @return ProviderInterface[]
     */
    public function resolveConfigurationProviders(
        $table,
        $fieldName,
        array $row = null,
        $extensionKey = null,
        $interfaces = null
    ) {
        $row = false === is_array($row) ? [] : $row;
        $providers = $this->getAllRegisteredProviderInstances();
        if ($interfaces) {
            $providers = array_filter(
                $providers,
                function (ProviderInterface $provider) use ($interfaces) {
                    foreach ((array) $interfaces as $interface) {
                        if (!$provider instanceof $interface) {
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
                return !$provider instanceof RecordProviderInterface || $provider->trigger($row, $table, $fieldName, $extensionKey);
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
    public function loadTypoScriptConfigurationProviderInstances()
    {
        $providerConfigurations = (array) $this->configurationService->getTypoScriptByPath('plugin.tx_flux.providers');
        $providers = [];
        foreach ($providerConfigurations as $name => $providerSettings) {
            $className = Provider::class;
            if (isset($providerSettings['className']) && class_exists($providerSettings['className'])) {
                $className = $providerSettings['className'];
            }
            /** @var ProviderInterface $provider */
            $provider = $this->objectManager->get($className);
            $provider->setName($name);
            $provider->loadSettings($providerSettings);
            $providers[$name] = $provider;
        }
        return $providers;
    }

    /**
     * @return ProviderInterface[]
     */
    protected function getAllRegisteredProviderInstances()
    {
        if (null === $this->providers) {
            $providers = $this->loadCoreRegisteredProviders();
            $typoScriptConfigurationProviders = $this->loadTypoScriptConfigurationProviderInstances();
            $providers = array_merge($providers, $typoScriptConfigurationProviders);
            $this->providers = $this->validateAndInstantiateProviders($providers);
        }
        return $this->providers;
    }

    /**
     * @param array $providers
     * @return ProviderInterface[]
     * @throws \RuntimeException
     */
    protected function validateAndInstantiateProviders(array $providers)
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
                $provider = $classNameOrInstance;
            } else {
                $provider = $this->objectManager->get($classNameOrInstance);
            }
            $instances[] = $provider;
        }
        return $instances;
    }

    /**
     * @return array
     */
    protected function loadCoreRegisteredProviders()
    {
        return Core::getRegisteredFlexFormProviders();
    }
}
