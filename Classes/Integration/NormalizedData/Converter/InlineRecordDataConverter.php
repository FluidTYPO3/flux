<?php
namespace FluidTYPO3\Flux\Integration\NormalizedData\Converter;

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexFormConverter
 */
class InlineRecordDataConverter implements ConverterInterface
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var array
     */
    protected $record;

    /**
     * @var array
     */
    protected $original = [];

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
        $this->original = $GLOBALS['TCA'][$table]['columns'][$field];
    }

    /**
     * Modify the input FormEngine structure, returning
     * the modified array.
     *
     * @param array $structure
     * @return array
     */
    public function convertStructure(array $structure): array
    {
        $source = $this->resolveDataSourceDefinition($structure);
        if ($source === null || empty($source['sheets'])) {
            return $structure;
        }
        $this->synchroniseConfigurationRecords($source);
        $structure['processedTca']['columns'][$this->field]['config'] = $structure['processedTca']['columns'][$this->field . '_values']['config'];
        return $structure;
    }

    /**
     * Converts traditional FlexForm array data by merging in
     * the data coming from IRRE records.
     *
     * @param array $data
     * @return array|\ArrayAccess
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
     *
     * @param array $dataSource
     * @return void
     */
    protected function synchroniseConfigurationRecords(array $dataSource): void
    {
        foreach ($dataSource['sheets'] as $sheetName => $sheetConfiguration) {
            $sheetData = $this->fetchSheetRecord($sheetName);

            if (empty($sheetData)) {
                $label = $sheetConfiguration['ROOT']['TCEforms']['sheetTitle'];
                $sheetData = array(
                    'pid' => $this->record['pid'],
                    'name' => $sheetName,
                    'sheet_label' => empty($label) ? $sheetName : $label,
                    'source_table' => $this->table,
                    'source_field' => $this->field,
                    'source_uid' => $this->record['uid']
                );
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
        $segments = GeneralUtility::trimExplode('.', $path);
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
     *
     * @param array $structure
     * @return array
     */
    protected function resolveDataSourceDefinition(array $structure): array
    {
        $flexFormTools = GeneralUtility::makeInstance(FlexFormTools::class);
        $config = $structure['processedTca']['columns'][$this->field]['config'];
        $identifier = $flexFormTools->getDataStructureIdentifier($config, $this->table, $this->field, $this->record);
        return $flexFormTools->parseDataStructureByIdentifier($identifier);
    }

    /**
     * @param array $data
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function assignVariableByDottedPath(array &$data, string $name, $value): void
    {
        if (!strpos($name, '.')) {
            $data[$name] = $value;
        } else {
            $assignIn = &$data;
            $segments = explode('.', $name);
            $last = array_pop($segments);
            foreach ($segments as $segment) {
                if (!array_key_exists($segment, $assignIn)) {
                    $assignIn[$segment] = [];
                }
                $assignIn = &$assignIn[$segment];
            }
            $assignIn[$last] = $value;
        }
    }

    protected function updateFieldData(int $sheetUid, string $fieldName, array $fieldData): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('flux_field');
        $connection->update('flux_field', $fieldData, ['sheet' => $sheetUid, 'field_name' => $fieldName]);
    }

    protected function insertFieldData(array $fieldData): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('flux_field');
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->insert('flux_field')->values($fieldData)->execute();
        return (int) $connection->lastInsertId('flux_field');
    }

    protected function insertSheetData(array $sheetData): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('flux_sheet');
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->insert('flux_sheet')->values($sheetData)->execute();
        return (int) $connection->lastInsertId('flux_sheet');
    }

    protected function fetchFieldData(int $uid): array
    {
        $settings = [];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('flux_field');
        $result = $queryBuilder->select('uid', 'field_name', 'field_value')->from('flux_field')->where(
            $queryBuilder->expr()->eq('sheet', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
        )->execute()->fetchAllAssociative();
        foreach ($result as $fieldRecord) {
            $this->assignVariableByDottedPath($settings, $fieldRecord['field_name'], $fieldRecord['field_value']);
        }
        return $settings;
    }

    protected function fetchConfigurationRecords(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('flux_sheet');
        return $queryBuilder->select('*')->from('flux_sheet')->where(
            $queryBuilder->expr()->eq('source_table', $queryBuilder->createNamedParameter($this->table, \PDO::PARAM_STR)),
            $queryBuilder->expr()->eq('source_field', $queryBuilder->createNamedParameter($this->field, \PDO::PARAM_STR)),
        )->execute()->fetchAllAssociative();
    }

    protected function fetchSheetRecord(string $sheetName): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('flux_sheet');
        return $queryBuilder->select('uid', 'name')->from('flux_sheet')->where(
            $queryBuilder->expr()->eq('name', $queryBuilder->createNamedParameter($sheetName, \PDO::PARAM_STR))
        )->execute()->fetchAssociative() ?: null;
    }
}
