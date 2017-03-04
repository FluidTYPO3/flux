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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Repository for record localizations
 */
class LocalizationRepository extends \TYPO3\CMS\Backend\Domain\Repository\Localization\LocalizationRepository
{
    /**
     * @var ContentService
     */
    protected $contentService;

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->contentService = $objectManager->get(ContentService::class);
    }

    /**
     * Fetch the language from which the records of a colPos in a certain language were initially localized
     *
     * @param int $pageId
     * @param int $colPos
     * @param int $localizedLanguage
     *
     * @return array|false
     */
    public function fetchOriginLanguage($pageId, $colPos, $localizedLanguage)
    {
        if ($colPos <= ContentService::COLPOS_FLUXCONTENT) {
            parent::fetchOriginLanguage($pageId, $colPos, $localizedLanguage);
        }

        // here starts the flux related code
        list($fluxParent, $fluxColumn) = $this->contentService->getTargetAreaStoredInSession($colPos);
        $db = $this->getDatabaseConnection();

        $record = $db->exec_SELECTgetSingleRow(
            'tt_content_orig.sys_language_uid',
            'tt_content,tt_content AS tt_content_orig,sys_language',
            'tt_content.colPos = ' . ContentService::COLPOS_FLUXCONTENT
            . ' AND tt_content.tx_flux_parent = ' . (integer)$fluxParent
            . ' AND tt_content.tx_flux_column = \'' . $this->getDatabaseConnection()->quoteStr($fluxColumn,
                'tt_content') . '\''
            . ' AND tt_content.pid = ' . (int)$pageId
            . ' AND tt_content.sys_language_uid = ' . (int)$localizedLanguage
            . ' AND tt_content.' . CompatibilityRegistry::get(ContentService::LANGUAGE_SOURCE_FIELD) . ' = tt_content_orig.uid'
            . ' AND tt_content_orig.sys_language_uid=sys_language.uid'
            . $this->getExcludeQueryPart()
            . $this->getAllowedLanguagesForBackendUser(),
            'tt_content_orig.sys_language_uid'
        );

        return $record;
    }

    /**
     * @param int $pageId
     * @param int $colPos
     * @param int $languageId
     *
     * @return int
     */
    public function getLocalizedRecordCount($pageId, $colPos, $languageId)
    {
        if ($colPos <= ContentService::COLPOS_FLUXCONTENT) {
            return parent::getLocalizedRecordCount($pageId, $colPos, $languageId);
        }

        // here starts the flux related code
        list($fluxParent, $fluxColumn) = $this->contentService->getTargetAreaStoredInSession($colPos);
        $db = $this->getDatabaseConnection();

        $record = $db->exec_SELECTgetSingleRow('*', 'tt_content',
            'uid = ' . (integer)$fluxParent . $this->getExcludeQueryPart() . $this->getAllowedLanguagesForBackendUser());

        $rows = false;
        if ($record !== null) {

            $rows = (int)$db->exec_SELECTcountRows(
                'uid',
                'tt_content',
                'tt_content.sys_language_uid=' . (integer)$languageId
                . ' AND tt_content.colPos = ' . ContentService::COLPOS_FLUXCONTENT
                . ' AND tt_content.tx_flux_parent = ' . (integer)$fluxParent
                . ' AND tt_content.tx_flux_column = \'' . $this->getDatabaseConnection()->quoteStr($fluxColumn,
                    'tt_content') . '\''
                . ' AND tt_content.pid=' . (integer)$pageId
                . ' AND tt_content.' . CompatibilityRegistry::get(ContentService::LANGUAGE_SOURCE_FIELD) . ' <> 0'
                . $this->getExcludeQueryPart()
            );
        }
        return $rows;
    }

    /**
     * Fetch all available languages
     *
     * @param int $pageId
     * @param int $colPos
     * @param int $languageId
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function fetchAvailableLanguages($pageId, $colPos, $languageId)
    {
        if ($colPos <= ContentService::COLPOS_FLUXCONTENT) {
            return parent::fetchAvailableLanguages($pageId, $colPos, $languageId);
        }

        // here starts the flux related code
        list($fluxParent, $fluxColumn) = $this->contentService->getTargetAreaStoredInSession($colPos);
        $result = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'sys_language.uid',
            'tt_content,sys_language',
            'tt_content.sys_language_uid=sys_language.uid'
            . ' AND tt_content.colPos = ' . ContentService::COLPOS_FLUXCONTENT
            . ' AND tt_content.tx_flux_parent = ' . (integer)$fluxParent
            . ' AND tt_content.tx_flux_column = \'' . $this->getDatabaseConnection()->quoteStr($fluxColumn, 'tt_content') . '\''
            . ' AND tt_content.pid = ' . (int)$pageId
            . ' AND sys_language.uid <> ' . (int)$languageId
            . $this->getExcludeQueryPart()
            . $this->getAllowedLanguagesForBackendUser(),
            'sys_language.uid',
            'sys_language.title'
        );

        return $result;
    }

    /**
     * /**
     * Get records for copy process
     *
     * @param int $pageId
     * @param int $colPos
     * @param int $destLanguageId
     * @param int $languageId
     * @param string $fields
     *
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \InvalidArgumentException
     */
    public function getRecordsToCopyDatabaseResult($pageId, $colPos, $destLanguageId, $languageId, $fields = '*')
    {
        if ($colPos <= ContentService::COLPOS_FLUXCONTENT) {
            return parent::getRecordsToCopyDatabaseResult($pageId, $colPos, $destLanguageId, $languageId, $fields);
        }

        // here starts the flux related code
        $db = $this->getDatabaseConnection();

        list($fluxParent, $fluxColumn) = $this->contentService->getTargetAreaStoredInSession($colPos);

        $record = $db->exec_SELECTgetSingleRow('*','tt_content','uid = ' . (integer) $fluxParent);
            $fluxParentParent = $record[CompatibilityRegistry::get(ContentService::LANGUAGE_SOURCE_FIELD)];
        
        $originalUids = [];

        // Get original uid of existing elements triggered language / colpos
        $queryBuilder = $this->getQueryBuilderWithWorkspaceRestriction('tt_content');

        $originalUidsStatement = $queryBuilder
            ->select(CompatibilityRegistry::get(ContentService::LANGUAGE_SOURCE_FIELD))
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($destLanguageId, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tt_content.colPos',
                    $queryBuilder->createNamedParameter(ContentService::COLPOS_FLUXCONTENT, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tt_content.tx_flux_parent',
                    $queryBuilder->createNamedParameter($fluxParent, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tt_content.tx_flux_column',
                    $queryBuilder->createNamedParameter($fluxColumn, \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'tt_content.pid',
                    $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT)
                )
            )
            ->execute();

        while ($origUid = $originalUidsStatement->fetchColumn(0)) {
            $originalUids[] = (int)$origUid;
        }

        $queryBuilder->select(...GeneralUtility::trimExplode(',', $fields, true))
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'tt_content.sys_language_uid',
                    $queryBuilder->createNamedParameter($languageId, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tt_content.colPos',
                    $queryBuilder->createNamedParameter(ContentService::COLPOS_FLUXCONTENT, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tt_content.tx_flux_parent',
                    $queryBuilder->createNamedParameter($fluxParentParent, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tt_content.tx_flux_column',
                    $queryBuilder->createNamedParameter($fluxColumn, \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'tt_content.pid',
                    $queryBuilder->createNamedParameter($pageId, \PDO::PARAM_INT)
                )
            )
            ->orderBy('tt_content.sorting');

        if (!empty($originalUids)) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->notIn(
                        'tt_content.uid',
                        $queryBuilder->createNamedParameter($originalUids, Connection::PARAM_INT_ARRAY)
                    )
                );
        }

        return $queryBuilder->execute();
    }

    /**
     * Returns the database connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
