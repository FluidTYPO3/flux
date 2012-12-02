<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Content',
	array(
		'Content' => 'render',
	),
	array(
	),
	Tx_Extbase_Utility_Extension::PLUGIN_TYPE_CONTENT_ELEMENT
);
