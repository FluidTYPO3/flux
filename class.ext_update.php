<?php

use Doctrine\DBAL\Exception\InvalidFieldNameException;
use FluidTYPO3\Flux\Provider\Interfaces\FluidProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\BackendWorkspaceRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendWorkspaceRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class ext_update
 *
 * Performs update tasks for extension flux
 */
// @codingStandardsIgnoreStart
class ext_update
{

    /**
     * @return boolean
     */
    public function access()
    {
        return $this->validatePresenceOfLegacyFieldsInDatabaseSchema()
            || !empty($this->detectFluidPagesNotInstalledAndBackendLayoutFieldsReferenceFluidpages());
    }

    /**
     * @return string
     */
    public function main()
    {
        $url = GeneralUtility::getIndpEnv('REQUEST_URI');
        $performMigration = (bool) GeneralUtility::_GET('performMigration');
        $conflictingRecordsIgnored = (bool) GeneralUtility::_GET('ignoreConflictingRecords');

        // Data collection, filled by the loops below and then finally rendered by the special Fluid view.
        $templateFilesWithErrors = [];
        $templateFilesRequiringMigration = [];
        $childContentRequiringMigration = [];
        $migratedChildContent = [];
        $notMigratedChildContentUids = [];
        $columnPositionMigrationMap = [];

        // Absolute primary integrity check. If the database columns we use in this migration script do not exist, there
        // are two possible explanations: either the database schema was not yet updated to include the migration
        // tracking field - in which case this must be added first. Or the fields were removed from the schema, which we
        // have to assume means the user already performed this migration and chose to update the schema and finally
        // remove the fields that Flux used previously (which are removed from the schema in Flux 9.0).
        if (!$this->validatePresenceOfMigrationVersionFieldInDatabaseSchema()) {
            return '<h3 class="text-danger">Please update the database schema to add "tt_content.tx_flux_migrated_version"' .
                ' but DO NOT REMOVE ANY FIELDS PREFIXED WITH "tx_flux_*" YET!</h3>' . PHP_EOL .
                '<p>Return to this migration script when you have added the field and it will show additional output</p>' .
                '<p><a class="btn btn-primary" href="' . $url . '">Re-check</a></p>' . PHP_EOL;;
        }

        // Secondary vital sanity check: if we can select ANY content records whatsoever, with a colPos value above 99,
        // but not equal to the Flux column number 18181 - then this constitutes an incompatibility with the current
        // setup and the reason for those colPos values existing in the database, must be corrected first. Until that
        // is done we cannot allow automatic migration as it might move content to incorrect places. We will however
        // allow the integrity checks to continue - there just won't be any migration actually performed.
        if (!$performMigration && !$conflictingRecordsIgnored) {
            $possibleConflictingRecords = $this->loadPossiblyConflictingRecords();
            if (!empty($possibleConflictingRecords)) {
                $content = '<h3>The following records may conflict!</h3>';
                $content .= '<p>Records which have <code>colPos &gt; 99</code> but <code>colPos != 18181</code> may' .
                ' conflict with the way Flux handles colPos values. This problem should be corrected by:</p>' . PHP_EOL;
                $content .= '<ol>' . PHP_EOL;
                $content .= '<li>Changing the colPos value in the backend layout that causes this, to a value below 99</li>' . PHP_EOL;
                $content .= '<li>Updating the database to change the saved colPos values to your new value</li>' . PHP_EOL;
                $content .= '</ol>' . PHP_EOL;
                $content .= '<p>This must be performed manually since Flux cannot automatically change your backend layouts!</p>' . PHP_EOL;
                $content .= '<p><a class="btn btn-danger" href="' . $url . '&ignoreConflictingRecords=1">Ignore this check - at your own risk!</a></p>' . PHP_EOL;
                $content .= '<h4>Records in violation</h4>';
                $content .= '<p>The list includes hidden and deleted records, so you may want to clean the database first.</p>' . PHP_EOL;
                $content .= '<ul>' . PHP_EOL;
                foreach ($possibleConflictingRecords as $possibleConflictingRecord) {
                    $content .= '<li>tt_content:' . $possibleConflictingRecord['uid'] . ' on page ' . $possibleConflictingRecord['pid'] .
                        ' with colPos ' . $possibleConflictingRecord['colPos'] . '</li>' . PHP_EOL;
                }
                $content .= '</ul>' . PHP_EOL;
                return $content;
            }
        }

        if ($performMigration) {
            $migratedPageRecords = $this->detectFluidPagesNotInstalledAndBackendLayoutFieldsReferenceFluidpages();
            if (!empty($migratedPageRecords)) {
                $this->updateBackendLayoutSelection();
            }

            // Integrity check and data gathering loop. Verify that all records which have a Provider which returns a Grid,
            // are able to load the template that is associated with it. Failure to load the template gets analysed and the
            // reason gets reported; if the reason is "required argument colPos not used on flux:grid.column" the failure
            // gets reported specially as a required migration.
            $statement = $this->loadContentRecords();

            while ($row = $statement->fetch()) {
                // Check 1: If this record has Provider(s), check if it will return a Grid. If it cannot, and the failure is
                // that colPos is not provided for the ViewHelper, track this template as one that needs migration and track
                // the content record as a parent which requires migration.
                $parentUid = $row['uid'];
                $uidForColumnPositionCalculation = $row['l18n_parent'] ?: $row['uid'];
                foreach ($this->loadProvidersForRecord($row) as $provider) {
                    try {
                        if ($provider instanceof FluidProviderInterface) {
                            $templatePathAndFilename = $provider->getTemplatePathAndFilename($row);
                        } else {
                            $templatePathAndFilename = sprintf(
                                'Not a file-based grid. Manual migration of class "%s" may be necessary!',
                                get_class($provider)
                            );
                        }
                        $grid = $provider->getGrid($row);
                        foreach ($grid->getRows() as $gridRow) {
                            foreach ($gridRow->getColumns() as $gridColumn) {
                                $name = $gridColumn->getName();
                                $columnPosition = $gridColumn->getColumnPosition();
                                $columnPositionMigrationMap[$parentUid][$name] = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                                    $uidForColumnPositionCalculation,
                                    $columnPosition
                                );
                            }
                        }
                        unset($grid);
                    } catch (\TYPO3Fluid\Fluid\Core\Parser\Exception $exception) {
                        if (strpos($exception->getMessage(), 'Required argument "colPos" was not supplied.') !== false) {
                            $templateFilesRequiringMigration[$templatePathAndFilename] = $exception->getMessage();
                        } else {
                            $templateFilesWithErrors[$templatePathAndFilename] = $exception->getMessage();
                        }
                    } catch (\Exception $exception) {
                        $templateFilesWithErrors[$templatePathAndFilename] = $exception->getMessage();
                    }
                }

                // Check 2: If this content record has the legacy Flux colPos value 18181, it needs adjustments. We collect
                // the UID values in a minimal array which we can process in a separate loop. We add a second check if the
                // record was already migrated, to protect the edge case of parent=181 and column=81 from being processed.
                if ((int)$row['colPos'] === 18181 && empty($row['tx_flux_migrated_version'])) {
                    $childContentRequiringMigration[] = [
                        'uid' => $row['uid'],
                        'pid' => $row['pid'],
                        'colPos' => $row['colPos'],
                        'tx_flux_column' => $row['tx_flux_column'],
                        'tx_flux_parent' => $row['tx_flux_parent']
                    ];
                }
                unset($row);
            }

            foreach ($childContentRequiringMigration as $childContent) {
                $newColumnPosition = $columnPositionMigrationMap[$childContent['tx_flux_parent']][$childContent['tx_flux_column']] ?? null;
                if ($newColumnPosition === null) {
                    $notMigratedChildContentUids[] = $childContent['uid'];
                } else {
                    $migratedChildContent[] = $this->fixColumnPositionInRecord(
                        $childContent,
                        $newColumnPosition
                    );
                }
            }
        }

