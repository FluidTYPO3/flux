<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\ContentService;

class PageLayoutView extends \TYPO3\CMS\Backend\View\PageLayoutView
{
    /**
     * @param array $pageinfo
     */
    public function setPageinfo($pageinfo)
    {
        $this->pageinfo = $pageinfo;
    }

    /**
     * @return array
     */
    public function getPageinfo()
    {
        return $this->pageinfo;
    }

    /**
     * Public access version of parent's method
     *
     * @param array $rowArray
     */
    public function generateTtContentDataArray(array $rowArray)
    {
        parent::generateTtContentDataArray($rowArray);
    }

    /**
     * @return PageLayoutController
     */
    public function getPageLayoutController()
    {
        return parent::getPageLayoutController();
}

    /**
     * Checks whether translated Content Elements exist in the desired language
     * If so, deny creating new ones via the UI
     *
     * @param array $contentElements
     * @param int $language
     * @return bool
     */
    public function checkIfTranslationsExistInLanguage(array $contentElements, $language)
    {
        return parent::checkIfTranslationsExistInLanguage($contentElements, $language);
    }

    /**
     * Gets content records per column.
     * This is required for correct workspace overlays.
     *
     * @param int $id Page Id to be used (not used at all, but part of the API, see $this->pidSelect)
     * @param int $fluxParent Id of tx_flux_parent
     * @param string $columnName tx_flux_column value to be considered to be shown
     * @param string $additionalWhereClause Additional where clause for database select
     * @return array Associative array for each column (colPos)
     */
    public function getContentRecordsPerFluxColumn($id, $fluxParent, $columnName, $additionalWhereClause = '')
    {
        $additionalWhereClause = $additionalWhereClause ? ' AND ' . $additionalWhereClause : '';
        $columnName = '\'' . $this->getDatabase()->quoteStr($columnName, 'tt_content') . '\'';
        $queryParts = $this->makeQueryArray('tt_content', $id,
            'AND colPos = ' . (integer)ContentService::COLPOS_FLUXCONTENT
            . ' AND tx_flux_parent = ' . (integer) $fluxParent
            . ' AND tx_flux_column = ' . $columnName . $additionalWhereClause);
        $result = $this->getDatabase()->exec_SELECT_queryArray($queryParts);
        // Traverse any selected elements and render their display code:
        $rowArr = $this->getResult($result);
        $contentRecordsPerColumn = [];

        foreach ($rowArr as $record) {
            $contentRecordsPerColumn[ContentService::COLPOS_FLUXCONTENT][] = $record;
        }

        return $contentRecordsPerColumn;
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }


}

