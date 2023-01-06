<?php
declare(strict_types=1);

(function () {
    if (!\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_FLEXFORM_TO_IRRE)) {
        return;
    }

    $GLOBALS['TCA']['flux_sheet'] = [
        'ctrl' => [
            'title' => 'Flux IRRE Sheet',
            'label' => 'sheet_label',
            'prependAtCopy' => '',
            'hideAtCopy' => false,
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'versioningWS' => false,
            'origUid' => 't3_origuid',
            'dividers2tabs' => true,
            'useColumnsForDefaultValues' => 'type',
            'languageField' => 'sys_language_uid',
            'transOrigPointerField' => 'l10n_parent',
            'transOrigDiffSourceField' => 'l10n_diffsource',
            'sortby' => '',
            'enablecolumns' => [],
            'iconfile' => 'EXT:flux/Resources/Public/Icons/Extension.svg',
        ],
        'types' => [
            '0' => [
                'showitem' => 'form_fields',
            ],
        ],
        'columns' => [
            'name' => [
                'label' => '',
                'config' => [
                    'type' => 'input',
                ],
            ],
            'sheet_label' => [
                'label' => '',
                'config' => [
                    'type' => 'input',
                    'renderType' => 'passthrough',
                ],
            ],
            'form_fields' => [
                'label' => '',
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'flux_field',
                    'foreign_field' => 'sheet',
                    'foreign_label' => 'field_label',
                    'size' => 1,
                    'minitems' => 0,
                    'maxitems' => 999,
                    'appearance' => [
                        'collapseAll' => false,
                        'expandSingle' => false,
                        'levelLinksPosition' => 'none',
                        'useSortable' => false,
                        'showPossibleLocalizationRecords' => false,
                        'showRemovedLocalizationRecords' => false,
                        'showAllLocalizationLink' => false,
                        'showSynchronizationLink' => false,
                        'enabledControls' => [
                            'info' => true,
                            'new' => false,
                            'create' => false,
                            'add' => false,
                            'localize' => false,
                            'delete' => true,
                        ],
                    ],
                ],
            ],
        ],
    ];

})();
