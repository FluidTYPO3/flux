<?php
namespace FluidTYPO3\Flux\Updates;

use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Registry;
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
 * @codeCoverageIgnore
 */
class MigrateColPosWizard implements
    \TYPO3\CMS\Install\Updates\UpgradeWizardInterface,
    \TYPO3\CMS\Install\Updates\ChattyInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Returns the title attribute
     *
     * @return string The title of this update wizard
     */
    public function getTitle(): string
    {
        return 'Flux: Fix content "sorting" values';
    }

    /**
     * Returns the identifier of this class
     *
     * @return string The identifier of this update wizard
     */
    public function getIdentifier(): string
    {
        return static::class;
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * Returns an array of class names of Prerequisite classes
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }

    /**
     * Is an update necessary?
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool
     */
    public function updateNecessary(): bool
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
            ->fetchOne();

        $this->output->write($numRows . ' content elements need to be fixed');

        return $numRows > 0;
    }

    /**
     * Execute the update
     * Called when a wizard reports that an update is necessary
     *
     * @return bool
     */
    public function executeUpdate(): bool
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
        /** @var DeletedRestriction $deletedRestriction */
        $deletedRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        while ($contentRow = $statement->fetch()) {
            list($min, $max) = ColumnNumberUtility::calculateMinimumAndMaximumColumnNumberWithinParent(
                $contentRow['uid']
            );

            $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add($deletedRestriction);
            $maxChildSorting = $queryBuilder
                ->selectLiteral('MAX(sorting)')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->gte('colPos', $queryBuilder->createNamedParameter($min, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->lte('colPos', $queryBuilder->createNamedParameter($max, \PDO::PARAM_INT)),
                )
                ->execute()
                ->fetchOne();

            $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add($deletedRestriction);
            $selfAndFollowingSiblings = $queryBuilder
                ->select('uid', 'sorting', 'colPos')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq(
                        'pid',
                        $queryBuilder->createNamedParameter($contentRow['pid'], \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'colPos',
                        $queryBuilder->createNamedParameter($contentRow['colPos'], \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->gte(
                        'sorting',
                        $queryBuilder->createNamedParameter($contentRow['sorting'], \PDO::PARAM_INT)
                    ),
                )
                ->orderBy('sorting', 'ASC')
                ->execute()
                ->fetchAllAssociative();

            $changed = false;
            $newSorting = $maxChildSorting;
            foreach ($selfAndFollowingSiblings as $siblingRow) {
                $newSorting++;
                if ($siblingRow['sorting'] >= $newSorting) {
                    //this sibling and all later ones are fine already
                    break;
                }

                $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
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

        $this->output->write($modified . " content element records modified\n");

        if ($tryAgain) {
            return $this->executeUpdate();
        }

        $this->markWizardAsDone();
        return true;
    }

    /**
     * Setter injection for output into upgrade wizards
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Marks some wizard as being "seen" so that it not shown again.
     *
     * Writes the info in LocalConfiguration.php
     */
    protected function markWizardAsDone()
    {
        GeneralUtility::makeInstance(Registry::class)->set('installUpdate', static::class, 1);
    }
}
