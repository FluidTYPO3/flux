<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\Overrides;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;

class ChimeraConfigurationManager extends AbstractChimeraConfigurationManager
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($container);
        $this->initializeConcreteConfigurationManager();
    }

    protected function initializeConcreteConfigurationManager(): void
    {
        parent::initializeConcreteConfigurationManager();

        /** @var FrontendConfigurationManager $frontendConfigurationManager */
        $frontendConfigurationManager = $this->container->get(FrontendConfigurationManager::class);
        /** @var BackendConfigurationManager $backendConfigurationManager */
        $backendConfigurationManager = $this->container->get(BackendConfigurationManager::class);

        $this->frontendConfigurationManager = $frontendConfigurationManager;
        $this->backendConfigurationManager = $backendConfigurationManager;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->updateRequest($request);
    }
}
