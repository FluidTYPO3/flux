<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

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