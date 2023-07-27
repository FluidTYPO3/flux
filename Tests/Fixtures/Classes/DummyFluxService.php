<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use PHPUnit\Framework\MockObject\Generator;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Service\FlexFormService;

class DummyFluxService extends FluxService
{
    public function __construct()
    {
        $this->serverRequest = $this->createMock(ServerRequest::class);
        $this->recordService = $this->createMock(WorkspacesAwareRecordService::class);
        $this->resourceFactory = $this->createMock(ResourceFactory::class);
        $this->providerResolver = $this->createMock(ProviderResolver::class);
        $this->cacheManager = $this->createMock(CacheManager::class);
        $this->transformer = $this->createMock(FormDataTransformer::class);
        $this->flexFormService = $this->createMock(FlexFormService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
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

    public function setCacheManager(CacheManager $cacheManager): void
    {
        $this->cacheManager = $cacheManager;
    }

    public function setFormDataTransformer(FormDataTransformer $transformer): void
    {
        $this->transformer = $transformer;
    }

    public function setFlexFormService(FlexFormService $flexFormService): void
    {
        $this->flexFormService = $flexFormService;
    }

    private function createMock(string $className): object
    {
        return (new Generator())->getMock(
            type: $className,
            callOriginalConstructor: false
        );
    }
}
