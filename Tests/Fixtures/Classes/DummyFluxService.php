<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Service\FluxService;
use PHPUnit\Framework\MockObject\Generator;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Service\FlexFormService;

class DummyFluxService extends FluxService
{
    public function __construct()
    {
        $this->transformer = $this->createMock(FormDataTransformer::class);
        $this->flexFormService = $this->createMock(FlexFormService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
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
        return (new Generator())->getMock($className, [], [], '', false);
    }
}
