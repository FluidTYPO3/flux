<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\Overrides;

use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;

class LegacyChimeraConfigurationManager extends AbstractChimeraConfigurationManager
{
    protected function initializeConcreteConfigurationManager(): void
    {
        $this->refreshRequestIfNecessary();

        parent::initializeConcreteConfigurationManager();

        $this->frontendConfigurationManager = $this->objectManager->get(FrontendConfigurationManager::class);
        $this->backendConfigurationManager = $this->objectManager->get(BackendConfigurationManager::class);
    }
}
