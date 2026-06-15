<?php

namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ColumnPositionItems
{
    private WorkspacesAwareRecordService $recordService;
    private ProviderResolver $providerResolver;

    public function __construct(WorkspacesAwareRecordService $recordService, ProviderResolver $providerResolver)
    {
        $this->recordService = $recordService;
        $this->providerResolver = $providerResolver;
    }

    /**
     * Gets colPos items to be shown in the forms engine.
     * This method is called as "itemsProcFunc" with the accordant context
     * for tt_content.colPos.
     */
    public function colPosListItemProcFunc(array &$parameters): void
    {
        if (!isset($parameters['row']['colPos']) || ((string) $parameters['row']['colPos']) === '') {
            return;
        }
        $parentRecordUid = ColumnNumberUtility::calculateParentUid($parameters['row']['colPos']);
        $parentRecord = $this->recordService->getSingle('tt_content', '*', $parentRecordUid);
        $provider = $this->providerResolver->resolvePrimaryConfigurationProvider('tt_content', null, $parentRecord);
        if ($parentRecord && $provider instanceof GridProviderInterface) {
            $grid = $provider->getGrid($parentRecord);
            $parameters['items'][] = [
                'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.backendLayout.columnsInParent',
                'value' => '--div--',
            ];
            foreach ($grid->getRows() as $row) {
                foreach ($row->getColumns() as $column) {
                    $parameters['items'][] = [
                        'label' => $column->getLabel(),
                        'value' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                            $parentRecordUid,
                            $column->getColumnPosition()
                        ),
                    ];
                }
            }
        }
    }
}
