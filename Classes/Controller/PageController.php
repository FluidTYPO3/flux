<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\RenderingContextBuilder;
use FluidTYPO3\Flux\Builder\RequestBuilder;
use FluidTYPO3\Flux\Provider\Interfaces\BasicProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\PageService;
use TYPO3\CMS\Extbase\Mvc\Response;

class PageController extends AbstractFluxController implements PageControllerInterface
{
    protected ?string $fluxRecordField = 'tx_fed_page_flexform';
    protected ?string $fluxTableName = 'pages';

    protected PageService $pageService;

    public function __construct(
        FluxService $fluxService,
        RenderingContextBuilder $renderingContextBuilder,
        RequestBuilder $requestBuilder,
        PageService $pageService
    ) {
        parent::__construct($fluxService, $renderingContextBuilder, $requestBuilder);
        $this->pageService = $pageService;
    }

    protected function initializeProvider(): void
    {
        $record = $this->getRecord();
        $provider = $this->configurationService->resolvePageProvider($record);
        if ($provider instanceof BasicProviderInterface) {
            $this->provider = $provider;
        }
    }

    public function getRecord(): array
    {
        return $GLOBALS['TSFE']->page ?? [];
    }
}
