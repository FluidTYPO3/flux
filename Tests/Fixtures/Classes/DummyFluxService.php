<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use PHPUnit\Framework\MockObject\Generator;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class DummyFluxService extends FluxService
{
    public function __construct()
    {
        $this->serverRequest = $this->createMock(ServerRequest::class);
        $this->recordService = $this->createMock(WorkspacesAwareRecordService::class);
        $this->resourceFactory = $this->createMock(ResourceFactory::class);
        $this->providerResolver = $this->createMock(ProviderResolver::class);
        $this->cacheService = $this->createMock(CacheService::class);
        $this->transformer = $this->createMock(FormDataTransformer::class);
        $this->flexFormService = $this->createMock(FlexFormService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->configurationManager = $this->createMock(ConfigurationManagerInterface::class);
    }

    public function setServerRequest(ServerRequest $serverRequest): void
    {
        $this->serverRequest = $serverRequest;
    }

    public function setRecordService(RecordService $recordService): void
    {
        $this->recordService = $recordService;
    }

    public function setResourceFactory(ResourceFactory $resourceFactory): void
    {
        $this->resourceFactory = $resourceFactory;
    }

    public function setProviderResolver(ProviderResolver $providerResolver): void
    {
        $this->providerResolver = $providerResolver;
    }

    public function setCacheService(CacheService $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    public function setFormDataTransformer(FormDataTransformer $transformer): void
    {
        $this->transformer = $transformer;
    }

    public function setFlexFormService(FlexFormService $flexFormService): void
    {
        $this->flexFormService = $flexFormService;
    }

    public function setConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    private function createMock(string $className): object
    {
        return (new Generator())->getMock($className, [], [], '', false);
    }
}
