<?php
namespace FluidTYPO3\Flux\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Configuration\ConfigurationManager as CoreConfigurationManager;

/**
 * Flux ConfigurationManager implementation
 *
 * More context-sensitive ConfigurationManager with TS resolve
 * methods optimised for use in the backend.
 */
class ConfigurationManager extends CoreConfigurationManager
{

    /**
     * @var BackendConfigurationManager
     */
    protected $concreteConfigurationManager;

    /**
     * @return void
     */
    protected function initializeConcreteConfigurationManager()
    {
        if (true === $this->environmentService->isEnvironmentInFrontendMode()) {
            $this->concreteConfigurationManager = $this->objectManager->get(FrontendConfigurationManager::class);
        } else {
            $this->concreteConfigurationManager = $this->objectManager->get(BackendConfigurationManager::class);
        }
    }

    /**
     * @param integer $currentPageId
     * @return void
     */
    public function setCurrentPageUid($currentPageId)
    {
        if (true === $this->concreteConfigurationManager instanceof BackendConfigurationManager) {
            $this->concreteConfigurationManager->setCurrentPageId($currentPageId);
        }
    }

    /**
     * Extended page UID fetch
     *
     * Uses a range of additional page UID resolve methods to
     * find the currently active page UID from URL, active
     * record, etc.
     *
     * @return integer
     */
    public function getCurrentPageId()
    {
        if (true === $this->concreteConfigurationManager instanceof BackendConfigurationManager) {
            return $this->concreteConfigurationManager->getCurrentPageId();
        }
        return 0;
    }
}
