<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\ContentService;
use TYPO3\CMS\Backend\RecordList\RecordListGetTableHookInterface;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

/**
 * This class removes all elements with colPos=18181 from db list view to avoid trouble with sorting
 * containers into child elements.
 */
class RecordListGetTableHookSubscriber implements RecordListGetTableHookInterface
{

    /**
     * modifies the DB list query: no elements with colPos=18181 are shown, these are child elements
     *
     * @param string $table The current database table
     * @param integer $pageId The record's page ID
     * @param string $additionalWhereClause An additional WHERE clause
     * @param string $selectedFieldsList Comma separated list of selected fields
     * @param DatabaseRecordList $parentObject Parent localRecordList object
     * @return void
     */
    public function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject)
    {
        if ('tt_content' === $table) {
            $additionalWhereClause .= ' AND colPos <> ' . ContentService::COLPOS_FLUXCONTENT;
        }
    }
}
