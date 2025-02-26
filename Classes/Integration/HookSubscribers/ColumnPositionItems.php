<?php

namespace FluidTYPO3\Flux\Integration\HookSubscribers;

use FluidTYPO3\Flux\Integration\FormEngine\SelectOption;
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
        $colPos = isset($parameters['row']['colPos']) ? (int)$parameters['row']['colPos'] : 0;

        $parentRecordUid = ColumnNumberUtility::calculateParentUid($colPos);
        $parentRecord = $this->recordService->getSingle('tt_content', '*', $parentRecordUid);
        $provider = $this->providerResolver->resolvePrimaryConfigurationProvider('tt_content', null, $parentRecord);

        if ($parentRecord && $provider instanceof GridProviderInterface) {
            $grid = $provider->getGrid($parentRecord);
            /** @var SelectOption $dividerItem */
            $dividerItem = GeneralUtility::makeInstance(
                SelectOption::class,
                'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.backendLayout.columnsInParent',
                '--div--'
            );
            $parameters['items'][] = $dividerItem->toArray();

            foreach ($grid->getRows() as $row) {
                foreach ($row->getColumns() as $column) {
                    /** @var SelectOption $item */
                    $item = GeneralUtility::makeInstance(
                        SelectOption::class,
                        $column->getLabel(),
                        ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                            $parentRecordUid,
                            $column->getColumnPosition()
                        )
                    );
                    $parameters['items'][] = $item->toArray();
                }
            }
        }
    }
}
