<?php
declare(strict_types=1);

(function () {
    if (!\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_FLEXFORM_TO_IRRE)) {
        return;
    }

    $GLOBALS['TCA']['flux_field'] = [
        'label' => 'Flux IRRE Field',
        'ctrl' => [
            'title' => 'Flux IRRE Field',
            'label' => '',
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
                'showitem' => 'field_value',
            ],
        ],
        'columns' => [
            'field_name' => [
                'label' => 'Field name',
                'config' => [
                    'type' => 'input'
                ],
            ],
            'field_label' => [
                'label' => 'Field label',
                'config' => [
                    'type' => 'input'
                ],
            ],
            'field_value' => [
                'label' => 'Field value',
                'config' => [
                    'type' => 'input'
                ],
            ],
        ],
    ];
})();
