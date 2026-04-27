<?php
namespace FluidTYPO3\Flux\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

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
