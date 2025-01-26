<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

/*
 * This file is part of the FluidTYPO3/Fluidpages project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\RenderingContextBuilder;
use FluidTYPO3\Flux\Builder\RequestBuilder;
use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Controller\PageController;
use FluidTYPO3\Flux\Integration\Resolver;
use FluidTYPO3\Flux\Provider\Interfaces\ControllerProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use PHPUnit\Framework\MockObject\Generator;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class DummyPageController extends PageController
{
    protected array $record = [];

    public function __construct()
    {
        $renderingContextBuilder = $this->createMock(RenderingContextBuilder::class);
        $requestBuilder = $this->createMock(RequestBuilder::class);
        $recordService = $this->createMock(WorkspacesAwareRecordService::class);
        $typoScriptService = $this->createMock(TypoScriptService::class);
        $providerResolver = $this->createMock(ProviderResolver::class);
        $resolver = $this->createMock(Resolver::class);
        $viewBuilder = $this->createMock(ViewBuilder::class);

        parent::__construct(
            $renderingContextBuilder,
            $requestBuilder,
            $recordService,
            $typoScriptService,
            $providerResolver,
            $resolver,
            $viewBuilder
        );
    }

    public function setView(ViewInterface $view): void
    {
        $this->view = $view;
    }

    public function getRecord(): array
    {
        return $this->record;
    }

    public function setRecord(array $record): void
    {
        $this->record = $record;
    }

    public function getProvider(): ?ControllerProviderInterface
    {
        return $this->provider;
    }

    private function createMock(string $className): object
    {
        return (new Generator())->getMock($className, [], [], '', false);
    }
}