        $fluidTemplate = $this->getFluidTemplateSource();
        $view = GeneralUtility::makeInstance(ObjectManager::class)->get(TemplateView::class);
        $view->getTemplatePaths()->setTemplateSource($fluidTemplate);

        $view->assignMultiple(
            [
                'url' => $url,
                'performMigration' => $performMigration,
                'conflictingRecordsIgnored' => $conflictingRecordsIgnored,
                'templatesWithErrors' => $this->removeBasePathFromKeys($templateFilesWithErrors),
                'templateFilesRequiringMigration' => $this->removeBasePathFromKeys($templateFilesRequiringMigration),
                'migratedChildContent' => $migratedChildContent,
                'migratedPageRecords' => $migratedPageRecords,
                'notMigratedChildContentUids' => $notMigratedChildContentUids,
            ]
        );

        return $view->render();
    }

    protected function detectFluidPagesNotInstalledAndBackendLayoutFieldsReferenceFluidpages(): array
    {
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluidpages')) {
            return [];
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select('uid')->from('pages')->where(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('backend_layout', $queryBuilder->createNamedParameter('fluidpages__fluidpages', \PDO::PARAM_STR)),
                $queryBuilder->expr()->eq('backend_layout_next_level', $queryBuilder->createNamedParameter('fluidpages__fluidpages', \PDO::PARAM_STR)),
                $queryBuilder->expr()->eq('backend_layout', $queryBuilder->createNamedParameter('fluidpages__grid', \PDO::PARAM_STR)),
                $queryBuilder->expr()->eq('backend_layout_next_level', $queryBuilder->createNamedParameter('fluidpages__grid', \PDO::PARAM_STR))
            )
        );
        return $queryBuilder->execute()->fetchAll();
    }

    protected function updateBackendLayoutSelection(): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->update('pages');
        $q1 = clone $queryBuilder;
        $q2 = clone $queryBuilder;
        $q1->set('backend_layout', 'flux__grid')
            ->where(
                $q1->expr()->orX(
                    $q1->expr()->eq('backend_layout', $q1->createNamedParameter('fluidpages__fluidpages', \PDO::PARAM_STR)),
                    $q1->expr()->eq('backend_layout_next_level', $q1->createNamedParameter('fluidpages__grid', \PDO::PARAM_STR))
                )
            )->execute();
        $q2->set('backend_layout_next_level', 'flux__grid')
            ->where(
                $q2->expr()->orX(
                    $q2->expr()->eq('backend_layout_next_level', $q2->createNamedParameter('fluidpages__fluidpages', \PDO::PARAM_STR)),
                    $q2->expr()->eq('backend_layout_next_level', $q2->createNamedParameter('fluidpages__grid', \PDO::PARAM_STR))
                )
            )->execute();
    }

    protected function removeBasePathFromKeys(array $values)
    {
        $basePath = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT');
        $basePathLength = strlen($basePath) + 1;
        $converted = [];
        foreach ($values as $key => $value) {
            $trimmed = substr($key, $basePathLength);
            $converted[$trimmed] = $value;
        }
        return $converted;
    }

    protected function fixColumnPositionInRecord(array $record, int $newColumnPosition)
    {
        $recordUid = $record['uid'];
        unset($record['uid']);
        $record['colPos'] = $newColumnPosition;
        $record['tx_flux_migrated_version'] = '9.0';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('tt_content')->where($queryBuilder->expr()->eq('uid', $recordUid));
        foreach ($record as $key => $value) {
            $queryBuilder->set($key, $value, true);
        }
        $queryBuilder->execute();
        return ['uid' => $recordUid] + $record;
    }

    protected function validatePresenceOfMigrationVersionFieldInDatabaseSchema(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select('tx_flux_migrated_version')->from('tt_content')->setMaxResults(1);
        try {
            $queryBuilder->execute()->fetchAll();
        } catch (InvalidFieldNameException $exception) {
            return false;
        }
        return true;
    }

    protected function validatePresenceOfLegacyFieldsInDatabaseSchema(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select('tx_flux_parent', 'tx_flux_column')->from('tt_content')->setMaxResults(1);
        try {
            $queryBuilder->execute()->fetchAll();
        } catch (InvalidFieldNameException $exception) {
            return false;
        }
        return true;
    }

    /**
     * @param array $record
     * @return GridProviderInterface[]
     */
    protected function loadProvidersForRecord(array $record): array
    {
        return GeneralUtility::makeInstance(ProviderResolver::class)->resolveConfigurationProviders(
            'tt_content',
            null,
            $record,
            null,
            GridProviderInterface::class
        );
    }

    protected function loadPossiblyConflictingRecords(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select('uid', 'pid', 'colPos')
            ->from('tt_content')
            ->andWhere(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->neq('colPos', 18181),
                $queryBuilder->expr()->gt('colPos', 99),
                $queryBuilder->expr()->isNull('tx_flux_migrated_version')
            );
        return $queryBuilder->execute()->fetchAll();
    }

    protected function loadContentRecords(): \Doctrine\DBAL\Driver\Statement
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $queryBuilder->getRestrictions()->removeByType(StartTimeRestriction::class);
        $queryBuilder->getRestrictions()->removeByType(EndTimeRestriction::class);
        $queryBuilder->getRestrictions()->removeByType(BackendWorkspaceRestriction::class);
        $queryBuilder->getRestrictions()->removeByType(FrontendGroupRestriction::class);
        $queryBuilder->getRestrictions()->removeByType(FrontendWorkspaceRestriction::class);
        $queryBuilder->select('*')->from('tt_content');
        return $queryBuilder->execute();
    }

    protected function getFluidTemplateSource(): string
    {
        return <<< FLUID
<f:if condition="{conflictingRecordsIgnored}">
    <h3 class="text-warning">Possibly conflicting records are ignored</h3>
    <p>
        You chose to ignore records which had a colPos value above 99 - these records may appear as child content
        elements under parents where they are not expected. Flux (almost) reserves such colPos values: they are possible
        to use, as long as they do not conflict with existing content record UIDs plus column position number used in
        templates. For example, a colPos value of <code>1002</code> is valid if content record with UID <code>10</code>
        does not have a grid column with a column position number of <code>2</code> (since virtual colPos value is
        calculated by taking parent record UID, multiplying it by 100, and adding the colPos value from templates). 
    </p>
    <p>
        You are advised to correct these - and you can return to this script at any time to repeat the check.
    </p>
    <p class="text-danger">
        Note however that once you begin creating content in the site, this test will begin to report false positives
        because it only excludes records which were migrated by this script - not freshly created ones. Creating new
        child content will result in that child content record showing up here.
    </p>
</f:if>

<f:if condition="!{performMigration}">
    <h1>Migration process</h1>
    <f:if condition="!{performMigration}">
        <p><a class="btn btn-success" href="{url}&performMigration=1">Perform migration</a></p>
        <p><em>Performing the migration can take several minutes</em></p>
    </f:if>
</f:if>

<f:if condition="{performMigration}">
    <h3>Migration results</h3>
    <f:if condition="{migratedPageRecords -> f:count()}">
        <f:then>
            <ul>
                <f:for each="{migratedPageRecords}" as="migratedRecord">
                    <li>pages:{migratedRecord.uid} has new backend layout selection "flux__grid" where either "fluidpages__grid" or "fluidpages__fluidpages" was selected.</li>
                </f:for>
            </ul>
        </f:then>
        <f:else>
            <p>No content records were migrated</p>
        </f:else>    
    </f:if>
    <f:if condition="{migratedChildContent -> f:count()}">
        <f:then>
            <ul>
                <f:for each="{migratedChildContent}" as="migratedRecord">
                    <li>tt_content:{migratedRecord.uid} on page {migratedRecord.pid} has new colPos {migratedRecord.colPos}</li>
                </f:for>
            </ul>
        </f:then>
        <f:else>
            <p>No content records were migrated</p>
        </f:else>
    </f:if>
    
    <f:if condition="{notMigratedChildContent -> f:count()}">
        <p>The following records could not be migrated. You may need to take care of these manually.</p>
        <ul>
            <f:for each="{migratedChildContent}" as="migratedRecord">
                <li>tt_content:{migratedRecord.uid} on page {migratedRecord.pid} has new colPos {migratedRecord.colPos}</li>
            </f:for>
        </ul>
    </f:if>

    <f:if condition="{templatesWithErrors -> f:count()}">
        <h3 class="text-danger">Template files have errors</h3>
        <p>
            The following template files (seem to) contain errors and will prevent migrating child content, if the template
            defines a grid. Because of these errors, any elements that use such a template, will be impossible to migrate
            until the error is fixed.
        </p>
        <p>
            <em>
                Note that if the error is caused by the template depending on variables that are for example defined in
                TypoScript, the template may not be possible to process in this migration wizard and you will have to
                manually migrate the content elements within grids of these templates. It is however recommended to make
                templates work without dependency on page context variables and instead use only global setup, if any.
            </em>
        </p>
        <p class="text-primary">Errors here will not prevent you from running the migration!</p>
        <f:render section="TemplateList" arguments="{templateFiles: templatesWithErrors}" />
        <p><a class="btn btn-warning" href="{url}">I have fixed the templates - run migration again</a></p>
    </f:if>
    
    <f:if condition="{templateFilesRequiringMigration -> f:count()}">
        <h3 class="text-danger">Template files require migration</h3>
        <p>The following template files must be migrated to add "colPos" for each flux:grid.column</p>
        <f:render section="TemplateList" arguments="{templateFiles: templateFilesRequiringMigration}" />
        <p class="text-danger">Errors here must be solved or you cannot continue!</p>
        <p><a class="btn btn-warning" href="{url}">I have fixed the templates - run migration again</a></p>
    </f:if>
</f:if>

<f:section name="TemplateList">
    <ol>
        <f:for each="{templateFiles}" as="message" key="template">
            <li>
                <div class="badge badge-warning">{template}</div>
                <div>{message}</div>
            </li>
        </f:for>
    </ol>
</f:section>
FLUID;
    }
}
