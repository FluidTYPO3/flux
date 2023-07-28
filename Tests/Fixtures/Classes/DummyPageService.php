<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use PHPUnit\Framework\MockObject\Generator;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class DummyPageService extends PageService
{
    public function __construct()
    {
        $this->configurationManager = $this->createMock(ConfigurationManagerInterface::class);
        $this->configurationService = new DummyFluxService();
        $this->workspacesAwareRecordService = $this->createMock(WorkspacesAwareRecordService::class);
    }

    public function setConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    public function setConfigurationService(DummyFluxService $configurationService): void
    {
        $this->configurationService = $configurationService;
    }

    public function setWorkspacesAwareRecordService(WorkspacesAwareRecordService $workspacesAwareRecordService): void
    {
        $this->workspacesAwareRecordService = $workspacesAwareRecordService;
    }

    private function createMock(string $className): object
    {
        return (new Generator())->getMock($className, [], [], '', false);
    }
}
