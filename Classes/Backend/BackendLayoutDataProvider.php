<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Proxy\DataProviderContextProxy;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayoutCollection;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderInterface;

class BackendLayoutDataProvider implements DataProviderInterface
{
    protected ProviderResolver $providerResolver;
    protected WorkspacesAwareRecordService $recordService;

    public function __construct(ProviderResolver $providerResolver, WorkspacesAwareRecordService $recordService)
    {
        $this->providerResolver = $providerResolver;
        $this->recordService = $recordService;
    }

    public function addBackendLayouts(
        DataProviderContext $dataProviderContext,
        BackendLayoutCollection $backendLayoutCollection
    ): void {
        $backendLayout = $this->getBackendLayout(
            'grid',
            DataProviderContextProxy::readPageUidFromObject($dataProviderContext)
        );
        if ($backendLayout) {
            $backendLayoutCollection->add($backendLayout);
        }
    }

    /**
     * Gets a backend layout by (regular) identifier.
     *
     * @param string $identifier
     * @param integer $pageId
     */
    public function getBackendLayout($identifier, $pageId): ?BackendLayout
    {
        $emptyLayout = $this->createBackendLayoutInstance($identifier, 'Empty', '');
        $record = $this->recordService->getSingle('pages', '*', $pageId);
        if (null === $record) {
            return $emptyLayout;
        }
        $provider = $this->resolveProvider($record);
        if (!$provider instanceof PageProvider) {
            return $emptyLayout;
        }
        $grid = $provider->getGrid($record);
        return $grid->buildBackendLayout(0);
    }

    protected function resolveProvider(array $record): ?ProviderInterface
    {
        $record = $this->recordService->getSingle('pages', '*', $record['uid']);

        // Stop processing if no template configured in rootline
        if (null === $record) {
            return null;
        }

        return $this->providerResolver->resolvePageProvider($record);
    }

    /**
     * @param string|array $configuration
     * @codeCoverageIgnore
     */
    protected function createBackendLayoutInstance(string $identifier, string $title, $configuration): BackendLayout
    {
        return new BackendLayout($identifier, 'Empty', '');
    }
}
