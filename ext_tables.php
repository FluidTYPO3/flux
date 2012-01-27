<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}


t3lib_extMgm::addTypoScriptSetup('
	plugin.tx_flux.view {
		templateRootPath = EXT:flux/Resources/Private/Templates/
		partialRootPath = EXT:flux/Resources/Private/Partials/
		layoutRootPath = EXT:flux/Resources/Private/Layouts/
	}
');

t3lib_extMgm::addTCAcolumns('tt_content', array(
		'tx_flux_column' => array (
			'exclude' => 1,
			'config' => array (
				'type' => 'passthrough',
			)
		),
	)
);

?>