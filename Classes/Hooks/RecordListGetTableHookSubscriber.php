<?php
/**
 * /***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 sitegeist media solutions GmbH
 *  author: Alexander Bohndorf <bohndorf@sitegeist.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace FluidTYPO3\Flux\Hooks;

/**
 * This class removes all elements with colPos=18181 from db list view to avoid trouble with sorting
 * containers into child elements
 *
 * Class RecordListGetTableHookSubscriber
 * @package FluidTYPO3\Flux\Hooks
 */
class RecordListGetTableHookSubscriber implements \TYPO3\CMS\Backend\RecordList\RecordListGetTableHookInterface {

	/**
	 * modifies the DB list query: no elements with colPos=18181 are shown, these are child elements
	 *
	 * @param string $table The current database table
	 * @param integer $pageId The record's page ID
	 * @param string $additionalWhereClause An additional WHERE clause
	 * @param string $selectedFieldsList Comma separated list of selected fields
	 * @param \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList $parentObject Parent localRecordList object
	 * @return void
	 */
	public function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject){
		if($table=='tt_content') {
			$additionalWhereClause .= ' AND colPos<>' . \FluidTYPO3\Flux\Service\ContentService::COLPOS_FLUXCONTENT;
		}
	}
}
