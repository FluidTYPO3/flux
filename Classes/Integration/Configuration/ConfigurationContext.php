<?php
namespace FluidTYPO3\Flux\Integration\Configuration;

use TYPO3\CMS\Core\SingletonInterface;

class ConfigurationContext implements SingletonInterface
{
    private bool $bootMode = false;

    public function isBootMode(): bool
    {
        return $this->bootMode;
    }

    public function setBootMode(bool $bootMode): void
    {
        $this->bootMode = $bootMode;
    }
}
