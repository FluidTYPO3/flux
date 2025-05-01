<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\NormalizedData\Converter;

use FluidTYPO3\Flux\Proxy\FlexFormToolsProxy;
use FluidTYPO3\Flux\Utility\DoctrineQueryProxy;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class InlineRecordDataConverter implements ConverterInterface
{
    protected string $table;
    protected string $field;
    protected array $record;
    protected array $original = [];

    /**
     * Constructor to receive all required parameters.
     *
     * @param string $table
     * @param string $field
     * @param array $record
     */
    public function __construct(string $table, string $field, array $record)
    {
        $this->table = $table;
        $this->field = $field;
        $this->record = $record;
        $this->original = $GLOBALS['TCA'][$table]['columns'][$field] ?? [];
    }

    /**
     * Modify the input FormEngine structure, returning
     * the modified array.
     */
    public function convertStructure(array $structure): array
    {
        $source = $this->resolveDataSourceDefinition($structure);
        if ($source === null || empty($source['sheets'])) {
            return $structure;
        }
        $this->synchroniseConfigurationRecords($source);
        $columns = &$structure['processedTca']['columns'];
        $columnms[$this->field]['config'] = $columns[$this->field . '_values']['config'];
        return $structure;
    }

    /**
     * Converts traditional FlexForm array data by merging in
     * the data coming from IRRE records.
     */
    public function convertData(array $data): array
    {
        $sheets = $this->fetchConfigurationRecords();
        foreach ($sheets as $sheetData) {
            $data = array_merge($data, $this->fetchFieldData($sheetData['uid']));
        }
        return $data;
    }

    /**
     * Synchronises the IRRE-attached relation records for
     * the record in question, if record has been saved and
     * now has a UID value. Uses the form structure defined
     * in $dataSource (and creates records with default
     * values those were if specified in data source).
     */
    protected function synchroniseConfigurationRecords(array $dataSource): void
    {
        foreach ($dataSource['sheets'] as $sheetName => $sheetConfiguration) {
            $sheetData = $this->fetchSheetRecord($sheetName);

            if (empty($sheetData)) {
                $label = $sheetConfiguration['ROOT']['TCEforms']['sheetTitle'];
                $sheetData = [
                    'pid' => $this->record['pid'],
                    'name' => $sheetName,
                    'sheet_label' => empty($label) ? $sheetName : $label,
                    'source_table' => $this->table,
                    'source_field' => $this->field,
                    'source_uid' => $this->record['uid']
                ];
                $sheetData['uid'] = $this->insertSheetData($sheetData);
            }
            $sheetUid = (int) $sheetData['uid'];
            $currentSettings = $this->fetchFieldData($sheetUid);

            foreach ($sheetConfiguration['ROOT']['el'] as $fieldName => $fieldConfiguration) {
                $fieldConfiguration = $fieldConfiguration['TCEforms'] ?? $fieldConfiguration;
                if (($fieldConfiguration['type'] ?? null) === 'array') {
                    // Field is set of objects. Currently unsupported - so we skip it.
                    continue;
                }
                $type = $fieldConfiguration['config']['type'];
                if ($type === 'select' && isset($fieldConfiguration['config']['renderType'])) {
                    $type = $fieldConfiguration['config']['renderType'];
                }
                if (!$this->assertArrayHasKey($currentSettings, $fieldName)) {
                    $fieldData = [
                        'pid' => $this->record['pid'],
                        'sheet' => $sheetUid,
                        'field_name' => $fieldName,
                        'field_label' => $fieldConfiguration['label'],
                        'field_type' => $type,
                        'field_value' => $fieldConfiguration['config']['default'],
                        'field_options' => json_encode($fieldConfiguration['config'], JSON_HEX_AMP | JSON_HEX_TAG)
                    ];
                    $this->insertFieldData($fieldData);
                } else {
                    $fieldData = [
                        'pid' => $this->record['pid'],
                        'field_type' => $type,
                        'field_label' => $fieldConfiguration['label'],
                        'field_options' => json_encode($fieldConfiguration['config'], JSON_HEX_AMP | JSON_HEX_TAG)
                    ];
                    $this->updateFieldData($sheetUid, $fieldName, $fieldData);
                }
            }
        }
    }

    protected function assertArrayHasKey(array $array, string $path): bool
    {
        if (empty($array)) {
            return false;
        }
        $segments = GeneralUtility::trimExplode('.', $path, true);
        if (count($segments) === 0) {
            return false;
        }
        $lastSegment = array_pop($segments);
        foreach ($segments as $segment) {
            $array = $array[$segment];
        }
        return is_array($array) && array_key_exists($lastSegment, $array);
    }

    /**
     * Resolves a data source definition (TCEforms array)
     * based on the properties of this converter instance
     * and the *original* TCA of the source record field.
     */
    protected function resolveDataSourceDefinition(array $structure): ?array
    {
        /** @var FlexFormToolsProxy $flexFormTools */
        $flexFormTools = GeneralUtility::makeInstance(FlexFormToolsProxy::class);
        $config = $structure['processedTca']['columns'][$this->field]['config'];
        try {
            $identifier = $flexFormTools->getDataStructureIdentifier(
                $config,
                $this->table,
                $this->field,
                $this->record
            );
            return $flexFormTools->parseDataStructureByIdentifier($identifier);
        } catch (\RuntimeException $exception) {
        }
        return null;
    }

    /**
     * @param mixed $value
     */
    protected function assignVariableByDottedPath(array $data, string $name, $value): array
    {
        $assignIn = &$data;
        $segments = GeneralUtility::trimExplode('.', $name, true);
        $last = array_pop($segments);
        foreach ($segments as $segment) {
            if (!array_key_exists($segment, $assignIn)) {
                $assignIn[$segment] = [];
            }
            $assignIn = &$assignIn[$segment];
        }
        $assignIn[$last] = $value;
        return $data;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function updateFieldData(int $sheetUid, string $fieldName, array $fieldData): void
    {
        $connection = $this->createConnectionForTable('flux_field');
        $connection->update('flux_field', $fieldData, ['sheet' => $sheetUid, 'field_name' => $fieldName]);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function insertFieldData(array $fieldData): int
    {
        $connection = $this->createConnectionForTable('flux_field');
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->insert('flux_field')->values($fieldData);
        DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder);
        return (int) $connection->lastInsertId('flux_field');
    }

    /**
     * @codeCoverageIgnore
     */
    protected function insertSheetData(array $sheetData): int
    {
        $connection = $this->createConnectionForTable('flux_sheet');
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->insert('flux_sheet')->values($sheetData);
        DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder);
        return (int) $connection->lastInsertId('flux_sheet');
    }

    /**
     * @codeCoverageIgnore
     */
    protected function fetchFieldData(int $uid): array
    {
        $settings = [];
        $queryBuilder = $this->createQueryBuilderForTable('flux_field');
        $queryBuilder->select('uid', 'field_name', 'field_value')->from('flux_field')->where(
            $queryBuilder->expr()->eq('sheet', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT))
        );
        /** @var array[] $result */
        $result = DoctrineQueryProxy::fetchAllAssociative(
            DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder)
        );
        foreach ($result as $fieldRecord) {
            $settings = $this->assignVariableByDottedPath(
                $settings,
                $fieldRecord['field_name'],
                $fieldRecord['field_value']
            );
        }
        return $settings;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function fetchConfigurationRecords(): array
    {
        $queryBuilder = $this->createQueryBuilderForTable('flux_sheet');
        $queryBuilder->select('*')->from('flux_sheet')->where(
            $queryBuilder->expr()->eq(
                'source_table',
                $queryBuilder->createNamedParameter($this->table, Connection::PARAM_STR)
            ),
            $queryBuilder->expr()->eq(
                'source_field',
                $queryBuilder->createNamedParameter($this->field, Connection::PARAM_STR)
            ),
        );
        return DoctrineQueryProxy::fetchAllAssociative(DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder));
    }

    /**
     * @codeCoverageIgnore
     */
    protected function fetchSheetRecord(string $sheetName): ?array
    {
        $queryBuilder = $this->createQueryBuilderForTable('flux_sheet');
        $queryBuilder->select('uid', 'name')->from('flux_sheet')->where(
            $queryBuilder->expr()->eq('name', $queryBuilder->createNamedParameter($sheetName, Connection::PARAM_STR))
        );
        return DoctrineQueryProxy::fetchAssociative(DoctrineQueryProxy::executeQueryOnQueryBuilder($queryBuilder));
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createConnectionForTable(string $table): Connection
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getConnectionForTable($table);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createQueryBuilderForTable(string $table): QueryBuilder
    {
        return $this->createConnectionForTable($table)->createQueryBuilder();
    }
}
