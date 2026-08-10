<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Domain\RecordFactory;

/**
 * Proxy around core's BackendLayoutRenderer, to be able to transfer context around together with the drawing class.
 */
#[Autoconfigure(public: true, autowire: true)]
class BackendLayoutRenderer
{
    /**
     * @var PageLayoutContext
     */
    protected ?PageLayoutContext $transferredContext = null;

    private \TYPO3\CMS\Backend\View\Drawing\BackendLayoutRenderer $backendLayoutRenderer;

    public function __construct(BackendViewFactory $backendViewFactory, ?RecordFactory $recordFactory = null)
    {
        $this->backendLayoutRenderer = new \TYPO3\CMS\Backend\View\Drawing\BackendLayoutRenderer(
            $backendViewFactory,
            $recordFactory
        );
    }

    public function getContext(): PageLayoutContext
    {
        return $this->transferredContext;
    }

    public function setContext(PageLayoutContext $context): void
    {
        $this->transferredContext = $context;
    }

    public function drawContent(
        ServerRequestInterface $request,
        PageLayoutContext $pageLayoutContext,
        bool $renderUnused = true
    ): string {
        return $this->backendLayoutRenderer->drawContent($request, $pageLayoutContext, $renderUnused);
    }
}
