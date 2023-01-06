<?php

$data = [
    'ctrl' => [
        'title' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types',
        'descriptionColumn' => 'description',
        'label' => 'title',
        'prependAtCopy' => '',
        'hideAtCopy' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,
        'origUid' => 't3_origuid',
        'editlock' => 'editlock',
        'useColumnsForDefaultValues' => 'type',
        'default_sortby' => 'ORDER BY sorting ASC',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'adminOnly' => true,
        'rootLevel' => true,
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:flux/Resources/Public/Icons/Plugin.png',
        'searchFields' => 'uid,title',
    ],
    'interface' => [
        'showRecordFieldList' => 'cruser_id,pid,hidden,starttime,endtime,fe_group,title,content_type,content_configuration,grid,template_file,template_source'
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0
            ],
        ],
        'cruser_id' => [
            'label' => 'cruser_id',
            'config' => [
                'type' => 'passthrough'
            ],
        ],
        'pid' => [
            'label' => 'pid',
            'config' => [
                'type' => 'passthrough'
            ],
        ],
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'sorting' => [
            'label' => 'sorting',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'editlock' => [
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:editlock',
            'config' => [
                'type' => 'check',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'title' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_formlabel',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'eval' => 'required',
            ],
        ],
        'content_type' => [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.content_type',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'eval' => 'required',
                'placeholder' => 'flux_newtype',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.description_formlabel',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'extension_identity' => [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.extension_identity',
            'config' => [
                'type' => 'input',
                'default' => 'FluidTYPO3.Flux',
            ],
        ],
        'icon' => [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.icon',
            'config' => [
                'type' => 'input',
            ],
        ],
        'content_configuration' => [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.content_configuration',
            'config' => [
                'type' => 'flex',
                'ds' => [
                    'default' => '<T3DataStructure><ROOT><el></el></ROOT></T3DataStructure>',
                ],
            ],
        ],
        'grid' => [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.grid_configuration',
            'config' => [
                'type' => 'flex',
                'ds' => [
                    'default' => '<T3DataStructure><ROOT><el></el></ROOT></T3DataStructure>',
                ],
            ],
        ],
        'template_file' => [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.template_file',
            'config' => [
                'type' => 'input',
            ],
        ],
        'template_source' => [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.template_source',
            'config' => [
                'type' => 'text',
                'cols' => 130,
                'rows' => 20
            ],
        ],
        'validation' => [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.validation',
            'config' => [
                'type' => 'user',
                'renderType' => 'fluxContentTypeValidator',
                'userFunc' => \FluidTYPO3\Flux\Content\ContentTypeValidator::class . '->validateContentTypeRecord'
            ],
        ],
        'template_dump' => [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.template_dump',
            'config' => [
                'type' => 'user',
                'renderType' => 'fluxTemplateSourceDumper',
                'userFunc' => \FluidTYPO3\Flux\Content\ContentTypeFluxTemplateDumper::class . '->dumpFluxTemplate'
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
            --div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.sheet.context,validation,title,content_type,extension_identity,icon,description,
            --div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.sheet.formFields,validation,content_configuration,
            --div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.sheet.grid,grid,
            --div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.sheet.template,validation,template_file,template_source,
            --div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:content_types.sheet.export,template_dump',
        ],
    ],
    'palettes' => []
];

if (!defined('TYPO3_version') || version_compare(TYPO3_version, '10.3', '>=')) {
    unset($data['interface']['showRecordFieldList']);
}

return $data;
