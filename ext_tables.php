<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/Common', 'Fluid Content Elements');
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TwitterBootstrap', 'Fluid Content Elements: Twitter Bootstrap');