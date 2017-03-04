<?php
namespace FluidTYPO3\Flux\Backend\Domain\Repository;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */


use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;

/**
 * Repository for record localizations legacy version for TYPO3 7.6
 */
class LegacyLocalizationRepository extends LocalizationRepository
{
    /**
     * Get records for copy process
     * We must use a legacy class, because the result of this method
     * is in TYPO3 7.6 a mysqli_result object and in TYPO3 8 it's a \Doctrine\DBAL\Driver\Statement
     *
     * @param int $pageId
     * @param int $colPos
     * @param int $destLanguageId
     * @param int $languageId
     * @param string $fields
     *
     * @return bool|\mysqli_result|object
     * @throws \InvalidArgumentException
     */
    public function getRecordsToCopyDatabaseResult($pageId, $colPos, $destLanguageId, $languageId, $fields = '*')
    {
        if ($colPos < ContentService::COLPOS_FLUXCONTENT) {
            return parent::getRecordsToCopyDatabaseResult($pageId, $colPos, $destLanguageId, $languageId, $fields);
        }

        // here starts the flux related code
        $db = $this->getDatabaseConnection();

        list($fluxParent, $fluxColumn) = $this->contentService->getTargetAreaStoredInSession($colPos);
        $fluxColumn = '\'' . $this->getDatabaseConnection()->quoteStr($fluxColumn, 'tt_content') . '\'';
        $fluxParent = (integer) $fluxParent;
        $pageId = (integer) $pageId;

        $record = $db->exec_SELECTgetSingleRow('*','tt_content','uid = ' . $fluxParent . $this->getExcludeQueryPart());
        $fluxParentParent = (integer) $record[CompatibilityRegistry::get(ContentService::LANGUAGE_SOURCE_FIELD)];
        // Get original uid of existing elements triggered language / colpos
        $originalUids = $db->exec_SELECTgetRows(
            't3_origuid',
            'tt_content',
            'sys_language_uid=' . (integer) $destLanguageId
            . ' AND tt_content.colPos = ' . ContentService::COLPOS_FLUXCONTENT
            . ' AND tt_content.tx_flux_parent = ' . $fluxParent
            . ' AND tt_content.tx_flux_column = ' .$fluxColumn
            . ' AND tt_content.pid=' . $pageId
            . $this->getExcludeQueryPart(),
            '',
            '',
            '',
            't3_origuid'
        );
        $originalUidList = $db->cleanIntList(implode(',', array_keys($originalUids)));

        $res = $db->exec_SELECTquery(
            $fields,
            'tt_content',
            'tt_content.sys_language_uid=' . (integer) $languageId
            . ' AND tt_content.colPos = ' . ContentService::COLPOS_FLUXCONTENT
            . ' AND tt_content.tx_flux_parent = ' . $fluxParentParent
            . ' AND tt_content.tx_flux_column = ' .$fluxColumn
            . ' AND tt_content.pid=' . $pageId
            . ' AND tt_content.uid NOT IN (' . $originalUidList . ')'
            . $this->getExcludeQueryPart(),
            '',
            'tt_content.sorting'
        );

        return $res;
    }
}
