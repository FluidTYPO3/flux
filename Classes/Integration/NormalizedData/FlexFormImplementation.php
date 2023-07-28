<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\NormalizedData;

use FluidTYPO3\Flux\Integration\NormalizedData\Converter\ConverterInterface;
use FluidTYPO3\Flux\Integration\NormalizedData\Converter\InlineRecordDataConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlexFormImplementation extends AbstractImplementation implements ImplementationInterface
{
    protected static array $registrations = [];

    public static function registerForTableAndField(
        string $table,
        string $field,
        ?\Closure $additionalConditionChecker = null
    ): void {
        if (!isset(static::$registrations[$table])) {
            static::$registrations[$table] = [];
        }
        if (!isset(static::$registrations[$table][$field])) {
            static::$registrations[$table][$field] = [];
        }
        $registrations = &static::$registrations[$table][$field];
        $registrations[] = [
            static::class,
            $additionalConditionChecker
        ];
        $GLOBALS['TCA'][$table]['columns'][$field . '_values'] = [
            'label' => 'Configuration',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'flux_sheet',
                'foreign_field' => 'source_uid',
                'foreign_table_field' => 'source_table',
                'minitems' => 0,
                'maxitems' => 99999,
                'appearance' => [
                    'collapseAll' => 0,
                    'expandSingle' => 0,
                    'levelLinksPosition' => 'none',
                    'useSortable' => 0,
                    'showPossibleLocalizationRecords' => 0,
                    'showRemovedLocalizationRecords' => 0,
                    'showAllLocalizationLink' => 0,
                    'showSynchronizationLink' => 0,
                    'enabledControls' => [
                        'info' => false,
                        'new' => false,
                        'hide' => false,
                        'localize' => false,
                        'delete' => true,
                    ],
                ],
                'behaviour' => [
                    'enableCascadingDelete' => true,
                    'disableMovingChildrenWithParent' => false,
                ],
            ],
        ];
    }

    /**
     * Must return TRUE only if this implementation applies
     * to the table and field provided. Each implementation
     * can then allow configuring whether or not it should
     * apply to a given table/field in any way desired.
     */
    public function appliesToTableField(string $table, ?string $field): bool
    {
        if ($field === null) {
            return static::appliesToTable($table);
        }
        return isset(static::$registrations[$table][$field]);
    }

    /**
     * Must return TRUE only if this implementation applies
     * to the table provided. Each implementation can then
     * allow configuring whether or not it should apply to
     * a given table in any way desired.
     */
    public function appliesToTable(string $table): bool
    {
        return isset(static::$registrations[$table]);
    }

    /**
     * Returns a Converter that does the actual processing
     * required by this Implementation. Requires the table,
     * field and record as input parameters, allowing an
     * Implementation to return any number of different
     * Converters based on these identifying values.
     */
    public function getConverterForTableFieldAndRecord(string $table, string $field, array $record): ConverterInterface
    {
        /** @var InlineRecordDataConverter $converter */
        $converter = GeneralUtility::makeInstance(InlineRecordDataConverter::class, $table, $field, $record);
        return $converter;
    }
}
