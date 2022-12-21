<?php
namespace FluidTYPO3\Flux\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Backend\Controller\Event\AfterPageColumnsSelectedForLocalizationEvent;

class AfterLocalizationControllerColumnsEventListener
{
    public function modifyColumnsManifest(AfterPageColumnsSelectedForLocalizationEvent $event): void
    {
        $columns = $event->getColumns();
        $columnList = $event->getColumnList();
        foreach ($event->getRecords() as $record) {
            $colPos = (int) $record['colPos'];
            if ($colPos >= ColumnNumberUtility::MULTIPLIER && !isset($columns['columns'][$colPos])) {
                $columns[$colPos] = 'Nested';
                $columnList[] = (string) $colPos;
            }
        }
        $event->setColumns($columns);
        $event->setColumnList(array_values(array_unique($columnList)));
    }
}
