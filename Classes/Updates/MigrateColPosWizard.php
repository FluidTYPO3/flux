<?php
namespace FluidTYPO3\Flux\Updates;

use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Fix the "sorting" value of content elements.
 *
 * Copying pages will fail to adjust the colPos values when the "sorting" value
 * of child elements is larger than that of its parent record.
 * This leads to "lost" content records that are visible in the list view,
 * but not in the web view in the TYPO3 backend.
 *
 * This problem happens at least when migrating from TYPO3 v7 to v8 while
 * upgrading to flux 9.5.0.
 * Old content records exhibit this problem, while newly created content
 * is fine - TYPO3 takes care of modifying the "sorting" values correctly.
 *
 * This wizard fixes the problematic sorting values by finding the largest
 * child sorting value and adjusting the parent's "sorting" accordingly,
 * as well as all following content element siblings.
 *
 * @author Christian Weiske <weiske@mogic.com>
 */
class MigrateColPosWizard extends \TYPO3\CMS\Install\Updates\AbstractUpdate
{
    /**
     * @var string
     */
    protected $title = 'Flux: Fix content "sorting" values';

    /**
     * Checks whether updates are required.
     *
     * @param string $description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $numRows = $queryBuilder
            ->count('*')
            ->from('tt_content', 'parent')
            ->from('tt_content', 'child')
            ->where(
                $queryBuilder->expr()->eq('parent.uid', 'FLOOR(child.colPos / 100)'),
                $queryBuilder->expr()->gte('child.sorting', 'parent.sorting')
            )
            ->execute()
            ->fetchColumn(0);

        $description = $numRows . ' content elements need to be fixed';

        return $numRows > 0;
    }

   /**
    * Performs the required update.
    *
    * @param array $dbQueries Queries done in this update
    * @param string $customMessage Custom message to be displayed after the update process finished
    * @return bool Whether everything went smoothly or not
    */
    public function performUpdate(array &$dbQueries, &$customMessage)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        //distinct is much faster than using groupBy()
        $statement = $queryBuilder
            ->selectLiteral('DISTINCT parent.uid', 'parent.pid', 'parent.sorting', 'parent.colPos')
            ->from('tt_content', 'parent')
            ->from('tt_content', 'child')
            ->where(
                $queryBuilder->expr()->eq('parent.uid', 'FLOOR(child.colPos / 100)'),
                $queryBuilder->expr()->gte('child.sorting', 'parent.sorting')
            )
            ->execute();

        $modified = 0;
        $tryAgain = false;
        while ($contentRow = $statement->fetch()) {
            list($min, $max) = ColumnNumberUtility::calculateMinimumAndMaximumColumnNumberWithinParent($contentRow['uid']);

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $maxChildSorting = $queryBuilder
                ->selectLiteral('MAX(sorting)')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->gte('colPos', $queryBuilder->createNamedParameter($min, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->lte('colPos', $queryBuilder->createNamedParameter($max, \PDO::PARAM_INT)),
                )
                ->execute()
                ->fetchColumn(0);

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $selfAndFollowingSiblings = $queryBuilder
                ->select('uid', 'sorting', 'colPos')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($contentRow['pid'], \PDO::PARAM_INT)),
                    $queryBuilder->expr()->eq('colPos', $queryBuilder->createNamedParameter($contentRow['colPos'], \PDO::PARAM_INT)),
                    $queryBuilder->expr()->gte('sorting', $queryBuilder->createNamedParameter($contentRow['sorting'], \PDO::PARAM_INT)),
                )
                ->orderBy('sorting', 'ASC')
                ->execute()
                ->fetchAll();

            $changed = false;
            $newSorting = $maxChildSorting;
            foreach ($selfAndFollowingSiblings as $siblingRow) {
                $newSorting++;
                if ($siblingRow['sorting'] >= $newSorting) {
                    //this sibling and all later ones are fine already
                    break;
                }

                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
                $res = $queryBuilder
                    ->update('tt_content')
                    ->where($queryBuilder->expr()->eq('uid', $siblingRow['uid'], \PDO::PARAM_INT))
                    ->set('sorting', $newSorting)
                    ->execute();

                $modified++;
                $changed = true;
            }

            if ($changed && $contentRow['colPos'] > 99) {
                //we modified the sorting of a content element that itself is
                // a child of another content element. that one might need
                // to be fixed, too.
                $tryAgain = true;
            }
        }

        $customMessage .= $modified . " content element records modified\n";

        if ($tryAgain) {
            return $this->performUpdate($dbQueries, $customMessage);
        }

        $this->markWizardAsDone();
        return true;
    }
}
