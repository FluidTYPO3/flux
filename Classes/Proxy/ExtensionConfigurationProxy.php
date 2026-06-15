<?php

namespace FluidTYPO3\Flux\Proxy;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

class ExtensionConfigurationProxy
{
    public function __construct(private ExtensionConfiguration $extensionConfiguration)
    {
    }

    public function get(string $extension, string $path = ''): mixed
    {
        return $this->extensionConfiguration->get($extension, $path);
    }

    public function set(string $extension, mixed $value = null): void
    {
        $this->extensionConfiguration->set($extension, $value);
    }

    public function setAll(array $configuration, bool $skipWriteIfLocalConfigurationDoesNotExist = false): void
    {
        $this->extensionConfiguration->setAll($configuration, $skipWriteIfLocalConfigurationDoesNotExist);
    }

    public function synchronizeExtConfTemplateWithLocalConfigurationOfAllExtensions(
        bool $skipWriteIfLocalConfigurationDoesNotExist = false
    ): void {
        $this->extensionConfiguration->synchronizeExtConfTemplateWithLocalConfiguration(
            $skipWriteIfLocalConfigurationDoesNotExist
        );
    }

    public function synchronizeExtConfTemplateWithLocalConfiguration(string $extensionKey): void
    {
        $this->extensionConfiguration->synchronizeExtConfTemplateWithLocalConfiguration($extensionKey);
    }
}
