<?php
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
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
     * @return ProviderInterface|NULL
     */
    public function resolvePrimaryConfigurationProvider($table, $fieldName, array $row = null, $extensionKey = null)
    {
        return array_pop($this->resolveConfigurationProviders($table, $fieldName, $row, $extensionKey));
    }

    /**
     * Resolves a ConfigurationProvider which can provide a working FlexForm
     * configuration based on the given parameters.
     *
     * @param string $table
     * @param string $fieldName
     * @param array $row
     * @param string $extensionKey
     * @throws \RuntimeException
     * @return ProviderInterface[]
     */
    public function resolveConfigurationProviders($table, $fieldName, array $row = null, $extensionKey = null)
    {
        $row = false === is_array($row) ? [] : $row;
        $providers = $this->getAllRegisteredProviderInstances();
        usort($providers, function ($a, $b) use ($row) {
            $priorityA = $a->getPriority($row);
            $priorityB = $b->getPriority($row);
            if ($priorityA === $priorityB) {
                return 0;
            }
            return $priorityA < $priorityB ? -1 : 1;
        });
        $providers = array_filter($providers, function ($provider) use ($row, $table, $fieldName) {
            return $provider->trigger($row, $table, $fieldName);
        });
        return $providers;
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
