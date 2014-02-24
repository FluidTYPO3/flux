<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

$TCA['tt_content']['columns']['colPos']['config']['items'][] = array(
	'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_container',
	\FluidTYPO3\Flux\Service\ContentService::COLPOS_FLUXCONTENT
);


\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('tt_content');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', array(
		'attributes' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.attributes',
			'config' => array (
				'type' => 'inline',
				'foreign_table' => 'tx_flux_domain_model_attribute',
				'foreign_match_fields' => array(
					'for_field' => 'pi_flexform'
				),
				'appearance' => array(
					'collapseAll' => TRUE,
					'useSortable' => FALSE,
					'levelLinksPosition' => 'none',
					'enabledControls' => array(
						'dragdrop' => FALSE,
						'sort' => FALSE,
						'new' => FALSE,
					)
				)
			)
		),
		'tx_flux_column' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_column',
			'displayCond' => 'FIELD:tx_flux_parent:>:0',
			'config' => array (
				'type' => 'select',
				'default' => '',
				'itemsProcFunc' => 'FluidTYPO3\Flux\Backend\AreaListItemsProcessor->itemsProcFunc'
			)
		),
		'tx_flux_parent' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_parent',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'tt_content',
				'foreign_table_where' => "tt_content.pid = '###CURRENT_PID###'",
				'default' => 0,
				'size' => 1,
				'maxitems' => 1,
			)
		),
		'tx_flux_children' => array (
			'exclude' => 0,
			'config' => array (
				'type' => 'inline',
				'foreign_table' => 'tt_content',
				'foreign_field' => 'tx_flux_parent',
				'foreign_sortby' => 'sorting',
				'appearance' => array(
					'collapseAll' => TRUE,
					'enabledControls' => array(
						'new' => FALSE,
						'hide' => TRUE
					)
				)
			)
		),
	)
);
$GLOBALS['TCA']['tx_flux_domain_model_attribute'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tx_flux_domain_model_attribute.title',
		'label' => 'name',
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/Attribute.gif',
		'searchFields' => 'name'
	),
	'interface' => array(
		'showRecordFieldList' => 'pid,sys_language_uid,l10n_parent,l10n_diffsource,name,attribute_values'
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_ttc.xml:sys_language_uid_formlabel',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_flux_domain_model_attribute',
				'foreign_table_where' => 'AND tx_flux_domain_model_attribute.pid=###CURRENT_PID### AND tx_flux_domain_model_attribute.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough'
			)
		),
		'pid' => array(
			'label' => 'pid',
			'config' => array(
				'type' => 'passthrough'
			)
		),
	)
);
$GLOBALS['TCA']['tx_flux_domain_model_value'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tx_flux_domain_model_value.title',
		'label' => 'value',
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'default_sortby' => 'ORDER BY sorting DESC',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/Attribute.gif',
		'searchFields' => 'value'
	),
	'interface' => array(
		'showRecordFieldList' => 'pid,sys_language_uid,l10n_parent,l10n_diffsource,value'
	),
	'columns' => $GLOBALS['TCA']['tx_flux_domain_model_attribute']['columns'],
	'types' => array(
		'1' => array(
			'showitem' => 'l10n_parent,l10n_diffsource'
		),
	),
);

$GLOBALS['TCA']['tx_flux_domain_model_attribute']['columns']['name'] = array(
	'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tx_flux_domain_model_attribute.name',
	'exclude' => 0,
	'config' => array(
		'type' => 'input',
	)
);

$GLOBALS['TCA']['tx_flux_domain_model_attribute']['columns']['attribute_values'] = array(
	'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tx_flux_domain_model_attribute.attribute_values',
	'exclude' => 0,
	'config' => array(
		'type' => 'inline',
		'foreign_table' => 'tx_flux_domain_model_value',
		'foreign_field' => 'attribute',
		'foreign_label' => 'value',
		'appearance' => array(
			'collapseAll' => TRUE,
			'useSortable' => FALSE,
			'levelLinksPosition' => 'none',
			'enabledControls' => array(
				'dragdrop' => FALSE,
				'sort' => FALSE,
				'new' => FALSE,
			)
		)
	)
);

$GLOBALS['TCA']['tx_flux_domain_model_attribute']['types']['1']['showitem'] .= ',name,attribute_values';
$GLOBALS['TCA']['tx_flux_domain_model_value']['types']['1']['showitem'] .= ',value';
$GLOBALS['TCA']['tx_flux_domain_model_value']['ctrl']['sortby'] = 'sorting';
$GLOBALS['TCA']['tx_flux_domain_model_value']['columns']['l10n_parent']['config']['foreign_table_where'] = 'AND tx_flux_domain_model_value.pid=###CURRENT_PID### AND tx_flux_domain_model_value.sys_language_uid IN (-1,0)';
$GLOBALS['TCA']['tx_flux_domain_model_value']['columns']['l10n_parent']['config']['foreign_table'] = 'tx_flux_domain_model_value';
$GLOBALS['TCA']['tx_flux_domain_model_value']['columns']['value'] = array(
	'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tx_flux_domain_model_value.value',
	'exclude' => 0,
	'config' => array(
		'type' => 'none',
	)
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', ',--div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tabs.relation,tx_flux_column,tx_flux_children;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_children,attributes,,');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_flux_domain_model_attribute');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_flux_domain_model_value');

\FluidTYPO3\Flux\Core::registerConfigurationProvider('FluidTYPO3\Flux\Provider\ContentProvider');
