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

?>